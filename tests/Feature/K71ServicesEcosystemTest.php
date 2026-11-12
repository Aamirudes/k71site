<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Enquiry;
use App\Models\PortfolioProject;
use App\Models\Product;
use App\Models\ProductGigPackage;
use App\Models\ProjectContract;
use App\Models\ProjectMilestone;
use App\Models\ProjectProposal;
use App\Models\Review;
use App\Models\Role;
use App\Models\ServiceOrder;
use App\Models\ServiceProject;
use App\Models\StorefrontAnalyticsEvent;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorSubdomainHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class K71ServicesEcosystemTest extends TestCase
{
    use RefreshDatabase;

    private function createServiceCategory(string $name = 'Brand & Web Design', string $slug = 'brand-web-design'): Category
    {
        return Category::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'vertical' => 'services',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );
    }

    private function createServiceProvider(string $email, string $shopName, string $slug, array $overrides = []): array
    {
        $vendorRole = Role::firstOrCreate(['name' => 'vendor'], ['guard_name' => 'web']);
        $customerRole = Role::firstOrCreate(['name' => 'customer'], ['guard_name' => 'web']);

        $user = User::factory()->create([
            'email' => $email,
            'email_verified_at' => now(),
        ]);
        $user->roles()->syncWithoutDetaching([$customerRole->id, $vendorRole->id]);

        $vendor = Vendor::create(array_merge([
            'user_id' => $user->id,
            'shop_name' => $shopName,
            'slug' => $slug,
            'vendor_type' => 'services',
            'provider_mode' => 'individual',
            'headline' => "{$shopName} Studio Headline",
            'tagline' => 'High-craft design & full-stack digital engineering',
            'description' => 'We architect reliable digital products, design systems, and brand identities.',
            'skills_json' => ['Laravel', 'Figma', 'Tailwind CSS'],
            'languages_json' => ['English', 'Hindi'],
            'work_mode' => 'remote',
            'availability_status' => 'available',
            'city' => 'Bengaluru',
            'state' => 'Karnataka',
            'contact_email' => $email,
            'contact_phone' => '9876543210',
            'email' => $email,
            'status' => 'approved',
            'approved_at' => now(),
            'website_status' => 'published',
            'website_published_at' => now(),
            'layout_preset' => 'velora',
            'public_contact_preferences' => [
                'show_email' => true,
                'show_phone' => true,
                'show_city' => true,
                'allow_direct_enquiry' => true,
                'allow_k71_chat' => true,
            ],
        ], $overrides));

        $this->grantVendorTestSubscription($vendor, 'services');

        return [$user, $vendor];
    }

    public function test_five_step_onboarding_draft_vs_published_website_gating(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $otherUser = User::factory()->create(['email_verified_at' => now()]);

        // Step 1: Identity
        $this->actingAs($user)
            ->post(route('services.onboarding.save'), [
                'step' => 1,
                'provider_mode' => 'agency',
                'shop_name' => 'Pixel Studio India',
                'headline' => 'Boutique Brand & Laravel Product Studio',
                'description' => 'We partner with startups to ship high-converting brand systems and web platforms.',
                'skills' => 'Brand Identity, UI/UX, Laravel',
                'languages' => 'English, Hindi',
                'work_mode' => 'remote',
                'city' => 'Mumbai',
                'hourly_rate' => 2500,
                'years_experience' => 7,
                'contact_email' => 'hello@pixelstudio.test',
                'contact_phone' => '9123456789',
            ])
            ->assertRedirect(route('services.onboarding', ['step' => 2]));

        $vendor = $user->fresh()->vendor;
        $this->assertNotNull($vendor);
        $this->assertSame('draft', $vendor->website_status);
        $this->assertFalse($vendor->isWebsitePublished());

        // Step 2: Claim Subdomain
        $this->actingAs($user)
            ->post(route('services.onboarding.save'), [
                'step' => 2,
                'subdomain' => 'pixelstudio',
            ])
            ->assertRedirect(route('services.onboarding', ['step' => 3]));

        $vendor->refresh();
        $this->assertSame('pixelstudio', $vendor->slug);

        // Step 3: Pick existing preset
        $this->actingAs($user)
            ->post(route('services.onboarding.save'), [
                'step' => 3,
                'preset_key' => 'orbit',
            ])
            ->assertRedirect(route('services.onboarding', ['step' => 4]));

        $this->assertSame('orbit', $vendor->fresh()->layout_preset);

        // Step 4: Portfolio Setup & inline project creation
        $this->actingAs($user)
            ->post(route('services.onboarding.save'), [
                'step' => 4,
                'hero_headline' => 'Designing Iconic Digital Brands',
                'hero_subtitle' => 'From architecture to launch-ready systems',
                'cta_primary_text' => 'Book Discovery Call',
                'website_link' => 'https://pixelstudio.example.com',
                'linkedin_link' => 'https://linkedin.com/company/pixelstudio',
                'portfolio_title' => 'Fintech Mobile Redesign',
                'portfolio_category' => 'Product Design',
                'portfolio_summary' => 'Redesigned onboarding flow increasing activation by 38%.',
            ])
            ->assertRedirect(route('services.onboarding', ['step' => 5]));

        $project = PortfolioProject::where('vendor_id', $vendor->id)->firstOrFail();

        // While website is in DRAFT: public visitors on subdomain & /s/{slug} receive 404 + noindex
        $this->actingAs($otherUser);
        $draftSubdomain = $this->get('http://pixelstudio.k71.in/');
        $draftSubdomain->assertStatus(404);
        $draftSubdomain->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        $draftPath = $this->get('/s/pixelstudio');
        $draftPath->assertStatus(404);
        $draftPath->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        $draftPortfolio = $this->get("http://pixelstudio.k71.in/portfolio/{$project->slug}");
        $draftPortfolio->assertStatus(404);
        $draftPortfolio->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        // Unrelated user cannot bypass draft with ?preview=1
        $this->get('http://pixelstudio.k71.in/?preview=1')->assertStatus(404);

        // Excluded from public store sitemap while in draft
        $sitemap = $this->get('/sitemap-stores.xml');
        $sitemap->assertOk();
        $sitemap->assertDontSee('pixelstudio');

        // Owner CAN preview via ?preview=1 with noindex
        $this->actingAs($user);
        $ownerPreview = $this->get('http://pixelstudio.k71.in/?preview=1');
        $ownerPreview->assertOk();
        $ownerPreview->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $ownerPreview->assertSee('Preview Mode');
        $ownerPreview->assertSee('Designing Iconic Digital Brands');
        $ownerPreview->assertSee('Fintech Mobile Redesign');

        // Step 5: Admin verifies & activates services subscription before publishing live
        $this->grantVendorTestSubscription($vendor->fresh(), 'services');

        // Step 5: Publish live
        $this->actingAs($user)
            ->post(route('services.onboarding.save'), [
                'step' => 5,
                'publish_action' => 'publish',
            ])
            ->assertRedirect(route('vendor.website.index'));

        $this->assertTrue($vendor->fresh()->isWebsitePublished());

        // Anonymous visitor now sees live personal website on subdomain & path without noindex
        auth()->logout();
        $liveSubdomain = $this->get('http://pixelstudio.k71.in/');
        $liveSubdomain->assertOk();
        $this->assertNull($liveSubdomain->headers->get('X-Robots-Tag'));
        $liveSubdomain->assertSee('Designing Iconic Digital Brands');
        $liveSubdomain->assertSee('Fintech Mobile Redesign');
        $liveSubdomain->assertDontSee('Preview Mode');

        $livePath = $this->get('/s/pixelstudio');
        $livePath->assertOk();
        $livePath->assertSee('Designing Iconic Digital Brands');

        // Sitemap now includes published website
        $sitemapLive = $this->get('/sitemap-stores.xml');
        $sitemapLive->assertOk();
        $sitemapLive->assertSee('pixelstudio');
    }

    public function test_subdomain_availability_reserved_names_cooldown_and_301_redirects(): void
    {
        [$userA, $vendorA] = $this->createServiceProvider('arjun@example.com', 'Arjun Works', 'arjun-design');
        [$userB, $vendorB] = $this->createServiceProvider('other@example.com', 'Other Studio', 'other-studio');

        // Reserved subdomains blocked
        foreach (['admin', 'services', 'api', 'dashboard', 'www'] as $reserved) {
            $this->actingAs($userB)
                ->getJson(route('services.check_subdomain', ['slug' => $reserved]))
                ->assertOk()
                ->assertJson(['available' => false]);
        }

        // Duplicate blocked
        $this->actingAs($userB)
            ->getJson(route('services.check_subdomain', ['slug' => 'arjun-design']))
            ->assertOk()
            ->assertJson(['available' => false]);

        // Valid fresh subdomain available
        $this->actingAs($userA)
            ->getJson(route('services.check_subdomain', ['slug' => 'arjun-studio']))
            ->assertOk()
            ->assertJson(['available' => true, 'slug' => 'arjun-studio']);

        // Create a portfolio project on Vendor A
        $project = PortfolioProject::create([
            'vendor_id' => $vendorA->id,
            'title' => 'Brand Redesign',
            'slug' => 'brand-redesign',
            'work_category' => 'Branding',
            'status' => 'published',
        ]);

        // Rename subdomain arjun-design -> arjun-studio
        $this->actingAs($userA)
            ->post(route('vendor.website.subdomain'), ['slug' => 'arjun-studio'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertSame('arjun-studio', $vendorA->fresh()->slug);

        $history = VendorSubdomainHistory::where('vendor_id', $vendorA->id)
            ->where('slug', 'arjun-design')
            ->first();
        $this->assertNotNull($history);
        $this->assertTrue((bool) $history->is_active_redirect);
        $this->assertTrue($history->cooldown_until->isFuture());

        // Cooldown prevents Vendor B from claiming old slug
        $this->actingAs($userB)
            ->getJson(route('services.check_subdomain', ['slug' => 'arjun-design']))
            ->assertOk()
            ->assertJson(['available' => false]);

        // Visiting old subdomain or /s/{old} issues 301 permanent redirect to new host
        auth()->logout();
        $oldSubdomainResp = $this->get('http://arjun-design.k71.in/');
        $oldSubdomainResp->assertStatus(301);
        $this->assertStringContainsString('arjun-studio.k71.in', (string) $oldSubdomainResp->headers->get('Location'));

        $oldPathResp = $this->get('/s/arjun-design');
        $oldPathResp->assertStatus(301);
        $this->assertStringContainsString('arjun-studio', (string) $oldPathResp->headers->get('Location'));

        $oldPortfolioResp = $this->get("http://arjun-design.k71.in/portfolio/{$project->slug}");
        $oldPortfolioResp->assertStatus(301);
        $location = (string) $oldPortfolioResp->headers->get('Location');
        $this->assertStringContainsString('arjun-studio.k71.in', $location);
        $this->assertStringContainsString("portfolio/{$project->slug}", $location);

        // New subdomain resolves live
        $this->get('http://arjun-studio.k71.in/')->assertOk()->assertSee('Brand Redesign');
    }

    public function test_non_destructive_preset_switching_and_all_10_presets_service_separation(): void
    {
        $cat = $this->createServiceCategory();
        [$user, $vendor] = $this->createServiceProvider('presets@example.com', 'Atelier Aura', 'atelier-aura');

        // Custom content before switching
        $this->actingAs($user)
            ->post(route('vendor.website.identity'), [
                'shop_name' => 'Atelier Aura',
                'provider_mode' => 'agency',
                'headline' => 'Custom Signature Headline Retained',
                'hero_subtitle' => 'Independent Product & Architecture Consultancy',
                'description' => 'We deliver end-to-end systems design with long-term engineering retainers.',
                'work_mode' => 'hybrid',
                'availability_status' => 'available',
                'skills_input' => 'Architecture, Prototyping, Figma',
            ])
            ->assertSessionHasNoErrors();

        $portfolio = PortfolioProject::create([
            'vendor_id' => $vendor->id,
            'category_id' => $cat->id,
            'title' => 'Enterprise Ledger Interface',
            'slug' => 'enterprise-ledger-interface',
            'work_category' => 'UI/UX',
            'client_name' => 'FinEdge India',
            'role' => 'Lead Product Designer',
            'summary' => 'Zero-latency multi-tenant financial cockpit.',
            'description' => 'Full redesign and component library implementation.',
            'deliverables_json' => ['Design System', 'Figma Prototype'],
            'skills_json' => ['Figma', 'Tailwind'],
            'status' => 'published',
            'is_featured' => true,
        ]);

        $service = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $cat->id,
            'title' => 'Full SaaS UX Audit & Redesign',
            'slug' => 'full-saas-ux-audit-redesign',
            'description' => 'Detailed heuristic analysis and interactive prototype overhaul.',
            'vertical' => 'services',
            'listing_type' => 'gig',
            'base_price' => 1500000,
            'gst_rate' => 18.00,
            'status' => 'active',
            'show_on_website' => true,
        ]);

        ProductGigPackage::create([
            'product_id' => $service->id,
            'tier' => 'basic',
            'title' => 'Core Audit',
            'description' => 'Heuristic report & loom walkthrough',
            'price' => 15000,
            'delivery_days' => 5,
            'revisions' => 2,
            'features_json' => ['Video Walkthrough'],
        ]);

        $tenPresets = ['velora', 'noire', 'aurel', 'chroma', 'orbit', 'zenith', 'solaris', 'nexus', 'elysium', 'vogue'];

        foreach ($tenPresets as $preset) {
            $this->actingAs($user)
                ->post(route('vendor.website.preset'), ['preset_key' => $preset])
                ->assertSessionHasNoErrors();

            $vendor->refresh();
            $this->assertSame($preset, $vendor->layout_preset);

            // Render subdomain as public visitor
            auth()->logout();
            $resp = $this->get('http://atelier-aura.k71.in/');
            $resp->assertOk();
            $resp->assertSee('Custom Signature Headline Retained');
            $resp->assertSee('Enterprise Ledger Interface');
            $resp->assertSee('Full SaaS UX Audit');
            $resp->assertSee('Powered by K71 Services');

            // Strictly no retail bag/cart popup bleed on any preset
            $resp->assertDontSee('k71-glass-cart-popup');
            $resp->assertDontSee('+ ADD TO BAG');
            $resp->assertDontSee('+ DOCK TO BAG');
            $resp->assertDontSee('+ Add to Bag');

            // Path fallback also renders cleanly
            $this->get('/s/atelier-aura')->assertOk()->assertSee('Custom Signature Headline Retained');
        }

        // Case study detail page renders cleanly on both subdomain and path fallback & records telemetry
        $detailSub = $this->get("http://atelier-aura.k71.in/portfolio/{$portfolio->slug}");
        $detailSub->assertOk();
        $detailSub->assertSee('Enterprise Ledger Interface');
        $detailSub->assertSee('FinEdge India');
        $detailSub->assertSee('Design System');

        $this->get("/s/atelier-aura/portfolio/{$portfolio->slug}")
            ->assertOk()
            ->assertSee('Enterprise Ledger Interface');

        $this->assertDatabaseHas('storefront_analytics_events', [
            'vendor_id' => $vendor->id,
            'event_type' => 'portfolio_view',
            'entity_id' => $portfolio->id,
        ]);
    }

    public function test_single_source_of_truth_between_dashboard_marketplace_and_personal_website(): void
    {
        $cat = $this->createServiceCategory('Web Development', 'web-development');
        [$user, $vendor] = $this->createServiceProvider('ssot@example.com', 'CodeCraft Studio', 'codecraft-studio');

        // Create service via Dashboard Services Studio (NOT duplicated separately for website)
        $this->actingAs($user)
            ->post(route('vendor.services.store'), [
                'title' => 'Laravel SaaS Architecture Build',
                'category_id' => $cat->id,
                'description' => 'Complete production-ready multi-tenant SaaS application architecture with automated tests.',
                'basic_title' => 'MVP Foundation',
                'basic_description' => 'Core auth, billing and dashboard setup',
                'basic_price' => 25000,
                'basic_delivery_days' => 10,
                'basic_revisions' => 2,
            ])
            ->assertRedirect(route('vendor.services.index'));

        $product = Product::withoutGlobalScopes()
            ->where('vendor_id', $vendor->id)
            ->where('vertical', 'services')
            ->firstOrFail();

        // Visible on BOTH K71 Services Marketplace and personal website
        auth()->logout();
        $this->get('/services')->assertOk()->assertSee('Laravel SaaS Architecture Build');
        $this->get('http://codecraft-studio.k71.in/')->assertOk()->assertSee('Laravel SaaS Architecture Build');

        // Edit in dashboard -> reflected immediately on both
        $this->actingAs($user)
            ->put(route('vendor.services.update', $product), [
                'title' => 'Enterprise Laravel Platform V2',
                'category_id' => $cat->id,
                'description' => 'Upgraded multi-region SaaS architecture with automated CI verification.',
                'base_price' => 35000,
                'show_on_website' => 1,
            ])
            ->assertRedirect(route('vendor.services.index'));

        auth()->logout();
        $this->get(route('services.show', $product->slug))->assertOk()->assertSee('Enterprise Laravel Platform V2');
        $this->get('http://codecraft-studio.k71.in/')->assertOk()->assertSee('Enterprise Laravel Platform V2');

        // Hide from personal website while keeping live on marketplace
        $this->actingAs($user)
            ->patch(route('vendor.services.toggle_website', $product))
            ->assertSessionHas('success');

        auth()->logout();
        $this->get('/services')->assertOk()->assertSee('Enterprise Laravel Platform V2');
        $this->get('http://codecraft-studio.k71.in/')->assertOk()->assertDontSee('Enterprise Laravel Platform V2');

        // Pause status -> hidden from marketplace directory
        $this->actingAs($user)
            ->patch(route('vendor.services.toggle_status', $product))
            ->assertSessionHas('success');

        auth()->logout();
        $this->get('/services')->assertOk()->assertDontSee('Enterprise Laravel Platform V2');
    }

    public function test_full_upwork_custom_project_proposal_interview_hire_milestone_and_review_lifecycle(): void
    {
        Storage::fake('public');
        $cat = $this->createServiceCategory('Software Engineering', 'software-engineering');

        $buyer = User::factory()->create(['first_name' => 'Rohan', 'last_name' => 'Mehta', 'email_verified_at' => now()]);
        [$userA, $vendorA] = $this->createServiceProvider('pro-a@example.com', 'Alpha Labs', 'alpha-labs');
        [$userB, $vendorB] = $this->createServiceProvider('pro-b@example.com', 'Beta Works', 'beta-works');

        $sampleA = PortfolioProject::create([
            'vendor_id' => $vendorA->id,
            'title' => 'Alpha Banking Core',
            'slug' => 'alpha-banking-core',
            'status' => 'published',
        ]);
        $sampleB = PortfolioProject::create([
            'vendor_id' => $vendorB->id,
            'title' => 'Beta Retail App',
            'slug' => 'beta-retail-app',
            'status' => 'published',
        ]);

        // Buyer publishes custom project brief
        $this->actingAs($buyer)
            ->post(route('services.projects.store'), [
                'title' => 'Build Multi-Vendor Analytics Portal',
                'category_id' => $cat->id,
                'description' => 'Need a clean analytics dashboard with CSV export, tenant isolation, and custom charting.',
                'skills' => 'Laravel, Chart.js, SQL',
                'deliverables' => "Backend API\nInteractive Dashboard",
                'budget_type' => 'fixed',
                'budget_min' => 25000,
                'budget_max' => 45000,
                'expected_duration' => '2 Weeks',
                'work_mode' => 'remote',
                'city' => 'Delhi',
            ])
            ->assertRedirect();

        $project = ServiceProject::firstOrFail();
        $this->get(route('services.projects.index'))->assertOk()->assertSee('Build Multi-Vendor Analytics Portal');

        // Provider A submits proposal trying to spoof Provider B's portfolio sample ID
        $this->actingAs($userA)
            ->post(route('services.proposals.store', $project), [
                'cover_letter' => 'We have delivered three production multi-tenant dashboards with full automated test suites.',
                'bid_amount' => 30000,
                'delivery_days' => 12,
                'pricing_type' => 'milestone',
                'portfolio_project_ids' => [$sampleA->id, $sampleB->id],
                'milestones' => [
                    ['title' => 'Architecture & DB', 'amount' => 12000, 'description' => 'Schema & API layer'],
                    ['title' => 'UI & Launch', 'amount' => 18000, 'description' => 'Interactive charts & tests'],
                ],
            ])
            ->assertSessionHas('success');

        $proposalA = ProjectProposal::where('vendor_id', $vendorA->id)->firstOrFail();
        // Cross-vendor portfolio spoofing is stripped server-side
        $this->assertSame([$sampleA->id], $proposalA->portfolio_project_ids_json);

        // Provider B submits competing proposal
        $this->actingAs($userB)
            ->post(route('services.proposals.store', $project), [
                'cover_letter' => 'Beta Works can deliver your analytics portal cleanly on a tight timeline.',
                'bid_amount' => 32000,
                'delivery_days' => 14,
                'pricing_type' => 'fixed',
            ])
            ->assertSessionHas('success');

        $proposalB = ProjectProposal::where('vendor_id', $vendorB->id)->firstOrFail();

        // Buyer reviews brief, shortlists Provider A, initiates interview conversation, then hires Provider A
        $this->actingAs($buyer)
            ->get(route('services.projects.show', $project))
            ->assertOk()
            ->assertSee('Alpha Labs')
            ->assertSee('Alpha Banking Core')
            ->assertSee('Beta Works');

        $this->actingAs($buyer)
            ->post(route('services.proposals.shortlist', $proposalA))
            ->assertSessionHas('success');
        $this->assertSame('shortlisted', $proposalA->fresh()->status);

        $interviewResp = $this->actingAs($buyer)
            ->post(route('services.proposals.interview', $proposalA));
        $interviewResp->assertRedirect();
        $this->assertDatabaseHas('conversations', [
            'vendor_id' => $vendorA->id,
            'user_id' => $buyer->id,
        ]);

        // Atomic single-winner hire
        $this->actingAs($buyer)
            ->post(route('services.proposals.hire', $proposalA))
            ->assertRedirect();

        $contract = ProjectContract::where('project_id', $project->id)->firstOrFail();
        $this->assertSame('hired', $proposalA->fresh()->status);
        $this->assertSame('rejected', $proposalB->fresh()->status);
        $this->assertSame('in_progress', $project->fresh()->status);
        $this->assertCount(2, $contract->milestones);

        // Second hire attempt blocked
        $this->actingAs($buyer)
            ->post(route('services.proposals.hire', $proposalB))
            ->assertSessionHas('error');
        $this->assertSame(1, ProjectContract::where('project_id', $project->id)->count());

        // Provider A submits milestone 1 with attachment
        $m1 = $contract->milestones()->orderBy('sort_order')->firstOrFail();
        $this->actingAs($userA)
            ->post(route('services.milestones.submit', [$contract, $m1]), [
                'submitted_notes' => 'Schema, API endpoints, and initial benchmark tests are ready for review.',
                'deliverables' => [UploadedFile::fake()->create('architecture.pdf', 120, 'application/pdf')],
            ])
            ->assertSessionHas('success');
        $this->assertSame('submitted', $m1->fresh()->status);

        // Buyer requests revision
        $this->actingAs($buyer)
            ->post(route('services.milestones.revision', [$contract, $m1]), [
                'revision_notes' => 'Please also add pagination metadata to the summary export endpoint.',
            ])
            ->assertSessionHas('info');
        $this->assertSame('revision_requested', $m1->fresh()->status);

        // Provider A resubmits -> Buyer approves -> Completes contract with rating & review
        $this->actingAs($userA)
            ->post(route('services.milestones.submit', [$contract, $m1]), [
                'submitted_notes' => 'Added pagination metadata and re-verified all integration assertions.',
            ])
            ->assertSessionHas('success');

        $this->actingAs($buyer)
            ->post(route('services.milestones.approve', [$contract, $m1]))
            ->assertSessionHas('success');
        $this->assertSame('approved', $m1->fresh()->status);

        $this->actingAs($buyer)
            ->post(route('services.contracts.complete', $contract), [
                'rating' => 5,
                'review_text' => 'Exceptional architecture quality and reliable communication!',
            ])
            ->assertSessionHas('success');

        $contract->refresh();
        $vendorA->refresh();
        $this->assertSame('completed', $contract->status);
        $this->assertSame('released', $contract->payment_status);
        $this->assertSame(5, (int) $contract->buyer_rating);
        $this->assertEquals(5.0, (float) $vendorA->rating);

        // Verified contract review shows up live on Alpha Labs' personal website
        auth()->logout();
        $this->get('http://alpha-labs.k71.in/')
            ->assertOk()
            ->assertSee('Exceptional architecture quality and reliable communication!');
    }

    public function test_service_order_review_reply_privacy_toggles_and_direct_enquiries(): void
    {
        $cat = $this->createServiceCategory('Motion Design', 'motion-design');
        [$vendorUser, $vendor] = $this->createServiceProvider('reply@example.com', 'Motion Forge', 'motion-forge', [
            'contact_phone' => '9988776655',
            'contact_email' => 'private@motionforge.example',
        ]);
        $buyer = User::factory()->create(['first_name' => 'Aarti', 'last_name' => 'Sharma', 'email_verified_at' => now()]);

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $cat->id,
            'title' => 'Product Launch Motion Film',
            'slug' => 'product-launch-motion-film',
            'description' => '3D motion teaser tailored for SaaS and product launches.',
            'vertical' => 'services',
            'listing_type' => 'gig',
            'base_price' => 800000,
            'gst_rate' => 18.00,
            'status' => 'active',
        ]);

        $order = ServiceOrder::create([
            'order_number' => 'SRV-TEST0001',
            'buyer_id' => $buyer->id,
            'vendor_id' => $vendor->id,
            'product_id' => $product->id,
            'requirements' => '30 second teaser for homepage launch.',
            'amount' => 800000,
            'platform_fee' => 80000,
            'vendor_payout' => 720000,
            'revisions_allowed' => 2,
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        // Buyer approves order and leaves verified review
        $this->actingAs($buyer)
            ->post(route('services.workspace.approve', $order), [
                'rating' => 5,
                'review_text' => 'Stunning animation cuts delivered ahead of schedule!',
            ])
            ->assertSessionHas('success');

        $review = Review::where('service_order_id', $order->id)->firstOrFail();

        // Provider replies to the verified review via Layer C dashboard
        $this->actingAs($vendorUser)
            ->post(route('vendor.reviews.reply', $review), [
                'vendor_reply' => 'Thank you Aarti! Thrilled to support your launch.',
            ])
            ->assertSessionHas('success');

        // Reply is visible on BOTH personal website & marketplace provider profile
        auth()->logout();
        $this->get('http://motion-forge.k71.in/')
            ->assertOk()
            ->assertSee('Stunning animation cuts delivered ahead of schedule!')
            ->assertSee('Thank you Aarti! Thrilled to support your launch.');

        $this->get(route('services.provider', $vendor->slug))
            ->assertOk()
            ->assertSee('Thank you Aarti! Thrilled to support your launch.');

        // Privacy test: hide phone and email publicly
        $this->actingAs($vendorUser)
            ->post(route('vendor.website.privacy'), [
                'show_email' => 0,
                'show_phone' => 0,
                'show_city' => 1,
                'allow_direct_enquiry' => 1,
                'allow_k71_chat' => 1,
            ])
            ->assertSessionHas('success');

        auth()->logout();
        $siteResp = $this->get('http://motion-forge.k71.in/');
        $siteResp->assertOk();
        $siteResp->assertDontSee('9988776655');
        $siteResp->assertDontSee('private@motionforge.example');
        $siteResp->assertDontSee('tel:');

        $profileResp = $this->get(route('services.provider', $vendor->slug));
        $profileResp->assertOk();
        $profileResp->assertDontSee('9988776655');
        $profileResp->assertDontSee('private@motionforge.example');

        // Direct enquiries via BOTH subdomain `/enquire` & path fallback `/s/{slug}/enquire`
        $this->actingAs($buyer)
            ->post('http://motion-forge.k71.in/enquire', [
                'name' => 'Aarti Sharma',
                'email' => 'aarti@client.test',
                'phone' => '9000011111',
                'budget' => 15000,
                'service_product_id' => $product->id,
                'subject' => 'Q4 Launch Teaser',
                'message' => 'Looking for a 45-second motion film for our Q4 launch event.',
            ])
            ->assertSessionHas('success');

        $this->post('/s/motion-forge/enquire', [
            'name' => 'Vikram Rao',
            'email' => 'vikram@client.test',
            'subject' => 'Explainer Video Quote',
            'message' => 'Could you share a timeline and quote for a 60-second product explainer?',
        ])->assertSessionHas('success');

        $this->assertSame(2, Enquiry::where('vendor_id', $vendor->id)->count());
        $this->assertSame(2, StorefrontAnalyticsEvent::where('vendor_id', $vendor->id)->where('event_type', 'enquiry_submit')->count());

        // Provider views enquiries and analytics in Layer C dashboard
        $this->actingAs($vendorUser);
        $this->get(route('vendor.enquiries.index'))
            ->assertOk()
            ->assertSee('Q4 Launch Teaser')
            ->assertSee('Explainer Video Quote');

        $this->get(route('vendor.analytics.index'))
            ->assertOk()
            ->assertSee('Analytics');
    }

    public function test_tenant_isolation_and_provider_authorization_guards(): void
    {
        $cat = $this->createServiceCategory('Consulting', 'consulting');
        [$userA, $vendorA] = $this->createServiceProvider('owner-a@example.com', 'Owner A Studio', 'owner-a');
        [$userB, $vendorB] = $this->createServiceProvider('intruder-b@example.com', 'Intruder B Studio', 'intruder-b');
        $buyer = User::factory()->create(['email_verified_at' => now()]);

        $portfolioA = PortfolioProject::create([
            'vendor_id' => $vendorA->id,
            'title' => 'Private Case Study',
            'slug' => 'private-case-study',
            'status' => 'published',
        ]);

        $serviceA = Product::create([
            'vendor_id' => $vendorA->id,
            'category_id' => $cat->id,
            'title' => 'Advisory Retainer',
            'slug' => 'advisory-retainer',
            'description' => 'Monthly strategic engineering guidance.',
            'vertical' => 'services',
            'listing_type' => 'gig',
            'base_price' => 500000,
            'gst_rate' => 18.00,
            'status' => 'active',
        ]);

        $reviewA = Review::create([
            'product_id' => $serviceA->id,
            'vendor_id' => $vendorA->id,
            'user_id' => $buyer->id,
            'rating' => 5,
            'body' => 'Great retainer session.',
            'is_approved' => true,
        ]);

        $project = ServiceProject::create([
            'buyer_id' => $buyer->id,
            'category_id' => $cat->id,
            'project_number' => 'PRJ-GUARD01',
            'title' => 'Confidential Advisory Audit',
            'slug' => 'confidential-advisory-audit',
            'description' => 'Audit our API latency across multiple regions.',
            'budget_type' => 'fixed',
            'budget_min' => 1000000,
            'status' => 'in_progress',
            'hired_vendor_id' => $vendorA->id,
        ]);

        $proposalA = ProjectProposal::create([
            'project_id' => $project->id,
            'vendor_id' => $vendorA->id,
            'cover_letter' => 'We can benchmark all regions within five days.',
            'bid_amount' => 1000000,
            'delivery_days' => 5,
            'status' => 'hired',
        ]);

        $contractA = ProjectContract::create([
            'contract_number' => 'CON-GUARD01',
            'project_id' => $project->id,
            'proposal_id' => $proposalA->id,
            'buyer_id' => $buyer->id,
            'vendor_id' => $vendorA->id,
            'agreed_amount' => 1000000,
            'payment_status' => 'escrow_funded',
            'status' => 'active',
            'buyer_rating' => 5,
            'buyer_review' => 'Solid work',
        ]);

        $milestoneA = ProjectMilestone::create([
            'contract_id' => $contractA->id,
            'title' => 'Phase 1',
            'amount' => 1000000,
            'sort_order' => 1,
            'status' => 'pending',
        ]);

        // Vendor B blocked across all Vendor A resources
        $this->actingAs($userB);
        $this->get(route('vendor.portfolio.edit', $portfolioA))->assertStatus(403);
        $this->delete(route('vendor.portfolio.destroy', $portfolioA))->assertStatus(403);
        $this->patch(route('vendor.portfolio.toggle_status', $portfolioA))->assertStatus(403);

        $this->assertTrue(in_array($this->get(route('vendor.services.edit', $serviceA))->getStatusCode(), [403, 404], true));
        $this->assertTrue(in_array($this->delete(route('vendor.services.destroy', $serviceA))->getStatusCode(), [403, 404], true));

        $this->post(route('vendor.reviews.reply', $reviewA), ['vendor_reply' => 'Hijack attempt'])->assertStatus(403);
        $this->post(route('vendor.reviews.contract_reply', $contractA), ['vendor_reply' => 'Hijack attempt'])->assertStatus(403);

        $this->post(route('services.milestones.submit', [$contractA, $milestoneA]), [
            'submitted_notes' => 'Unauthorized milestone submission attempt.',
        ])->assertStatus(403);

        // Non-buyer cannot approve milestone
        $this->actingAs($userA)
            ->post(route('services.milestones.approve', [$contractA, $milestoneA]))
            ->assertStatus(403);
    }

    public function test_retail_vendor_storefront_remains_unaffected(): void
    {
        $vendorRole = Role::firstOrCreate(['name' => 'vendor'], ['guard_name' => 'web']);
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->roles()->syncWithoutDetaching([$vendorRole->id]);

        $retailCat = Category::firstOrCreate(
            ['slug' => 'apparel-test'],
            ['name' => 'Apparel', 'vertical' => 'marketplace', 'is_active' => true]
        );

        $retailVendor = Vendor::create([
            'user_id' => $user->id,
            'shop_name' => 'Loom Heritage Clothing',
            'slug' => 'loom-heritage',
            'vendor_type' => 'clothing',
            'status' => 'approved',
            'approved_at' => now(),
            'email' => $user->email,
            'layout_preset' => 'velora',
        ]);
        $this->grantVendorTestSubscription($retailVendor, 'products');

        Product::create([
            'vendor_id' => $retailVendor->id,
            'category_id' => $retailCat->id,
            'title' => 'Handwoven Kurta Set',
            'slug' => 'handwoven-kurta-set',
            'description' => 'Pure cotton handloom kurta set.',
            'base_price' => 249900,
            'gst_rate' => 5.00,
            'status' => 'active',
            'total_stock' => 10,
        ]);

        $resp = $this->get('/s/loom-heritage');
        $resp->assertOk();
        $resp->assertSee('Loom Heritage Clothing');
        $resp->assertSee('Handwoven Kurta Set');
        $resp->assertSee('k71-glass-cart-popup');
    }
}

