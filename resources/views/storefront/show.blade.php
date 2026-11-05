@php
    if (!app()->bound('current_vendor')) {
        app()->instance('current_vendor', $vendor);
    }
    
    $storeCanonicalUrl = $vendor->storefront_url;
    $config = $config ?? $vendor->getStorefrontConfig($isPreview ?? false);
    $theme = $config['theme'] ?? [];
    $brand = $config['brand'] ?? [];
    
    $storeName = $brand['store_name'] ?? $vendor->shop_name;
    $storeLogo = $brand['logo_url'] ?? $vendor->logo_url;
    $storeBanner = $brand['banner_url'] ?? $vendor->banner_url;
    $storeDescription = $brand['description'] ?? $vendor->description ?? 'Official verified flagship store on K71';

    $storeSchema = [
        '@type' => 'Store',
        'name' => $storeName,
        'url' => $storeCanonicalUrl,
        'description' => Str::limit(strip_tags($storeDescription), 250),
        'telephone' => $vendor->contact_phone,
        'parentOrganization' => [
            '@type' => 'Organization',
            'name' => config('marketplace.name', 'K71'),
            'url' => url('/'),
        ],
    ];
    if ($storeLogo) {
        $storeSchema['image'] = $storeLogo;
        $storeSchema['logo'] = $storeLogo;
    } elseif ($storeBanner) {
        $storeSchema['image'] = $storeBanner;
    }

    $storeBreadcrumb = [
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Stores', 'item' => route('catalog.index')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $storeName, 'item' => $storeCanonicalUrl],
        ],
    ];

    $storeSchemaJson = [
        '@context' => 'https://schema.org',
        '@graph' => [
            $storeSchema,
            $storeBreadcrumb,
        ],
    ];

    $presetKey = $config['preset'] ?? 'atelier';
    $presetView = 'storefront.presets.' . $presetKey;
    if (!view()->exists($presetView)) {
        $presetView = 'storefront.presets.atelier';
    }

    // Google Fonts selection
    $headingFont = $theme['font_heading'] ?? 'Playfair Display';
    $bodyFont = $theme['font_body'] ?? 'Inter';
    $fontsQuery = str_replace(' ', '+', $headingFont) . ':wght@400;600;700;800;900';
    if ($bodyFont !== $headingFont) {
        $fontsQuery .= '&family=' . str_replace(' ', '+', $bodyFont) . ':wght@300;400;500;600;700';
    }
@endphp

<x-layouts.public 
    :title="$storeName" 
    :canonicalUrl="$storeCanonicalUrl"
    :metaDescription="Str::limit(strip_tags($storeDescription), 160)" 
    :ogImage="$storeBanner ?: $storeLogo"
    :schemaJson="$storeSchemaJson"
    :fullWidth="true"
    :hideMainHeader="true"
    :hideFooter="true"
