<?php

namespace App\Services\Services;

use App\Models\PortfolioProject;
use App\Models\Product;
use App\Models\ProjectContract;
use App\Models\Review;
use App\Models\Vendor;
use App\Services\Storefront\StorefrontPresetService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ServiceWebsitePresenter
{
    public function __construct(
        protected StorefrontPresetService $presetService
    ) {}

    public function build(Vendor $vendor, bool $preview = false, array $config = []): array
    {
        $version = (int) ($vendor->website_cache_version ?? 1);
        $cacheKey = "service_website_v1_{$vendor->id}_{$version}";

        if (!$preview && empty($config)) {
            return Cache::remember($cacheKey, now()->addMinutes(10), fn () => $this->assemble($vendor, false, []));
        }

        return $this->assemble($vendor, $preview, $config);
    }

    protected function assemble(Vendor $vendor, bool $preview, array $config): array
    {
        if (empty($config)) {
            $config = $vendor->getStorefrontConfig($preview);
        }

        $serviceSite = is_array($config['service_site'] ?? null) ? $config['service_site'] : [];
        $brandCfg = is_array($config['brand'] ?? null) ? $config['brand'] : [];
        $heroCfg = is_array($config['hero'] ?? null) ? $config['hero'] : [];

        $sectionVisibility = array_merge([
            'hero' => true,
            'portfolio' => true,
            'services' => true,
            'about' => true,
            'reviews' => true,
            'contact' => true,
        ], $serviceSite['section_visibility'] ?? []);

        $navLabels = array_merge([
            'overview' => 'Overview',
            'work' => 'Work',
            'services' => 'Services',
            'about' => 'About',
            'reviews' => 'Reviews',
            'contact' => 'Contact',
        ], $serviceSite['nav_labels'] ?? []);

        $contactPrefs = array_merge([
            'show_email' => true,
            'show_phone' => false,
            'show_city' => true,
            'allow_direct_enquiry' => true,
            'allow_k71_chat' => true,
        ], is_array($vendor->public_contact_preferences) ? $vendor->public_contact_preferences : []);

        // 1. Portfolio Case Studies (real PortfolioProject table first)
        $portfolioQuery = PortfolioProject::with(['category', 'serviceProduct'])
            ->where('vendor_id', $vendor->id)
            ->when(!$preview, fn ($q) => $q->where('status', 'published'))
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->latest('id');

        $portfolioProjects = $portfolioQuery->get();

        // 2. Active Services from single source of truth (`products` table)
        $services = Product::withoutGlobalScopes()
            ->with(['images', 'category', 'gigPackages'])
            ->where('vendor_id', $vendor->id)
            ->where('vertical', 'services')
            ->where('status', 'active')
            ->where(function ($q) {
                $q->where('show_on_website', true)->orWhereNull('show_on_website');
            })
            ->orderByDesc('is_website_featured')
            ->orderByDesc('is_featured')
            ->latest('id')
            ->get();

        // 3. Genuine Verified Client Reviews (ServiceOrder Reviews + ProjectContract Reviews)
        $productIds = Product::withoutGlobalScopes()
            ->where('vendor_id', $vendor->id)
            ->pluck('id');

        $productReviews = Review::with(['user', 'product.images', 'serviceOrder'])
            ->where('is_approved', true)
            ->where(function ($q) use ($vendor, $productIds) {
                $q->where('vendor_id', $vendor->id);
                if ($productIds->isNotEmpty()) {
                    $q->orWhereIn('product_id', $productIds);
                }
            })
            ->latest('id')
            ->take(24)
            ->get()
            ->map(function (Review $rev) {
                return [
                    'id' => 'rev_' . $rev->id,
                    'source' => $rev->service_order_id ? 'Verified Service Order' : 'Verified Client Review',
                    'reviewer_name' => $rev->user?->name ?? 'Verified Client',
                    'reviewer_initial' => strtoupper(substr($rev->user?->name ?? 'C', 0, 1)),
                    'rating' => (int) $rev->rating,
                    'body' => $rev->body ?? $rev->comment ?? '',
                    'service_title' => $rev->product?->title ?? 'Service Engagement',
                    'created_at' => $rev->created_at,
                    'vendor_reply' => $rev->vendor_reply,
                    'vendor_replied_at' => $rev->vendor_replied_at,
                ];
            });

        $contractReviews = ProjectContract::with(['buyer', 'project'])
            ->where('vendor_id', $vendor->id)
            ->whereNotNull('buyer_rating')
            ->latest('reviewed_at')
            ->take(16)
            ->get()
            ->map(function (ProjectContract $c) {
                return [
                    'id' => 'con_' . $c->id,
                    'source' => 'Completed Project Contract',
                    'reviewer_name' => $c->buyer?->name ?? 'Project Client',
                    'reviewer_initial' => strtoupper(substr($c->buyer?->name ?? 'P', 0, 1)),
                    'rating' => (int) $c->buyer_rating,
                    'body' => $c->buyer_review ?? '',
                    'service_title' => $c->project?->title ?? 'Contract Project',
                    'created_at' => $c->reviewed_at ?? $c->completed_at ?? $c->updated_at,
                    'vendor_reply' => $c->vendor_reply,
                    'vendor_replied_at' => $c->vendor_replied_at,
                ];
            });

        $reviews = $productReviews
            ->concat($contractReviews)
            ->sortByDesc(fn ($item) => $item['created_at']?->timestamp ?? 0)
            ->values();

        $reviewsCount = $reviews->count();
        $averageRating = $reviewsCount > 0 ? round((float) $reviews->avg('rating'), 1) : null;

        $ordersCompleted = $vendor->serviceOrders()->where('status', 'completed')->count();
        $contractsCompleted = $vendor->contracts()->where('status', 'completed')->count();
        $completedCount = max((int) ($vendor->completed_projects ?? 0), $ordersCompleted + $contractsCompleted);

        $skills = collect($vendor->skills_json ?? [])->filter()->values()->all();
        $languages = collect($vendor->languages_json ?? [])->filter()->values()->all();
        $externalLinks = is_array($vendor->external_links) ? array_filter($vendor->external_links) : [];

        $workMode = $vendor->work_mode ?: 'remote';
        $workModeLabel = match ($workMode) {
            'onsite' => 'On-Site / Local',
            'hybrid' => 'Remote & On-Site',
            default => 'Remote Worldwide',
        };

        $availability = $vendor->availability_status ?: ($vendor->vacation_mode ? 'away' : 'available');
        $availabilityLabel = match ($availability) {
            'busy' => 'Limited Availability',
            'away' => 'Currently Away',
            default => 'Available for New Projects',
        };

        $providerMode = $vendor->provider_mode ?: 'individual';
        $providerModeLabel = $providerMode === 'agency' ? 'Creative & Service Agency' : 'Independent Specialist';

        $displayName = $brandCfg['store_name'] ?? $vendor->shop_name;
        $headline = ($serviceSite['hero_headline'] ?? null) ?: ($vendor->headline ?: ($vendor->tagline ?: ($brandCfg['tagline'] ?? $displayName)));
        $subtitle = ($serviceSite['hero_subtitle'] ?? null) ?: ($heroCfg['subtitle'] ?? null) ?: ($vendor->tagline ?: ($brandCfg['tagline'] ?? ''));
        $bio = $vendor->description ?: ($brandCfg['description'] ?? '');

        $initials = collect(explode(' ', trim($displayName)))
            ->filter()
            ->take(2)
            ->map(fn ($w) => strtoupper(substr($w, 0, 1)))
            ->implode('') ?: 'K7';

        $rawPresetKey = $config['preset'] ?? $vendor->layout_preset ?? 'velora';
        $presetKey = $this->presetService->normalizePresetKey($rawPresetKey);
        $presetMeta = $this->presetService->getPreset($presetKey);
        $tokens = self::presetTokens($presetKey, is_array($config['theme'] ?? null) ? $config['theme'] : []);

        $categories = $portfolioProjects->pluck('work_category')
            ->merge($services->map(fn ($s) => $s->category?->name))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return [
            'vendor' => $vendor,
            'is_preview' => $preview,
            'is_published' => $vendor->isWebsitePublished(),
            'preset_key' => $presetKey,
            'preset_meta' => $presetMeta,
            'tokens' => $tokens,
            'theme' => $config['theme'] ?? [],
            'service_site' => $serviceSite,
            'section_visibility' => $sectionVisibility,
            'nav_labels' => $navLabels,
            'identity' => [
                'name' => $displayName,
                'initials' => $initials,
                'headline' => $headline,
                'subtitle' => $subtitle,
                'bio' => $bio,
                'provider_mode' => $providerMode,
                'provider_mode_label' => $providerModeLabel,
                'work_mode' => $workMode,
                'work_mode_label' => $workModeLabel,
                'availability' => $availability,
                'availability_label' => $availabilityLabel,
                'response_time' => $vendor->response_time ?: ($vendor->response_time_minutes ? "Under {$vendor->response_time_minutes} mins" : 'Same day'),
                'years_experience' => $vendor->years_experience,
                'city' => $vendor->city,
                'state' => $vendor->state,
                'logo_url' => $brandCfg['logo_url'] ?? $vendor->logo_url,
                'banner_url' => $brandCfg['banner_url'] ?? $vendor->banner_url,
                'hourly_rate' => $vendor->hourly_rate,
                'is_verified_pro' => (bool) $vendor->is_verified_pro,
                'cta_primary_text' => $serviceSite['cta_primary_text'] ?? 'Start a Project',
                'cta_secondary_text' => $serviceSite['cta_secondary_text'] ?? 'View Work',
            ],
            'skills' => $skills,
            'languages' => $languages,
            'categories' => $categories,
            'external_links' => $externalLinks,
            'contact' => [
                'show_email' => (bool) ($contactPrefs['show_email'] ?? true),
                'email' => !empty($contactPrefs['show_email']) ? ($vendor->contact_email ?: $vendor->email) : null,
                'show_phone' => (bool) ($contactPrefs['show_phone'] ?? false),
                'phone' => !empty($contactPrefs['show_phone']) ? ($vendor->contact_phone ?: $vendor->phone) : null,
                'show_city' => (bool) ($contactPrefs['show_city'] ?? true),
                'city' => !empty($contactPrefs['show_city']) ? trim(($vendor->city ? $vendor->city : '') . ($vendor->state ? ', ' . $vendor->state : ''), ', ') : null,
                'allow_direct_enquiry' => (bool) ($contactPrefs['allow_direct_enquiry'] ?? true),
                'allow_k71_chat' => (bool) ($contactPrefs['allow_k71_chat'] ?? true),
            ],
            'portfolio_projects' => $portfolioProjects,
            'services' => $services,
            'reviews' => $reviews,
            'metrics' => [
                'reviews_count' => $reviewsCount,
                'average_rating' => $averageRating,
                'completed_projects' => $completedCount,
                'services_count' => $services->count(),
                'portfolio_count' => $portfolioProjects->count(),
            ],
            'readiness' => $this->evaluatePublishReadiness($vendor),
            'marketplace_profile_url' => route('services.provider', $vendor->slug),
            'website_url' => $vendor->storefront_url,
            'preview_url' => $vendor->storefrontUrl(['preview' => 1]),
        ];
    }

    public function evaluatePublishReadiness(Vendor $vendor): array
    {
        $hasIdentity = strlen(trim((string) $vendor->shop_name)) >= 2
            && (strlen(trim((string) ($vendor->headline ?: $vendor->tagline))) >= 4)
            && strlen(trim((string) $vendor->description)) >= 15;

        $hasSubdomain = strlen(trim((string) $vendor->slug)) >= 3;
        $hasPreset = (bool) ($vendor->layout_preset || $vendor->storefrontConfig?->preset_key);
        $servicesCount = Product::withoutGlobalScopes()
            ->where('vendor_id', $vendor->id)
            ->where('vertical', 'services')
            ->where('status', 'active')
            ->count();
        $portfolioCount = PortfolioProject::where('vendor_id', $vendor->id)
            ->where('status', 'published')
            ->count();

        $items = [
            [
                'key' => 'identity',
                'label' => 'Profile identity, headline & bio',
                'required' => true,
                'passed' => $hasIdentity,
            ],
            [
                'key' => 'subdomain',
                'label' => 'Unique website subdomain claimed',
                'required' => true,
                'passed' => $hasSubdomain,
            ],
            [
                'key' => 'preset',
                'label' => 'Design preset chosen from K71 Studio',
                'required' => true,
                'passed' => $hasPreset,
            ],
            [
                'key' => 'offerings',
                'label' => 'At least 1 active service OR published portfolio project',
                'required' => false,
                'passed' => ($servicesCount + $portfolioCount) > 0,
            ],
        ];

        $requiredMissing = collect($items)->filter(fn ($i) => $i['required'] && !$i['passed'])->values()->all();
        $passedCount = collect($items)->filter(fn ($i) => $i['passed'])->count();
        $score = (int) round(($passedCount / count($items)) * 100);

        return [
            'can_publish' => empty($requiredMissing),
            'score' => $score,
            'items' => $items,
            'missing' => $requiredMissing,
        ];
    }

    public static function presetTokens(string $presetKey, array $theme = []): array
    {
        $isDarkPreset = in_array($presetKey, ['noire', 'orbit', 'nexus', 'elysium', 'kinetic'], true) || !empty($theme['dark_mode']);

        $tokens = match ($presetKey) {
            'noire' => [
                'dark' => true,
                'radius' => 'rounded-none',
                'card' => 'bg-[#111114] border border-zinc-800 text-zinc-100 rounded-none',
                'card_hover' => 'hover:border-white transition-colors duration-200',
                'surface_alt' => 'bg-[#0b0b0e] border border-zinc-800/80 rounded-none',
                'muted' => 'text-zinc-400',
                'heading' => 'font-mono uppercase tracking-tight text-white',
                'pill' => 'bg-zinc-900 border border-zinc-700 text-zinc-200 rounded-none',
                'accent_badge' => 'bg-white text-black font-bold rounded-none',
                'btn_primary' => 'bg-white text-black hover:bg-zinc-200 rounded-none font-mono uppercase tracking-wider',
                'btn_secondary' => 'border border-zinc-600 text-white hover:border-white hover:bg-zinc-900 rounded-none font-mono uppercase tracking-wider',
                'input' => 'bg-[#09090b] border-zinc-700 text-white placeholder-zinc-500 rounded-none focus:border-white focus:ring-0',
            ],
            'aurel' => [
                'dark' => false,
                'radius' => 'rounded-3xl',
                'card' => 'bg-white border border-stone-200/90 text-stone-900 rounded-3xl shadow-[0_6px_30px_rgba(28,25,23,0.04)]',
                'card_hover' => 'hover:shadow-[0_14px_40px_rgba(28,25,23,0.08)] hover:-translate-y-0.5 transition-all duration-300',
                'surface_alt' => 'bg-[#f6f2eb] border border-stone-200/80 rounded-2xl',
                'muted' => 'text-stone-600',
                'heading' => 'font-serif italic text-stone-900',
                'pill' => 'bg-amber-50 border border-amber-200/80 text-amber-900 rounded-full',
                'accent_badge' => 'bg-stone-900 text-white rounded-full',
                'btn_primary' => 'bg-stone-900 text-white hover:bg-[#c28422] rounded-full font-semibold',
                'btn_secondary' => 'border border-stone-300 text-stone-800 hover:border-stone-900 bg-white rounded-full font-semibold',
                'input' => 'bg-[#faf8f5] border-stone-300 text-stone-900 placeholder-stone-400 rounded-2xl focus:border-stone-900 focus:ring-1 focus:ring-stone-900',
            ],
            'chroma' => [
                'dark' => false,
                'radius' => 'rounded-2xl',
                'card' => 'bg-white border-2 border-black text-black rounded-2xl shadow-[5px_5px_0px_0px_rgba(0,0,0,1)]',
                'card_hover' => 'hover:-translate-x-0.5 hover:-translate-y-0.5 hover:shadow-[7px_7px_0px_0px_rgba(37,99,235,1)] transition-all duration-200',
                'surface_alt' => 'bg-slate-50 border-2 border-black rounded-2xl',
                'muted' => 'text-zinc-600',
                'heading' => 'font-heading font-black uppercase tracking-tight text-black',
                'pill' => 'bg-yellow-300 border-2 border-black text-black rounded-full font-bold',
                'accent_badge' => 'bg-blue-600 text-white border-2 border-black rounded-full font-black',
                'btn_primary' => 'bg-black text-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(37,99,235,1)] hover:bg-blue-600 rounded-xl font-black uppercase tracking-wider',
                'btn_secondary' => 'bg-white text-black border-2 border-black hover:bg-zinc-100 rounded-xl font-black uppercase tracking-wider',
                'input' => 'bg-white border-2 border-black text-black placeholder-zinc-400 rounded-xl focus:border-blue-600 focus:ring-0',
            ],
            'orbit' => [
                'dark' => true,
                'radius' => 'rounded-3xl',
                'card' => 'bg-slate-950/75 backdrop-blur-2xl border border-white/15 ring-1 ring-cyan-400/20 text-slate-100 rounded-3xl shadow-[0_20px_60px_rgba(0,0,0,0.65)]',
                'card_hover' => 'hover:ring-cyan-400/60 hover:border-cyan-300/40 transition-all duration-300',
                'surface_alt' => 'bg-white/[0.04] border border-white/10 rounded-2xl',
                'muted' => 'text-slate-300',
                'heading' => 'font-heading font-bold text-white tracking-tight',
                'pill' => 'bg-cyan-500/15 border border-cyan-400/35 text-cyan-200 rounded-full',
                'accent_badge' => 'bg-gradient-to-r from-cyan-400 to-indigo-500 text-slate-950 font-black rounded-full',
                'btn_primary' => 'bg-gradient-to-r from-cyan-400 via-sky-400 to-indigo-500 text-slate-950 font-black rounded-full shadow-[0_0_25px_rgba(34,211,238,0.45)] hover:brightness-110',
                'btn_secondary' => 'bg-white/10 border border-white/20 text-white hover:bg-white/20 rounded-full font-bold',
                'input' => 'bg-slate-900/80 border-white/20 text-white placeholder-slate-400 rounded-2xl focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400',
            ],
            'zenith' => [
                'dark' => false,
                'radius' => 'rounded-none',
                'card' => 'bg-white border border-stone-300 text-[#111813] rounded-none shadow-xs',
                'card_hover' => 'hover:border-[#111813] transition-colors duration-200',
                'surface_alt' => 'bg-[#efefea] border border-stone-300 rounded-none',
                'muted' => 'text-stone-600',
                'heading' => 'font-heading font-bold tracking-tight text-[#111813]',
                'pill' => 'bg-stone-100 border border-stone-300 text-[#1b3b2b] rounded-full font-mono',
                'accent_badge' => 'bg-[#1b3b2b] text-white rounded-none font-mono',
                'btn_primary' => 'bg-[#111813] text-white hover:bg-[#1b3b2b] rounded-none font-mono uppercase tracking-wider',
                'btn_secondary' => 'border border-[#111813] text-[#111813] hover:bg-[#111813] hover:text-white rounded-none font-mono uppercase tracking-wider',
                'input' => 'bg-[#f5f5f0] border-stone-400 text-[#111813] placeholder-stone-400 rounded-none focus:border-[#111813] focus:ring-0',
            ],
            'solaris' => [
                'dark' => false,
                'radius' => 'rounded-[2rem]',
                'card' => 'bg-white border-2 border-[#e8dccb] text-[#291a13] rounded-[2rem] shadow-[0_10px_30px_rgba(41,26,19,0.05)]',
                'card_hover' => 'hover:border-[#c25e3e] transition-all duration-300',
                'surface_alt' => 'bg-[#f5eee4] border border-[#e8dccb] rounded-3xl',
                'muted' => 'text-[#785b4f]',
                'heading' => 'font-heading font-bold text-[#291a13]',
                'pill' => 'bg-[#f8f2e9] border border-[#e8dccb] text-[#c25e3e] rounded-full',
                'accent_badge' => 'bg-[#c25e3e] text-white rounded-full',
                'btn_primary' => 'bg-[#c25e3e] text-white hover:bg-[#291a13] rounded-full font-bold',
                'btn_secondary' => 'bg-white border-2 border-[#e8dccb] text-[#291a13] hover:border-[#c25e3e] rounded-full font-bold',
                'input' => 'bg-[#fcfaf6] border-[#dccbb5] text-[#291a13] placeholder-[#9c7e70] rounded-2xl focus:border-[#c25e3e] focus:ring-1 focus:ring-[#c25e3e]',
            ],
            'nexus' => [
                'dark' => true,
                'radius' => 'rounded-none',
                'card' => 'bg-[#111318] border border-zinc-800 text-zinc-100 rounded-none',
                'card_hover' => 'hover:border-[#a3e635] transition-colors duration-200',
                'surface_alt' => 'bg-[#0b0d11] border border-zinc-800 rounded-none',
                'muted' => 'text-zinc-400',
                'heading' => 'font-heading font-black uppercase tracking-wider text-white',
                'pill' => 'bg-[#171b22] border border-[#a3e635]/40 text-[#a3e635] rounded-none font-mono',
                'accent_badge' => 'bg-[#a3e635] text-black font-black rounded-none font-mono',
                'btn_primary' => 'bg-[#a3e635] text-black hover:bg-lime-300 rounded-none font-mono font-black uppercase tracking-wider',
                'btn_secondary' => 'border border-[#a3e635]/60 text-[#a3e635] hover:bg-[#a3e635] hover:text-black rounded-none font-mono uppercase tracking-wider',
                'input' => 'bg-[#0a0b0e] border-zinc-700 text-white placeholder-zinc-500 rounded-none focus:border-[#a3e635] focus:ring-0',
            ],
            'elysium' => [
                'dark' => true,
                'radius' => 'rounded-3xl',
                'card' => 'bg-gradient-to-b from-[#0d1326] to-[#070b18] border border-[#d4af37]/35 text-[#fcfbf7] rounded-3xl shadow-[0_15px_45px_rgba(0,0,0,0.7)]',
                'card_hover' => 'hover:border-[#f5d77f]/70 transition-all duration-300',
                'surface_alt' => 'bg-[#0a0f20] border border-[#d4af37]/25 rounded-2xl',
                'muted' => 'text-slate-300',
                'heading' => 'font-heading font-bold text-white tracking-wide',
                'pill' => 'bg-[#d4af37]/15 border border-[#d4af37]/40 text-[#f5d77f] rounded-full',
                'accent_badge' => 'bg-gradient-to-r from-[#b38728] via-[#d4af37] to-[#f5d77f] text-black font-bold rounded-full',
                'btn_primary' => 'bg-gradient-to-r from-[#b38728] via-[#d4af37] to-[#f5d77f] text-black font-bold rounded-full hover:brightness-110',
                'btn_secondary' => 'border border-[#d4af37]/50 text-[#f5d77f] hover:bg-[#d4af37]/15 rounded-full font-bold',
                'input' => 'bg-[#060913] border-[#d4af37]/35 text-white placeholder-slate-400 rounded-2xl focus:border-[#f5d77f] focus:ring-1 focus:ring-[#d4af37]',
            ],
            'vogue' => [
                'dark' => false,
                'radius' => 'rounded-none',
                'card' => 'bg-white border-2 border-black text-[#09090b] rounded-none shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]',
                'card_hover' => 'hover:shadow-[8px_8px_0px_0px_rgba(225,29,72,1)] transition-all duration-200',
                'surface_alt' => 'bg-stone-50 border-2 border-black rounded-none',
                'muted' => 'text-zinc-600',
                'heading' => 'font-heading font-black uppercase tracking-tight text-[#09090b]',
                'pill' => 'bg-black text-white rounded-none font-mono',
                'accent_badge' => 'bg-[#e11d48] text-white rounded-none font-mono font-bold',
                'btn_primary' => 'bg-black text-white hover:bg-[#e11d48] rounded-none font-mono font-bold uppercase tracking-widest',
                'btn_secondary' => 'bg-white border-2 border-black text-black hover:bg-black hover:text-white rounded-none font-mono font-bold uppercase tracking-widest',
                'input' => 'bg-white border-2 border-black text-black placeholder-zinc-400 rounded-none focus:border-[#e11d48] focus:ring-0',
            ],
            'kinetic' => [
                'dark' => true,
                'radius' => 'rounded-3xl',
                'card' => 'bg-[#121216]/95 border border-white/12 ring-1 ring-[#ECD2F3]/15 text-[#EFE9F2] rounded-3xl shadow-[0_24px_70px_rgba(0,0,0,0.75)]',
                'card_hover' => 'hover:border-[#ECD2F3]/50 hover:shadow-[0_28px_80px_rgba(236,210,243,0.16)] transition-all duration-300',
                'surface_alt' => 'bg-[#08080b] border border-white/10 rounded-2xl',
                'muted' => 'text-[#EFE9F2]/68',
                'heading' => 'font-heading font-light uppercase tracking-tight text-white',
                'pill' => 'bg-[#ECD2F3]/14 border border-[#ECD2F3]/30 text-[#FFCDFF] rounded-full',
                'accent_badge' => 'bg-[#ECD2F3] text-[#1A1018] font-bold rounded-full',
                'btn_primary' => 'bg-[#ECD2F3] hover:bg-[#F6E3FB] text-[#1A1018] rounded-full font-semibold shadow-[0_10px_28px_rgba(236,210,243,0.28)]',
                'btn_secondary' => 'bg-white/10 border border-white/18 text-[#EFE9F2] hover:bg-white/18 rounded-full font-semibold',
                'input' => 'bg-[#0b0b0f] border-white/15 text-[#EFE9F2] placeholder-white/40 rounded-2xl focus:border-[#ECD2F3] focus:ring-1 focus:ring-[#ECD2F3]',
            ],
            default => [
                'dark' => false,
                'radius' => 'rounded-3xl',
                'card' => 'bg-white/95 border border-stone-200/90 text-stone-900 rounded-3xl shadow-sm',
                'card_hover' => 'hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300',
                'surface_alt' => 'bg-stone-100/80 border border-stone-200/80 rounded-2xl',
                'muted' => 'text-stone-600',
                'heading' => 'font-heading font-bold text-stone-900',
                'pill' => 'bg-amber-50 border border-amber-200/70 text-amber-900 rounded-full',
                'accent_badge' => 'bg-stone-900 text-white rounded-full',
                'btn_primary' => 'bg-stone-900 text-white hover:opacity-90 rounded-full font-bold',
                'btn_secondary' => 'bg-white border border-stone-300 text-stone-900 hover:bg-stone-50 rounded-full font-bold',
                'input' => 'bg-stone-50 border-stone-200 text-stone-900 placeholder-stone-400 rounded-2xl focus:border-stone-900 focus:ring-1 focus:ring-stone-900',
            ],
        };

        $tokens['is_dark'] = $isDarkPreset;

        return $tokens;
    }
}
