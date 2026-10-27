<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class StorageFallbackController extends Controller
{
    /**
     * Sirve el archivo desde storage si existe en disco, o genera un placeholder SVG elegante según la categoría.
     */
    public function serve(string $folder, string $filename): Response
    {
        $relativePath = "{$folder}/{$filename}";

        // 1. Si el archivo realmente existe en storage/app/public, devolverlo directamente
        if (Storage::disk('public')->exists($relativePath)) {
            $path = Storage::disk('public')->path($relativePath);
            $mime = mime_content_type($path) ?: 'application/octet-stream';
            return response()->file($path, [
                'Content-Type'  => $mime,
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        // 2. Si no existe en el disco, servir un SVG elegante temático de SIGVOS
        $svg = $this->generatePlaceholderSvg($folder);

        return response($svg, 200, [
            'Content-Type'  => 'image/svg+xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    private function generatePlaceholderSvg(string $folder): string
    {
        $config = match ($folder) {
            'lotes' => [
                'bg1'   => '#065f46',
                'bg2'   => '#047857',
                'title' => 'Lote SIGVOS',
                'icon'  => '<path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 0 1 0-5 2.5 2.5 0 0 1 0 5z" fill="#ffffff" opacity="0.95"/>',
            ],
            'cultivos', 'cultivos_portadas' => [
                'bg1'   => '#059669',
                'bg2'   => '#10b981',
                'title' => 'Cultivo SIGVOS',
                'icon'  => '<path d="M12 22v-9M12 13C9 13 6 10 6 6c3 0 6 3 6 7zM12 13c3 0 6-3 6-7-3 0-6 3-6 7z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>',
            ],
            'fotos_perfil' => [
                'bg1'   => '#334155',
                'bg2'   => '#475569',
                'title' => 'Usuario SIGVOS',
                'icon'  => '<path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" fill="#ffffff" opacity="0.95"/>',
            ],
            default => [
                'bg1'   => '#0f2e22',
                'bg2'   => '#10b981',
                'title' => 'Evidencia SIGVOS',
                'icon'  => '<path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z" fill="#ffffff" opacity="0.95"/>',
            ],
        };

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400" width="100%" height="100%">
  <defs>
    <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="{$config['bg1']}" />
      <stop offset="100%" stop-color="{$config['bg2']}" />
    </linearGradient>
    <pattern id="pattern" width="30" height="30" patternUnits="userSpaceOnUse">
      <path d="M 30 0 L 0 30 M 0 0 L 30 30" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="1"/>
    </pattern>
  </defs>
  <rect width="400" height="400" fill="url(#grad)" />
  <rect width="400" height="400" fill="url(#pattern)" />
  <g transform="translate(200, 165) scale(4) translate(-12, -12)">
    {$config['icon']}
  </g>
  <text x="200" y="248" font-family="'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" font-size="20" font-weight="700" fill="#ffffff" text-anchor="middle" letter-spacing="0.5">{$config['title']}</text>
  <text x="200" y="274" font-family="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" font-size="12" fill="rgba(255,255,255,0.8)" text-anchor="middle">Fotografía del sistema</text>
</svg>
SVG;
    }
}
