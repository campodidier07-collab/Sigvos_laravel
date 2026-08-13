@props([
    'title', 
    'subtitle' => 'Vista general del módulo.', 
    'icon', 
    'bannerTitle' => null, 
    'bannerSubtitle' => null
])

@section('navbar-title')
    <div class="module-header" style="padding: 0;">
        <div class="module-icon-box" style="width: 45px; height: 45px; font-size: 1.25rem; border-radius: 10px;">
            <i class="fas {{ $icon }}"></i>
        </div>
        <div class="module-header-info">
            <h2 style="font-size: 1.25rem; margin-bottom: 0;">{{ $title }}</h2>
            <p style="font-size: 0.8rem; margin: 0; color: #94a3b8;">{{ \Carbon\Carbon::now()->translatedFormat('l, d \d\e F \d\e Y') }}</p>
        </div>
    </div>
@endsection

<div class="module-banner d-flex justify-content-between align-items-center">
    <div>
        <h3>{{ $bannerTitle ?? $title }}</h3>
        <p>{{ $bannerSubtitle ?? $subtitle }}</p>
    </div>
    @if(isset($slot) && $slot->isNotEmpty())
        <div class="module-header-buttons">
            {{ $slot }}
        </div>
    @endif
</div>