>
    <!-- Dynamic Google Fonts Preload for Signature Presets -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family={{ $fontsQuery }}&family=Space+Grotesk:wght@400;600;700&family=Syne:wght@700;800;900&family=Fraunces:ital,opsz,wght@0,9..144,600;0,9..144,800;1,9..144,400&family=Plus+Jakarta+Sans:wght@500;700;800&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Storefront CSS Theme Variables -->
    <style>
        :root {
            --sf-primary: {{ $theme['primary_color'] ?? '#18181b' }};
            --sf-secondary: {{ $theme['secondary_color'] ?? '#71717a' }};
            --sf-accent: {{ $theme['accent_color'] ?? '#c59b27' }};
            --sf-bg: {{ $theme['bg_color'] ?? '#fafaf9' }};
            --sf-surface: {{ $theme['surface_color'] ?? '#ffffff' }};
            --sf-text: {{ $theme['text_color'] ?? '#18181b' }};
            --sf-font-heading: '{{ $headingFont }}', serif, sans-serif;
            --sf-font-body: '{{ $bodyFont }}', system-ui, -apple-system, sans-serif;
        }

        .font-heading {
            font-family: var(--sf-font-heading);
        }
        .font-body {
            font-family: var(--sf-font-body);
        }

        /* Editor Section Selection Highlight Outline */
        [data-section-id] {
            position: relative;
            transition: outline 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        body.is-editor-mode [data-section-id]:hover {
            outline: 2px dashed rgba(59, 130, 246, 0.5);
            outline-offset: -2px;
            cursor: pointer;
        }
        body.is-editor-mode [data-section-id].editor-selected-section {
            outline: 3px solid #3b82f6 !important;
            outline-offset: -3px;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.25);
        }
    </style>

    {{-- Render the active Signature Preset --}}
    @include($presetView)

    <!-- K71 Unified Cart Integration & Quick Add Bridge -->
    <div id="quick-cart-toast" 
         class="fixed bottom-6 right-6 z-50 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none bg-stone-900 text-white px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-3 text-xs font-semibold">
        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
        <span id="quick-cart-toast-msg">Added to bag</span>
    </div>

    <script>
        // High-Performance Quick Add to Bag Bridge
        window.quickAddToCart = function(productId, variantId) {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const toast = document.getElementById('quick-cart-toast');
            const toastMsg = document.getElementById('quick-cart-toast-msg');

            fetch('{{ route('cart.add') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token || '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    product_id: productId,
                    variant_id: variantId || null,
                    quantity: 1
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' || data.success) {
                    // Update badges
                    const badges = document.querySelectorAll('#cart-counter-badge');
                    badges.forEach(b => {
                        b.textContent = data.cart_count || (parseInt(b.textContent || 0) + 1);
                        b.classList.remove('scale-0');
                        b.classList.add('scale-100');
                    });

                    // Trigger toast
                    if (toast && toastMsg) {
                        toastMsg.textContent = '✓ Item added to bag';
                        toast.classList.remove('translate-y-20', 'opacity-0');
                        toast.classList.add('translate-y-0', 'opacity-100');
                        setTimeout(() => {
                            toast.classList.remove('translate-y-0', 'opacity-100');
                            toast.classList.add('translate-y-20', 'opacity-0');
                        }, 2500);
                    }

                    // Open mini-cart drawer if defined
                    if (window.openK71Cart) {
                        window.openK71Cart();
                    }
                } else {
                    window.location.href = '{{ route('cart.index') }}';
                }
            })
            .catch(err => {
                console.warn('Quick cart fallback:', err);
                window.location.href = '{{ route('cart.index') }}';
            });
        };

        // Visual Editor Real-time Bridge (if loaded inside editor iframe)
        if (window.parent && window.parent !== window) {
            document.body.classList.add('is-editor-mode');

            // Listen for editor events
            window.addEventListener('message', function(event) {
                if (!event.data || typeof event.data !== 'object') return;

                if (event.data.action === 'highlight_section') {
                    const secId = event.data.sectionId;
                    document.querySelectorAll('.editor-selected-section').forEach(el => el.classList.remove('editor-selected-section'));
                    
                    const target = document.querySelector(`[data-section-id="${secId}"]`) || document.getElementById(secId);
                    if (target) {
                        target.classList.add('editor-selected-section');
                        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }

                if (event.data.action === 'update_theme') {
                    const t = event.data.theme;
                    if (t) {
                        if (t.primary_color) document.documentElement.style.setProperty('--sf-primary', t.primary_color);
                        if (t.accent_color) document.documentElement.style.setProperty('--sf-accent', t.accent_color);
                        if (t.bg_color) document.documentElement.style.setProperty('--sf-bg', t.bg_color);
                        if (t.surface_color) document.documentElement.style.setProperty('--sf-surface', t.surface_color);
                        if (t.text_color) document.documentElement.style.setProperty('--sf-text', t.text_color);
                    }
                }
            });

            // Notify parent when a section is clicked in the canvas
            document.addEventListener('click', function(e) {
                const secEl = e.target.closest('[data-section-id]');
                if (secEl) {
                    const secId = secEl.getAttribute('data-section-id');
                    window.parent.postMessage({
                        action: 'section_clicked',
                        sectionId: secId
                    }, '*');
                }
            }, true);
        }
    </script>
</x-layouts.public>
