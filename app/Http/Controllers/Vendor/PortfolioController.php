<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\PortfolioProject;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    protected function resolveVendor(Request $request)
    {
        $vendor = $request->attributes->get('current_vendor') ?? auth()->user()?->vendor;
        if (!$vendor) {
            abort(403, 'Vendor account required.');
        }

        return $vendor;
    }

    public function index(Request $request): View
    {
        $vendor = $this->resolveVendor($request);
        $projects = PortfolioProject::with(['category', 'serviceProduct'])
            ->where('vendor_id', $vendor->id)
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->latest('id')
            ->paginate(12);

        return view('vendor.portfolio.index', compact('vendor', 'projects'));
    }

    public function create(Request $request): View
    {
        $vendor = $this->resolveVendor($request);
        $categories = Category::where('vertical', 'services')->where('is_active', true)->orderBy('name')->get();
        $services = Product::withoutGlobalScopes()->where('vendor_id', $vendor->id)->where('vertical', 'services')->orderBy('title')->get();

        return view('vendor.portfolio.create', compact('vendor', 'categories', 'services'));
    }

    public function store(Request $request): RedirectResponse
    {
        $vendor = $this->resolveVendor($request);

        $validated = $request->validate([
            'title' => 'required|string|max:160',
            'summary' => 'nullable|string|max:300',
            'description' => 'nullable|string|max:6000',
            'client_name' => 'nullable|string|max:120',
            'role' => 'nullable|string|max:120',
            'work_category' => 'nullable|string|max:80',
            'category_id' => 'nullable|exists:categories,id',
            'service_product_id' => 'nullable|integer',
            'skills_input' => 'nullable|string|max:500',
            'deliverables_input' => 'nullable|string|max:1200',
            'external_url' => 'nullable|url|max:255',
            'completed_at' => 'nullable|date',
            'status' => 'required|in:draft,published',
            'is_featured' => 'nullable|boolean',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:8192',
            'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:8192',
        ]);

        $entitlements = app(\App\Services\Monetization\EntitlementService::class);
        $authCheck = $entitlements->authorizeSectorWrite($vendor, 'services');
        if (!$authCheck['allowed']) {
            return back()->withInput()->with('error', $authCheck['reason']);
        }
        if ($validated['status'] === 'published') {
            $quotaCheck = $entitlements->checkNumericQuota($vendor, 'services', 'max_portfolio_projects', 1);
            if (!$quotaCheck['allowed']) {
                return back()->withInput()->with('error', $quotaCheck['reason']);
            }
        }

        $galleryUploadCount = $request->hasFile('gallery') ? count(array_filter((array) $request->file('gallery'))) : 0;
        if ($galleryUploadCount > 0) {
            $mediaLimit = $entitlements->getNumericLimit($vendor, 'services', 'max_portfolio_media');
            if ($mediaLimit !== null && $galleryUploadCount > $mediaLimit) {
                return back()->withInput()->with('error', "Your current services plan allows up to {$mediaLimit} gallery photo(s) per portfolio case study.");
            }
        }

        $coverPath = null;
        if ($request->hasFile('cover_image') && $request->file('cover_image')->isValid()) {
            $coverPath = $request->file('cover_image')->store('portfolio/' . $vendor->id, 'public');
            k71_sync_public_storage($coverPath);
        }

        $galleryPaths = [];
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                if ($file && $file->isValid()) {
                    $p = $file->store('portfolio/' . $vendor->id, 'public');
                    k71_sync_public_storage($p);
                    $galleryPaths[] = $p;
                }
            }
        }

        $linkedServiceId = null;
        if (!empty($validated['service_product_id'])) {
            $linkedServiceId = Product::withoutGlobalScopes()
                ->where('vendor_id', $vendor->id)
                ->where('id', $validated['service_product_id'])
                ->value('id');
        }

        $skills = collect(explode(',', (string) ($validated['skills_input'] ?? '')))->map(fn ($s) => trim($s))->filter()->values()->all();
        $deliverables = collect(preg_split('/\r\n|\r|\n/', (string) ($validated['deliverables_input'] ?? '')))->map(fn ($s) => trim($s))->filter()->values()->all();

        $baseSlug = Str::slug($validated['title']) ?: 'case-study';
        $slug = $baseSlug;
        $i = 1;
        while (PortfolioProject::where('vendor_id', $vendor->id)->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $i++;
        }

        PortfolioProject::create([
            'vendor_id' => $vendor->id,
            'category_id' => $validated['category_id'] ?? null,
            'service_product_id' => $linkedServiceId,
            'title' => $validated['title'],
            'slug' => $slug,
            'summary' => $validated['summary'] ?? null,
            'description' => $validated['description'] ?? null,
            'client_name' => $validated['client_name'] ?? null,
            'role' => $validated['role'] ?? null,
            'work_category' => $validated['work_category'] ?? null,
            'skills_json' => $skills,
            'deliverables_json' => $deliverables,
            'external_url' => $validated['external_url'] ?? null,
            'cover_image' => $coverPath,
            'gallery_json' => $galleryPaths,
            'completed_at' => $validated['completed_at'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'status' => $validated['status'],
        ]);

        $vendor->bumpWebsiteCache();

        return redirect()->route('vendor.portfolio.index')->with('success', 'Portfolio case study created.');
    }

    public function edit(Request $request, PortfolioProject $portfolio): View
    {
        Gate::authorize('update', $portfolio);
        $vendor = $this->resolveVendor($request);
        $categories = Category::where('vertical', 'services')->where('is_active', true)->orderBy('name')->get();
        $services = Product::withoutGlobalScopes()->where('vendor_id', $vendor->id)->where('vertical', 'services')->orderBy('title')->get();

        return view('vendor.portfolio.edit', ['vendor' => $vendor, 'project' => $portfolio, 'categories' => $categories, 'services' => $services]);
    }

    public function update(Request $request, PortfolioProject $portfolio): RedirectResponse
    {
        Gate::authorize('update', $portfolio);
        $vendor = $this->resolveVendor($request);

        $validated = $request->validate([
            'title' => 'required|string|max:160',
            'summary' => 'nullable|string|max:300',
            'description' => 'nullable|string|max:6000',
            'client_name' => 'nullable|string|max:120',
            'role' => 'nullable|string|max:120',
            'work_category' => 'nullable|string|max:80',
            'category_id' => 'nullable|exists:categories,id',
            'service_product_id' => 'nullable|integer',
            'skills_input' => 'nullable|string|max:500',
            'deliverables_input' => 'nullable|string|max:1200',
            'external_url' => 'nullable|url|max:255',
            'completed_at' => 'nullable|date',
            'status' => 'required|in:draft,published',
            'is_featured' => 'nullable|boolean',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:8192',
            'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:8192',
        ]);

        $coverPath = $portfolio->cover_image;
        if ($request->hasFile('cover_image') && $request->file('cover_image')->isValid()) {
            if ($coverPath) {
                k71_delete_public_storage($coverPath);
            }
            $coverPath = $request->file('cover_image')->store('portfolio/' . $vendor->id, 'public');
            k71_sync_public_storage($coverPath);
        }

        $galleryPaths = is_array($portfolio->gallery_json) ? $portfolio->gallery_json : [];
        $entitlements = app(\App\Services\Monetization\EntitlementService::class);
        $authCheck = $entitlements->authorizeSectorWrite($vendor, 'services');
        if (!$authCheck['allowed']) {
            return back()->withInput()->with('error', $authCheck['reason']);
        }
        $galleryUploadCount = $request->hasFile('gallery') ? count(array_filter((array) $request->file('gallery'))) : 0;
        if ($galleryUploadCount > 0) {
            $mediaLimit = $entitlements->getNumericLimit($vendor, 'services', 'max_portfolio_media');
            if ($mediaLimit !== null && (count($galleryPaths) + $galleryUploadCount) > $mediaLimit) {
                return back()->withInput()->with('error', "Your current services plan allows up to {$mediaLimit} gallery photo(s) per portfolio case study.");
            }
        }
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                if ($file && $file->isValid()) {
                    $p = $file->store('portfolio/' . $vendor->id, 'public');
                    k71_sync_public_storage($p);
                    $galleryPaths[] = $p;
                }
            }
        }

        $linkedServiceId = null;
        if (!empty($validated['service_product_id'])) {
            $linkedServiceId = Product::withoutGlobalScopes()
                ->where('vendor_id', $vendor->id)
                ->where('id', $validated['service_product_id'])
                ->value('id');
        }

        $skills = collect(explode(',', (string) ($validated['skills_input'] ?? '')))->map(fn ($s) => trim($s))->filter()->values()->all();
        $deliverables = collect(preg_split('/\r\n|\r|\n/', (string) ($validated['deliverables_input'] ?? '')))->map(fn ($s) => trim($s))->filter()->values()->all();

        $portfolio->update([
            'category_id' => $validated['category_id'] ?? null,
            'service_product_id' => $linkedServiceId,
            'title' => $validated['title'],
            'summary' => $validated['summary'] ?? null,
            'description' => $validated['description'] ?? null,
            'client_name' => $validated['client_name'] ?? null,
            'role' => $validated['role'] ?? null,
            'work_category' => $validated['work_category'] ?? null,
            'skills_json' => $skills,
            'deliverables_json' => $deliverables,
            'external_url' => $validated['external_url'] ?? null,
            'cover_image' => $coverPath,
            'gallery_json' => $galleryPaths,
            'completed_at' => $validated['completed_at'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'status' => $validated['status'],
        ]);

        $vendor->bumpWebsiteCache();

        return redirect()->route('vendor.portfolio.index')->with('success', 'Portfolio case study updated.');
    }

    public function toggleStatus(Request $request, PortfolioProject $portfolio): RedirectResponse
    {
        Gate::authorize('update', $portfolio);
        $next = $portfolio->status === 'published' ? 'draft' : 'published';
        if ($portfolio->vendor) {
            $entitlements = app(\App\Services\Monetization\EntitlementService::class);
            $authCheck = $entitlements->authorizeSectorWrite($portfolio->vendor, 'services');
            if (!$authCheck['allowed']) {
                return back()->with('error', $authCheck['reason']);
            }
            if ($next === 'published') {
                $quotaCheck = $entitlements->checkNumericQuota($portfolio->vendor, 'services', 'max_portfolio_projects', 1);
                if (!$quotaCheck['allowed']) {
                    return back()->with('error', $quotaCheck['reason']);
                }
            }
        }
        $portfolio->update(['status' => $next]);
        $portfolio->vendor?->bumpWebsiteCache();

        return back()->with('success', $next === 'published' ? 'Case study published on your website.' : 'Case study moved to draft.');
    }

    public function toggleFeatured(Request $request, PortfolioProject $portfolio): RedirectResponse
    {
        Gate::authorize('update', $portfolio);
        $portfolio->update(['is_featured' => !$portfolio->is_featured]);
        $portfolio->vendor?->bumpWebsiteCache();

        return back()->with('success', $portfolio->is_featured ? 'Pinned as Featured Work.' : 'Removed from Featured.');
    }

    public function destroy(Request $request, PortfolioProject $portfolio): RedirectResponse
    {
        Gate::authorize('delete', $portfolio);
        if ($portfolio->cover_image) {
            k71_delete_public_storage($portfolio->cover_image);
        }
        foreach ((array) $portfolio->gallery_json as $g) {
            k71_delete_public_storage($g);
        }
        $vendor = $portfolio->vendor;
        $portfolio->delete();
        $vendor?->bumpWebsiteCache();

        return back()->with('success', 'Portfolio case study deleted.');
    }
}

