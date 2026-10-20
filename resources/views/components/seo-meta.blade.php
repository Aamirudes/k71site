@props(['model'])

@php
    $title = $model->getSeoTitle() . ' | ' . config('marketplace.name');
    $description = $model->getSeoDescription();
    $image = $model->getOgImage() ? asset('storage/' . $model->getOgImage()) : asset(config('marketplace.seo.og_image'));
    $url = request()->url();
@endphp

<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $url }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:image" content="{{ $image }}">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="{{ $url }}">
<meta property="twitter:title" content="{{ $title }}">
<meta property="twitter:description" content="{{ $description }}">
<meta property="twitter:image" content="{{ $image }}">

<!-- Canonical -->
<link rel="canonical" href="{{ $url }}">
