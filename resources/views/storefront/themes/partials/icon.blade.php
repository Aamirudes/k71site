@php
    $iconName = $name ?? 'arrow-right';
    $iconClass = $class ?? 'w-4 h-4';
@endphp
@switch($iconName)
    @case('search')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M20 20l-3.5-3.5"/></svg>
        @break
    @case('bag')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 8h12l-1 12H7L6 8z"/><path stroke-linecap="round" d="M9 8V6a3 3 0 016 0v2"/></svg>
        @break
    @case('menu')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
        @break
    @case('close')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
        @break
    @case('user')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path stroke-linecap="round" d="M4 20c0-4 3.6-6 8-6s8 2 8 6"/></svg>
        @break
    @case('zoom')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M20 20l-3.5-3.5M11 8v6M8 11h6"/></svg>
        @break
    @case('star')
        <svg class="{{ $iconClass }}" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.5l2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 17.4 6.1 20.5l1.2-6.5L2.5 9.4l6.6-.9L12 2.5z"/></svg>
        @break
    @case('check')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        @break
    @case('shield')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v6c0 4.5-3 7.8-7 9-4-1.2-7-4.5-7-9V6l7-3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.5 12l1.8 1.8 3.7-3.8"/></svg>
        @break
    @case('truck')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h11v10H3V6zm11 3h4l3 3v4h-7V9z"/><circle cx="7.5" cy="17.5" r="1.5"/><circle cx="17.5" cy="17.5" r="1.5"/></svg>
        @break
    @case('refresh')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 12a8 8 0 0113.7-5.6L20 8.5M20 4v4.5h-4.5M20 12a8 8 0 01-13.7 5.6L4 15.5M4 20v-4.5h4.5"/></svg>
        @break
    @case('ruler')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 15l12-12 6 6-12 12-6-6zm5-1l2 2m1-5l2 2m1-5l2 2"/></svg>
        @break
    @case('sliders')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/><circle cx="9" cy="6" r="2" fill="currentColor"/><circle cx="15" cy="12" r="2" fill="currentColor"/><circle cx="10" cy="18" r="2" fill="currentColor"/></svg>
        @break
    @case('external')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 4h6v6M10 14L20 4M18 14v5a1 1 0 01-1 1H5a1 1 0 01-1-1V7a1 1 0 011-1h5"/></svg>
        @break
    @default
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6l6 6-6 6"/></svg>
@endswitch
