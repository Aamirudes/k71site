@extends('storefront.themes.layout')

@section('theme_content')
@php
    $arch = $manifest['layout']['page_architecture'] ?? 'editorial-runway';
    $archView = 'storefront.themes.architectures.' . $arch;
@endphp

@if(view()->exists($archView))
    @include($archView)
    @include('storefront.themes.partials.flagship_band')
@else
    @include('storefront.themes.partials.sections')
@endif
@endsection
