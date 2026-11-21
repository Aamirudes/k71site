@extends('storefront.themes.layout')

@section('theme_content')
@php
    $themeId = $manifest['id'] ?? 'maison-elite';
    $customThemeHome = 'storefront.themes.bespoke.' . $themeId . '.home';
    $arch = $manifest['layout']['page_architecture'] ?? 'editorial-runway';
    $archView = 'storefront.themes.architectures.' . $arch;
@endphp

@if(view()->exists($customThemeHome))
    @include($customThemeHome)
@elseif(view()->exists($archView))
    @include($archView)
    @if(!empty($manifest['layout']['show_flagship_band']))
        @include('storefront.themes.partials.flagship_band')
    @endif
@else
    @include('storefront.themes.partials.sections')
@endif
@endsection
