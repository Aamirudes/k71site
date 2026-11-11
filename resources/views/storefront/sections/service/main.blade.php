@php
    $presetKey = $presetKey ?? ($providerSite['preset_key'] ?? 'velora');
    $ps = $ps ?? ($providerSite['tokens'] ?? \App\Services\Services\ServiceWebsitePresenter::presetTokens($presetKey, $theme ?? ($providerSite['theme'] ?? [])));
    $isSubdomainHost = request()->attributes->has('current_vendor') && !request()->is('s/*');
    $enquireAction = $isSubdomainHost ? '/enquire' : route('storefront.enquire', $vendor->slug);
    $portfolioUrlFn = fn (string $projectSlug) => $isSubdomainHost
        ? url('/portfolio/' . $projectSlug) . (!empty($isPreview) ? '?preview=1' : '')
        : route('storefront.portfolio.show', [$vendor->slug, $projectSlug]) . (!empty($isPreview) ? '?preview=1' : '');
@endphp

<div class="flex-1 space-y-10 sm:space-y-14">
    @if($providerSite['section_visibility']['hero'] ?? true)
        @include('storefront.sections.service.hero')
    @endif

    @include('storefront.sections.service.portfolio_grid')
    @include('storefront.sections.service.services_showcase')
    @include('storefront.sections.service.about_credentials')
    @include('storefront.sections.service.reviews_and_replies')
    @include('storefront.sections.service.contact_enquiry')
</div>

@include('storefront.sections.service.footer')
