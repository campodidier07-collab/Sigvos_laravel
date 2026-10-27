<?php

namespace App\Http\Requests\Auth;

use App\Models\Usuario;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $email = strtolower($this->string('email'));
        $usuario = Usuario::where('email', $email)->first();

        // Verificar si la cuenta está bloqueada temporalmente en base de datos
        if ($usuario && $usuario->bloqueado_hasta && $usuario->bloqueado_hasta->isFuture()) {
            $minutos = now()->diffInMinutes($usuario->bloqueado_hasta) + 1;
            throw ValidationException::withMessages([
                'email' => "Cuenta temporalmente bloqueada por múltiples intentos fallidos. Intenta nuevamente en {$minutos} minuto(s).",
            ]);
        }

        // Verificar si la cuenta ha sido desactivada
        if ($usuario && ! $usuario->activo) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Tu cuenta ha sido desactivada. Comunícate con el administrador.',
            ]);
        }

        $credentials = array_merge($this->only('email', 'password'), ['activo' => true]);

        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            if ($usuario) {
                $usuario->increment('intentos_fallidos');
                if ($usuario->intentos_fallidos >= 5) {
                    $usuario->update(['bloqueado_hasta' => now()->addMinutes(15)]);
                }
            }

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // Restablecer contadores y registrar último acceso en caso de éxito
        if ($usuario) {
            $usuario->update([
                'intentos_fallidos' => 0,
                'bloqueado_hasta'   => null,
                'ultimo_acceso'     => now(),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
