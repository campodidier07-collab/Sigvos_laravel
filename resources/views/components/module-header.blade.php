@props([
    'title', 
    'subtitle' => 'Vista general del módulo.', 
    'icon', 
    'bannerTitle' => null, 
    'bannerSubtitle' => null
])

@section('navbar-title')
    <div class="module-header" style="padding: 0; display: flex; align-items: center; gap: 12px;">
        <div class="module-icon-box" style="width: 40px; height: 40px; font-size: 1.15rem; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: #10b981; color: white; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.25); flex-shrink: 0;">
            @php
                $cleanIcon = $icon;
                if (!str_contains($cleanIcon, 'fa-solid') && !str_contains($cleanIcon, 'fa-regular') && !str_contains($cleanIcon, 'fa-brands')) {
                    $cleanIcon = str_starts_with($cleanIcon, 'fa-') ? 'fa-solid ' . $cleanIcon : 'fa-solid fa-' . $cleanIcon;
                }
            @endphp
            <i class="{{ $cleanIcon }}"></i>
        </div>
        <div class="module-header-info">
            <h2 style="font-size: 1.15rem; font-weight: 800; margin: 0; color: #1e293b; font-family: 'Outfit', sans-serif; line-height: 1.2;">{{ $title }}</h2>
            <p style="font-size: 0.78rem; margin: 0; color: #64748b; font-weight: 500;">{{ ucfirst(\Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY')) }}</p>
        </div>
    </div>
@endsection

<div class="module-banner d-flex justify-content-between align-items-center flex-wrap" style="gap: 16px;">
    <div>
        <h3>{{ $bannerTitle ?? $title }}</h3>
        <p>{{ $bannerSubtitle ?? $subtitle }}</p>
    </div>
    @if(isset($slot) && $slot->isNotEmpty())
        <div class="module-header-buttons d-flex align-items-center flex-wrap" style="gap: 10px;">
            {{ $slot }}
        </div>
    @endif
</div>

