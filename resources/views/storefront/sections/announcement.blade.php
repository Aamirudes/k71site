@php
    $headerCfg = $config['header'] ?? [];
    $enabled = $headerCfg['announcement_enabled'] ?? true;
    $text = $headerCfg['announcement_text'] ?? '';
    $link = $headerCfg['announcement_link'] ?? '';
    $bg = $headerCfg['announcement_bg'] ?? '#18181b';
    $textColor = $headerCfg['announcement_text_color'] ?? '#ffffff';
@endphp

@if($enabled && !empty($text))
<div id="section-announcement" 
     data-section-id="header"
     class="w-full py-2 px-4 text-center text-xs font-medium tracking-wide transition-colors relative z-40"
     style="background-color: {{ $bg }}; color: {{ $textColor }};">
    <div class="max-w-7xl mx-auto flex items-center justify-center gap-2">
        @if(!empty($link))
            <a href="{{ $link }}" class="hover:underline flex items-center justify-center gap-1.5 transition">
                <span>{{ $text }}</span>
                <span class="text-[10px] opacity-75">→</span>
            </a>
        @else
            <span>{{ $text }}</span>
        @endif
    </div>
</div>
@endif

