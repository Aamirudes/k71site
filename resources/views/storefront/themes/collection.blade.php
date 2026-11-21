@extends('storefront.themes.layout')

@section('theme_content')
@php
    $themeId = $manifest['id'] ?? 'maison-elite';
    $customThemeCol = 'storefront.themes.bespoke.' . $themeId . '.collection';
    $arch = $manifest['layout']['page_architecture'] ?? 'editorial-runway';
    $archColView = 'storefront.themes.collections.' . $arch;
@endphp

@if(view()->exists($customThemeCol))
    @include($customThemeCol)
@elseif(view()->exists($archColView))
    @include($archColView)
@else
    @include('storefront.themes.collections.editorial-runway')
@endif
@endsection
