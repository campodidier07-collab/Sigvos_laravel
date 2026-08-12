@extends('layouts.adminlte')

@section('title', 'Mi Perfil')

@section('content')

<style>
    .profile-hero {
        background: linear-gradient(135deg, #064e3b 0%, #065f46 40%, #047857 100%);
        border-radius: 20px;
        padding: 36px;
        position: relative;
        overflow: hidden;
        margin-bottom: 28px;
    }
    .profile-hero::before {
        content: '';
        position: absolute; top: -60px; right: -60px;
        width: 200px; height: 200px;
        border-radius: 50%;
        background: rgba(255,255,255,0.05);
    }
    .profile-hero::after {
        content: '';
        position: absolute; bottom: -40px; left: 40px;
        width: 130px; height: 130px;
        border-radius: 50%;
        background: rgba(255,255,255,0.04);
    }

    /* Avatar */
    .avatar-wrapper {
        position: relative;
        display: inline-block;
        cursor: pointer;
    }
    .avatar-img {
        width: 100px; height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid rgba(255,255,255,0.3);
        box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        transition: all 0.3s ease;
    }
    .avatar-initials {
        width: 100px; height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, #10b981, #34d399);
        color: white;
        display: flex; align-items: center; justify-content: center;
        font-size: 2.4rem; font-weight: 800;
        border: 4px solid rgba(255,255,255,0.3);
        box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        transition: all 0.3s ease;
    }
    .avatar-overlay {
        position: absolute; inset: 0;
        border-radius: 50%;
        background: rgba(0,0,0,0.45);
        display: flex; align-items: center; justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        color: white; font-size: 1.3rem;
    }
    .avatar-wrapper:hover .avatar-overlay { opacity: 1; }
    .avatar-wrapper:hover .avatar-img,
    .avatar-wrapper:hover .avatar-initials { filter: brightness(0.7); }

    /* Cards */
    .profile-card {
        background: #fff;
        border-radius: 18px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        overflow: hidden;
        transition: box-shadow 0.3s ease;
        margin-bottom: 24px;
    }
    .profile-card:hover { box-shadow: 0 8px 30px rgba(0,0,0,0.1); }
    .profile-card-header {
        padding: 20px 28px 14px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .profile-card-icon {
        width: 40px; height: 40px;
        border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .icon-green  { background: #ecfdf5; color: #059669; }
    .icon-blue   { background: #eff6ff; color: #3b82f6; }
    .profile-card-body { padding: 24px 28px; }

    /* Form fields */
    .form-field { margin-bottom: 20px; }
    .form-field label {
        display: block;
        font-size: 0.82rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .form-control-custom {
        width: 100%;
        padding: 11px 16px;
        border: 1.5px solid #e2e8f0;
        border-radius: 11px;
        font-size: 0.9rem;
        color: #1e293b;
        background: #f8fafc;
        transition: all 0.25s ease;
        outline: none;
    }
    .form-control-custom:focus {
        border-color: #10b981;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(16,185,129,0.12);
    }
    .form-control-custom.is-invalid { border-color: #ef4444; }

    /* Buttons */
    .btn-save {
        background: linear-gradient(135deg, #059669, #10b981);
        color: white;
        border: none;
        padding: 11px 28px;
        border-radius: 11px;
        font-weight: 600;
        font-size: 0.88rem;
        cursor: pointer;
        transition: all 0.25s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(16,185,129,0.35);
        color: white;
    }
    .btn-save:active { transform: translateY(0); }

    /* Success toast */
    .success-toast {
        background: #f0fdf4;
        border: 1.5px solid #bbf7d0;
        color: #166534;
        padding: 10px 18px;
        border-radius: 10px;
        font-size: 0.83rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .error-msg { color: #ef4444; font-size: 0.78rem; margin-top: 4px; }

    /* Password strength */
    .strength-bar { height: 4px; border-radius: 2px; margin-top: 8px; background: #e2e8f0; overflow: hidden; }
    .strength-fill { height: 100%; border-radius: 2px; transition: width 0.4s ease, background 0.4s ease; width: 0; }
    .strength-text { font-size: 0.75rem; color: #64748b; margin-top: 4px; }

    /* Role badge in hero */
    .hero-role-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.25);
        color: rgba(255,255,255,0.95);
        font-size: 0.78rem;
        font-weight: 600;
        padding: 4px 14px;
        border-radius: 20px;
        margin-top: 6px;
    }
    .online-dot {
        width: 7px; height: 7px;
        border-radius: 50%;
        background: #4ade80;
        box-shadow: 0 0 6px #4ade80;
    }
</style>

<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex align-items-center gap-2 mb-0">
            <i class="fas fa-user-circle text-muted"></i>
            <span class="text-muted small">Mi Cuenta &rsaquo; Perfil</span>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">

    {{-- ── Hero Banner ──────────────────────────────────────────────────── --}}
    <div class="profile-hero">
        <div class="row align-items-center position-relative" style="z-index:1;">
            <div class="col-auto">
                <form id="avatarForm" method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="file" id="avatarInput" name="foto_perfil" accept="image/*" style="display:none">
                    <div class="avatar-wrapper" onclick="document.getElementById('avatarInput').click()" title="Cambiar foto">
                        @if(auth()->user()->foto_perfil)
                            <img id="avatarPreview" src="{{ asset('storage/' . auth()->user()->foto_perfil) }}" class="avatar-img" alt="Foto de perfil">
                        @else
                            <div class="avatar-initials" id="avatarInitials">
                                {{ strtoupper(substr(auth()->user()->nombre ?? 'U', 0, 1)) }}
                            </div>
                        @endif
                        <div class="avatar-overlay"><i class="fas fa-camera"></i></div>
                    </div>
                </form>
            </div>
            <div class="col">
                <h1 style="color:#fff; font-size:1.6rem; font-weight:800; margin:0 0 2px; font-family:'Outfit',sans-serif;">
                    {{ auth()->user()->nombre ?? 'Mi Perfil' }}
                </h1>
                <p style="color:rgba(255,255,255,0.7); margin:0; font-size:0.9rem;">{{ auth()->user()->email }}</p>
                <div class="hero-role-badge">
                    <span class="online-dot"></span>
                    {{ auth()->user()->rol?->nombre ?? 'Usuario' }}
                </div>
            </div>
            <div class="col-auto d-none d-md-block text-right" style="z-index:1;">
                <p style="color:rgba(255,255,255,0.55); font-size:0.75rem; margin:0;">Miembro desde</p>
                <p style="color:rgba(255,255,255,0.9); font-weight:600; margin:0; font-size:0.92rem;">
                    {{ auth()->user()->created_at?->format('M Y') ?? '—' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Tip de foto --}}
    <p style="font-size:0.8rem; color:#64748b; margin-bottom:20px; margin-top:-10px;">
        <i class="fas fa-info-circle text-success mr-1"></i>
        Haz clic sobre tu foto o inicial para cambiar tu imagen de perfil.
    </p>

    <div class="row">
        {{-- ── Información Personal ─────────────────────────────────────── --}}
        <div class="col-lg-6">
            <div class="profile-card">
                <div class="profile-card-header">
                    <div class="profile-card-icon icon-green">
                        <i class="fas fa-user-edit"></i>
                    </div>
                    <div>
                        <h3 style="margin:0; font-size:1rem; font-weight:700; color:#1e293b;">Información Personal</h3>
                        <p style="margin:0; font-size:0.78rem; color:#94a3b8;">Actualiza tu nombre, correo y teléfono</p>
                    </div>
                </div>
                <div class="profile-card-body">


                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" id="profileForm">
                        @csrf @method('patch')

                        {{-- Nombre --}}
                        <div class="form-field">
                            <label for="nombre">Nombre completo</label>
                            <input type="text" id="nombre" name="nombre"
                                   class="form-control-custom {{ $errors->has('nombre') ? 'is-invalid' : '' }}"
                                   value="{{ old('nombre', auth()->user()->nombre) }}"
                                   placeholder="Ej: Sergio Rodríguez" required>
                            @error('nombre') <p class="error-msg"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                        </div>

                        {{-- Email --}}
                        <div class="form-field">
                            <label for="email">Correo electrónico</label>
                            <input type="email" id="email" name="email"
                                   class="form-control-custom {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                   value="{{ old('email', auth()->user()->email) }}"
                                   placeholder="correo@ejemplo.com" required>
                            @error('email') <p class="error-msg"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                        </div>

                        {{-- Teléfono --}}
                        <div class="form-field">
                            <label for="telefono">Teléfono <span style="color:#94a3b8; font-weight:400;">(opcional)</span></label>
                            <input type="text" id="telefono" name="telefono"
                                   class="form-control-custom {{ $errors->has('telefono') ? 'is-invalid' : '' }}"
                                   value="{{ old('telefono', auth()->user()->telefono) }}"
                                   placeholder="Ej: 3001234567">
                            @error('telefono') <p class="error-msg"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                        </div>

                        <div class="d-flex align-items-center gap-3 mt-2">
                            <button type="submit" class="btn-save">
                                <i class="fas fa-save"></i> Guardar cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── Cambiar Contraseña ───────────────────────────────────────── --}}
        <div class="col-lg-6">
            <div class="profile-card">
                <div class="profile-card-header">
                    <div class="profile-card-icon icon-blue">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div>
                        <h3 style="margin:0; font-size:1rem; font-weight:700; color:#1e293b;">Seguridad</h3>
                        <p style="margin:0; font-size:0.78rem; color:#94a3b8;">Cambia tu contraseña de acceso</p>
                    </div>
                </div>
                <div class="profile-card-body">


                    <form method="POST" action="{{ route('password.update') }}" id="passwordForm">
                        @csrf @method('put')

                        {{-- Contraseña actual --}}
                        <div class="form-field">
                            <label for="current_password">Contraseña actual</label>
                            <div style="position:relative;">
                                <input type="password" id="current_password" name="current_password"
                                       class="form-control-custom {{ $errors->updatePassword->has('current_password') ? 'is-invalid' : '' }}"
                                       placeholder="••••••••" autocomplete="current-password">
                                <span class="toggle-pw" data-target="current_password" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);cursor:pointer;color:#94a3b8;">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                            @if($errors->updatePassword->has('current_password'))
                                <p class="error-msg"><i class="fas fa-exclamation-circle mr-1"></i>{{ $errors->updatePassword->first('current_password') }}</p>
                            @endif
                        </div>

                        {{-- Nueva contraseña --}}
                        <div class="form-field">
                            <label for="password">Nueva contraseña</label>
                            <div style="position:relative;">
                                <input type="password" id="password" name="password"
                                       class="form-control-custom {{ $errors->updatePassword->has('password') ? 'is-invalid' : '' }}"
                                       placeholder="Mínimo 8 caracteres" autocomplete="new-password"
                                       oninput="checkStrength(this.value)">
                                <span class="toggle-pw" data-target="password" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);cursor:pointer;color:#94a3b8;">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                            <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                            <p class="strength-text" id="strengthText"></p>
                            @if($errors->updatePassword->has('password'))
                                <p class="error-msg"><i class="fas fa-exclamation-circle mr-1"></i>{{ $errors->updatePassword->first('password') }}</p>
                            @endif
                        </div>

                        {{-- Confirmar contraseña --}}
                        <div class="form-field">
                            <label for="password_confirmation">Confirmar nueva contraseña</label>
                            <div style="position:relative;">
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                       class="form-control-custom"
                                       placeholder="Repite la contraseña" autocomplete="new-password">
                                <span class="toggle-pw" data-target="password_confirmation" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);cursor:pointer;color:#94a3b8;">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>

                        <button type="submit" class="btn-save" style="background:linear-gradient(135deg,#2563eb,#3b82f6);">
                            <i class="fas fa-shield-alt"></i> Actualizar contraseña
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
</section>

<script>
// ── Previsualizar avatar al seleccionar archivo ─────────────────────────────
document.getElementById('avatarInput').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function (e) {
        // Reemplazar initials por imagen real o actualizar src
        const existing = document.getElementById('avatarPreview');
        const initials = document.getElementById('avatarInitials');

        if (existing) {
            existing.src = e.target.result;
        } else if (initials) {
            const img = document.createElement('img');
            img.id = 'avatarPreview';
            img.src = e.target.result;
            img.className = 'avatar-img';
            img.alt = 'Foto de perfil';
            initials.parentNode.replaceChild(img, initials);
        }
    };
    reader.readAsDataURL(file);

    // Enviar automáticamente el form de avatar
    document.getElementById('avatarForm').submit();
});

// ── Toggle show/hide password ───────────────────────────────────────────────
document.querySelectorAll('.toggle-pw').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const input = document.getElementById(this.dataset.target);
        const icon = this.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });
});

// ── Medidor de fortaleza de contraseña ─────────────────────────────────────
function checkStrength(val) {
    const fill = document.getElementById('strengthFill');
    const text = document.getElementById('strengthText');
    let score = 0;
    if (val.length >= 8)  score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const levels = [
        { pct: '0%',   color: '#e2e8f0', label: '' },
        { pct: '25%',  color: '#ef4444', label: '🔴 Muy débil' },
        { pct: '50%',  color: '#f97316', label: '🟠 Débil' },
        { pct: '75%',  color: '#eab308', label: '🟡 Aceptable' },
        { pct: '100%', color: '#10b981', label: '🟢 Segura' },
    ];
    const lvl = levels[score] || levels[0];
    fill.style.width = lvl.pct;
    fill.style.background = lvl.color;
    text.textContent = lvl.label;
}
</script>

@endsection
