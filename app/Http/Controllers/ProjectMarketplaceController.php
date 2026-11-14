<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\PortfolioProject;
use App\Models\ProjectContract;
use App\Models\ProjectMilestone;
use App\Models\ProjectProposal;
use App\Models\ServiceProject;
use App\Services\MediaUploadService;
use App\Services\NotificationCenterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectMarketplaceController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $catSlug = (string) $request->query('cat', '');
        $workMode = (string) $request->query('work_mode', '');
        $budgetType = (string) $request->query('budget_type', '');

        $categories = Category::where('vertical', 'services')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $query = ServiceProject::with(['buyer', 'category'])
            ->open()
            ->latest('id');

        if ($search !== '') {
            $escaped = addcslashes($search, '%_\\');
            $query->where(function ($q) use ($escaped) {
                $q->where('title', 'like', "%{$escaped}%")
                    ->orWhere('description', 'like', "%{$escaped}%")
                    ->orWhere('city', 'like', "%{$escaped}%");
            });
        }

        if ($catSlug !== '') {
            $query->whereHas('category', fn ($q) => $q->where('slug', $catSlug));
        }

        if (in_array($workMode, ['remote', 'onsite', 'hybrid'], true)) {
            $query->where('work_mode', $workMode);
        }

        if (in_array($budgetType, ['fixed', 'hourly'], true)) {
            $query->where('budget_type', $budgetType);
        }

        $projects = $query->paginate(12)->withQueryString();

        $myProjects = auth()->check()
            ? ServiceProject::with('contract')->where('buyer_id', auth()->id())->latest('id')->take(6)->get()
            : collect();

        return view('services.projects.index', compact(
            'projects',
            'categories',
            'search',
            'catSlug',
            'workMode',
            'budgetType',
            'myProjects'
        ));
    }

    public function create(): View
    {
        $categories = Category::where('vertical', 'services')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('services.projects.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|min:8|max:200',
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('vertical', 'services')],
            'description' => 'required|string|min:20|max:5000',
            'skills' => 'nullable|string|max:400',
            'deliverables' => 'nullable|string|max:1000',
            'budget_type' => 'required|in:fixed,hourly',
            'budget_min' => 'required|numeric|min:100',
            'budget_max' => 'nullable|numeric|gte:budget_min',
            'expected_duration' => 'nullable|string|max:80',
            'deadline_at' => 'nullable|date|after_or_equal:today',
            'work_mode' => 'required|in:remote,onsite,hybrid',
            'city' => 'nullable|string|max:100',
        ]);

        $skills = !empty($validated['skills'])
            ? array_values(array_filter(array_map('trim', explode(',', $validated['skills']))))
            : [];
        $deliverables = !empty($validated['deliverables'])
            ? array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $validated['deliverables']))))
            : [];

        $baseSlug = Str::limit(Str::slug($validated['title']) ?: 'project', 180, '');
        $slug = $baseSlug . '-' . strtolower(Str::random(6));

        $project = ServiceProject::create([
            'buyer_id' => auth()->id(),
            'category_id' => $validated['category_id'] ?? null,
            'project_number' => 'PRJ-' . strtoupper(Str::random(8)),
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'],
            'skills_json' => $skills,
            'deliverables_json' => $deliverables,
            'budget_type' => $validated['budget_type'],
            'budget_min' => (int) round(((float) $validated['budget_min']) * 100),
            'budget_max' => !empty($validated['budget_max']) ? (int) round(((float) $validated['budget_max']) * 100) : null,
            'expected_duration' => $validated['expected_duration'] ?? '1 – 2 Weeks',
            'deadline_at' => $validated['deadline_at'] ?? null,
            'work_mode' => $validated['work_mode'],
            'city' => ($validated['city'] ?? null) ?: 'Remote',
            'status' => 'open',
        ]);

        return redirect()->route('services.projects.show', $project)
            ->with('success', 'Your project brief (' . $project->project_number . ') is live! Service providers can now submit tailored proposals.');
    }

    public function show(ServiceProject $project): View
    {
        $project->load(['buyer', 'category', 'hiredVendor', 'contract']);

        $user = auth()->user();
        $isBuyer = $user && ($user->id === $project->buyer_id || $user->hasRole('admin'));
        $vendor = $user?->vendor;
        $isEligibleProvider = $vendor && $vendor->isServices() && !$isBuyer;

        $proposals = $isBuyer
            ? $project->proposals()->with(['vendor'])->get()
            : collect();

        $myProposal = $isEligibleProvider
            ? $project->proposals()->where('vendor_id', $vendor->id)->first()
            : null;

        $myPortfolioProjects = $isEligibleProvider
            ? PortfolioProject::where('vendor_id', $vendor->id)->where('status', 'published')->latest('id')->get()
            : collect();

        return view('services.projects.show', compact(
            'project',
            'isBuyer',
            'isEligibleProvider',
            'vendor',
            'proposals',
            'myProposal',
            'myPortfolioProjects'
        ));
    }

    public function submitProposal(Request $request, ServiceProject $project): RedirectResponse
    {
        Gate::authorize('submitProposal', $project);
        $vendor = auth()->user()->vendor;

        if (!in_array($project->status, ['open', 'shortlisting'], true)) {
            return back()->with('error', 'This project is no longer accepting new proposals.');
        }

        $deadline = $project->deadline_at ?? $project->expires_at;
        if ($deadline && \Illuminate\Support\Carbon::parse($deadline)->isPast()) {
            return back()->with('error', 'This project brief has passed its deadline and cannot accept proposals.');
        }

        if (ProjectProposal::where('project_id', $project->id)->where('vendor_id', $vendor->id)->exists()) {
            return back()->with('error', 'You have already submitted a proposal for this project.');
        }

        $validated = $request->validate([
            'cover_letter' => 'required|string|min:25|max:4000',
            'bid_amount' => 'required|numeric|min:100',
            'delivery_days' => 'required|integer|min:1|max:365',
            'pricing_type' => 'nullable|in:fixed,hourly,milestone',
            'portfolio_project_ids' => 'nullable|array',
            'portfolio_project_ids.*' => 'integer',
            'milestones' => 'nullable|array|max:6',
            'milestones.*.title' => 'nullable|string|max:150',
            'milestones.*.amount' => 'nullable|numeric|min:1',
            'milestones.*.description' => 'nullable|string|max:500',
        ]);

        // Ensure attached portfolio samples belong strictly to the submitting vendor
        $validPortfolioIds = [];
        if (!empty($validated['portfolio_project_ids'])) {
            $validPortfolioIds = PortfolioProject::where('vendor_id', $vendor->id)
                ->whereIn('id', $validated['portfolio_project_ids'])
                ->pluck('id')
                ->all();
        }

        $cleanMilestones = [];
        if (!empty($validated['milestones']) && is_array($validated['milestones'])) {
            foreach ($validated['milestones'] as $m) {
                if (!empty($m['title']) && !empty($m['amount']) && (float) $m['amount'] > 0) {
                    $cleanMilestones[] = [
                        'title' => trim($m['title']),
                        'amount_paise' => (int) round(((float) $m['amount']) * 100),
                        'description' => trim((string) ($m['description'] ?? '')),
                    ];
                }
            }
        }

        $proposal = DB::transaction(function () use ($project, $vendor, $validated, $validPortfolioIds, $cleanMilestones) {
            $created = ProjectProposal::create([
                'project_id' => $project->id,
                'vendor_id' => $vendor->id,
                'cover_letter' => $validated['cover_letter'],
                'bid_amount' => (int) round(((float) $validated['bid_amount']) * 100),
                'delivery_days' => (int) $validated['delivery_days'],
                'pricing_type' => $validated['pricing_type'] ?? $project->budget_type,
                'portfolio_project_ids_json' => $validPortfolioIds,
                'proposed_milestones_json' => $cleanMilestones,
                'status' => 'pending',
            ]);

            $project->increment('proposals_count');
            return $created;
        });

        app(NotificationCenterService::class)->notifyUser(
            $project->buyer_id,
            '💼',
            'New Project Proposal Received',
            "{$vendor->shop_name} submitted a bid of {$proposal->formatted_bid} on \"{$project->title}\".",
            route('services.projects.show', $project->slug),
            'services_proposal',
            'project_proposal_' . $proposal->id
        );

        return back()->with('success', 'Proposal submitted with your portfolio samples!');
    }

    public function shortlistProposal(ProjectProposal $proposal): RedirectResponse
    {
        Gate::authorize('decide', $proposal);
        if (!in_array($proposal->status, ['pending', 'shortlisted'], true)) {
            return back()->with('error', 'Only pending proposals can be shortlisted.');
        }

        $proposal->update(['status' => 'shortlisted']);
        if ($proposal->project && $proposal->project->status === 'open') {
            $proposal->project->update(['status' => 'shortlisting']);
        }

        app(NotificationCenterService::class)->notifyUser(
            $proposal->vendor?->user_id,
            '⭐',
            'Proposal Shortlisted!',
            "Your proposal for \"{$proposal->project?->title}\" has been shortlisted by the client.",
            route('services.projects.show', $proposal->project?->slug ?? ''),
            'services_proposal',
            'proposal_shortlisted_' . $proposal->id
        );

        return back()->with('success', $proposal->vendor->shop_name . ' marked as Shortlisted.');
    }

    public function rejectProposal(ProjectProposal $proposal): RedirectResponse
    {
        Gate::authorize('decide', $proposal);
        if ($proposal->status === 'hired') {
            return back()->with('error', 'Cannot reject an already hired proposal.');
        }

        $proposal->update(['status' => 'rejected']);

        app(NotificationCenterService::class)->notifyUser(
            $proposal->vendor?->user_id,
            'ℹ️',
            'Proposal Update',
            "Your proposal on \"{$proposal->project?->title}\" was declined by the client.",
            route('services.projects.index'),
            'services_proposal',
            'proposal_rejected_' . $proposal->id
        );

        return back()->with('info', 'Proposal declined.');
    }

    public function withdrawProposal(ProjectProposal $proposal): RedirectResponse
    {
        Gate::authorize('withdraw', $proposal);
        $proposal->update(['status' => 'withdrawn']);

        return back()->with('info', 'Your proposal has been withdrawn.');
    }

    public function interviewProposal(ProjectProposal $proposal): RedirectResponse
    {
        Gate::authorize('decide', $proposal);

        $project = $proposal->project;
        $conversation = Conversation::firstOrCreate(
            [
                'vendor_id' => $proposal->vendor_id,
                'user_id' => $project->buyer_id,
                'product_id' => null,
            ],
            [
                'subject' => 'Project Interview: ' . $project->title,
                'last_message_at' => now(),
            ]
        );

        $introBody = "Hi {$proposal->vendor->shop_name}, let's discuss your proposal ({$proposal->formatted_bid}) for \"{$project->title}\".";

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'user',
            'sender_id' => auth()->id(),
            'body' => $introBody,
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        // Mirror interview message into Direct Chat engine so real-time chat opens seamlessly
        try {
            $vendorUserId = (int) ($proposal->vendor?->user_id ?? 0);
            $buyerId = (int) $project->buyer_id;
            if ($vendorUserId > 0 && $buyerId > 0 && $vendorUserId !== $buyerId) {
                MessageController::ensureChatSchema();
                $u1 = min($buyerId, $vendorUserId);
                $u2 = max($buyerId, $vendorUserId);
                $nowStr = now()->toDateTimeString();

                $chatConv = DB::table('chat_conversations')
                    ->where('user_one_id', $u1)
                    ->where('user_two_id', $u2)
                    ->first();

                if (!$chatConv) {
                    $chatConvId = DB::table('chat_conversations')->insertGetId([
                        'user_one_id' => $u1,
                        'user_two_id' => $u2,
                        'last_message_at' => $nowStr,
                        'status' => 'accepted',
                        'requested_by' => $buyerId,
                    ]);
                } else {
                    $chatConvId = $chatConv->id;
                    DB::table('chat_conversations')->where('id', $chatConvId)->update([
                        'last_message_at' => $nowStr,
                        'status' => 'accepted',
                        'user_one_hidden_at' => null,
                        'user_two_hidden_at' => null,
                    ]);
                }

                DB::table('chat_messages')->insert([
                    'conversation_id' => $chatConvId,
                    'sender_id' => $buyerId,
                    'body' => $introBody,
                    'is_read' => 0,
                    'created_at' => $nowStr,
                ]);
            }
        } catch (\Throwable $e) {}

        return redirect()->route('messages.show', $conversation);
    }

    /**
     * Atomic DB-locked single-winner hiring workflow creating contract + milestones.
     */
    public function hireProposal(ProjectProposal $proposal): RedirectResponse
    {
        Gate::authorize('decide', $proposal);

        try {
            $contract = DB::transaction(function () use ($proposal) {
                $lockedProject = ServiceProject::where('id', $proposal->project_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!in_array($lockedProject->status, ['open', 'shortlisting'], true)
                    || ProjectContract::where('project_id', $lockedProject->id)->exists()) {
                    throw new \RuntimeException('A provider has already been hired for this project.');
                }

                $lockedProposal = ProjectProposal::where('id', $proposal->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (in_array($lockedProposal->status, ['rejected', 'withdrawn', 'hired'], true)) {
                    throw new \RuntimeException('This proposal is no longer eligible for hiring.');
                }

                $contract = ProjectContract::create([
                    'contract_number' => 'CON-' . strtoupper(Str::random(8)),
                    'project_id' => $lockedProject->id,
                    'proposal_id' => $lockedProposal->id,
                    'buyer_id' => $lockedProject->buyer_id,
                    'vendor_id' => $lockedProposal->vendor_id,
                    'agreed_amount' => $lockedProposal->bid_amount,
                    'payment_status' => 'escrow_funded',
                    'status' => 'active',
                    'started_at' => now(),
                ]);

                $milestones = is_array($lockedProposal->proposed_milestones_json)
                    ? $lockedProposal->proposed_milestones_json
                    : [];

                if (empty($milestones)) {
                    ProjectMilestone::create([
                        'contract_id' => $contract->id,
                        'title' => 'Complete Project Delivery',
                        'description' => 'Full project deliverables as specified in the project brief and accepted proposal.',
                        'amount' => $lockedProposal->bid_amount,
                        'due_at' => now()->addDays(max(1, (int) $lockedProposal->delivery_days))->toDateString(),
                        'sort_order' => 1,
                        'status' => 'pending',
                    ]);
                } else {
                    foreach (array_values($milestones) as $idx => $m) {
                        ProjectMilestone::create([
                            'contract_id' => $contract->id,
                            'title' => $m['title'] ?? ('Milestone ' . ($idx + 1)),
                            'description' => $m['description'] ?? null,
                            'amount' => (int) ($m['amount_paise'] ?? 0),
                            'due_at' => now()->addDays(max(1, (int) $lockedProposal->delivery_days))->toDateString(),
                            'sort_order' => $idx + 1,
                            'status' => 'pending',
                        ]);
                    }
                }

                $lockedProposal->update(['status' => 'hired']);

                ProjectProposal::where('project_id', $lockedProject->id)
                    ->where('id', '!=', $lockedProposal->id)
                    ->whereIn('status', ['pending', 'shortlisted'])
                    ->update(['status' => 'rejected']);

                $lockedProject->update([
                    'status' => 'in_progress',
                    'hired_vendor_id' => $lockedProposal->vendor_id,
                ]);

                return $contract;
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        app(NotificationCenterService::class)->notifyUser(
            $proposal->vendor?->user_id,
            '🎉',
            'You Have Been Hired!',
            "Contract #{$contract->contract_number} created for \"{$proposal->project?->title}\".",
            route('services.contracts.show', $contract),
            'services_contract',
            'contract_hired_' . $contract->id
        );

        return redirect()->route('services.contracts.show', $contract)
            ->with('success', 'Contract activated! ' . $proposal->vendor->shop_name . ' has been hired and project milestones created.');
    }

    public function showContract(ProjectContract $contract): View
    {
        Gate::authorize('view', $contract);
        $contract->load(['project.category', 'proposal', 'buyer', 'vendor', 'milestones']);

        $isBuyer = auth()->id() === $contract->buyer_id;
        $isVendor = auth()->user()?->vendor?->id === $contract->vendor_id;

        return view('services.contracts.show', compact('contract', 'isBuyer', 'isVendor'));
    }

    public function submitMilestone(Request $request, ProjectContract $contract, ProjectMilestone $milestone): RedirectResponse
    {
        Gate::authorize('submitMilestone', $contract);
        abort_if($milestone->contract_id !== $contract->id, 404);

        if ($contract->status !== 'active') {
            return back()->with('error', 'This contract is no longer active.');
        }

        if ($milestone->status === 'approved') {
            return back()->with('error', 'This milestone is already approved and cannot be re-submitted.');
        }

        $validated = $request->validate([
            'submitted_notes' => 'required|string|min:10|max:3000',
            'deliverables' => 'nullable|array|max:8',
            'deliverables.*' => 'nullable|file|max:20480',
        ]);

        $uploaded = [];
        if ($request->hasFile('deliverables')) {
            $mediaService = app(MediaUploadService::class);
            $dir = 'contracts/' . $contract->contract_number . '/milestones/' . $milestone->id;
            foreach ($request->file('deliverables') as $file) {
                if ($file) {
                    $meta = $mediaService->storeSafeDeliverable($file, $dir, 'deliverables');
                    k71_sync_public_storage($meta['path']);
                    $uploaded[] = $meta;
                }
            }
        }

        try {
            DB::transaction(function () use ($contract, $milestone, $validated, $uploaded) {
                $lockedContract = ProjectContract::whereKey($contract->id)->lockForUpdate()->firstOrFail();
                $lockedMilestone = ProjectMilestone::whereKey($milestone->id)->lockForUpdate()->firstOrFail();

                if ($lockedContract->status !== 'active') {
                    throw new \RuntimeException('This contract is no longer active.');
                }
                if ($lockedMilestone->status === 'approved') {
                    throw new \RuntimeException('This milestone is already approved and cannot be overwritten.');
                }

                $lockedMilestone->update([
                    'status' => 'submitted',
                    'submitted_notes' => $validated['submitted_notes'],
                    'deliverables_json' => !empty($uploaded) ? $uploaded : $lockedMilestone->deliverables_json,
                    'submitted_at' => now(),
                ]);
            });
        } catch (\Throwable $e) {
            foreach ($uploaded as $meta) {
                if (!empty($meta['path']) && function_exists('k71_delete_public_storage')) {
                    k71_delete_public_storage($meta['path']);
                }
            }
            return back()->with('error', $e instanceof \RuntimeException ? $e->getMessage() : 'Failed to submit milestone deliverables.');
        }

        app(NotificationCenterService::class)->notifyUser(
            $contract->buyer_id,
            '📦',
            'Milestone Deliverables Submitted',
            "{$contract->vendor?->shop_name} submitted deliverables for \"{$milestone->title}\".",
            route('services.contracts.show', $contract),
            'services_contract',
            'milestone_submitted_' . $milestone->id . '_' . now()->timestamp
        );

        return back()->with('success', 'Milestone deliverables submitted for buyer review.');
    }

    public function approveMilestone(ProjectContract $contract, ProjectMilestone $milestone): RedirectResponse
    {
        Gate::authorize('reviewMilestone', $contract);
        abort_if($milestone->contract_id !== $contract->id, 404);

        try {
            DB::transaction(function () use ($contract, $milestone) {
                $lockedContract = ProjectContract::whereKey($contract->id)->lockForUpdate()->firstOrFail();
                $lockedMilestone = ProjectMilestone::whereKey($milestone->id)->lockForUpdate()->firstOrFail();

                if ($lockedContract->status !== 'active') {
                    throw new \RuntimeException('This contract is no longer active.');
                }

                if ($lockedMilestone->status === 'approved') {
                    throw new \RuntimeException('This milestone is already approved.');
                }

                if (!in_array($lockedMilestone->status, ['submitted', 'revision_requested'], true)) {
                    throw new \RuntimeException('Provider must submit deliverables before this milestone can be approved.');
                }

                $lockedMilestone->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        app(NotificationCenterService::class)->notifyUser(
            $contract->vendor?->user_id,
            '✅',
            'Milestone Approved!',
            "Client approved milestone \"{$milestone->title}\" on contract #{$contract->contract_number}.",
            route('services.contracts.show', $contract),
            'services_contract',
            'milestone_approved_' . $milestone->id
        );

        return back()->with('success', 'Milestone "' . $milestone->title . '" approved!');
    }

    public function requestMilestoneRevision(Request $request, ProjectContract $contract, ProjectMilestone $milestone): RedirectResponse
    {
        Gate::authorize('reviewMilestone', $contract);
        abort_if($milestone->contract_id !== $contract->id, 404);

        $validated = $request->validate([
            'revision_notes' => 'required|string|min:10|max:2000',
        ]);

        try {
            DB::transaction(function () use ($contract, $milestone, $validated) {
                $lockedContract = ProjectContract::whereKey($contract->id)->lockForUpdate()->firstOrFail();
                $lockedMilestone = ProjectMilestone::whereKey($milestone->id)->lockForUpdate()->firstOrFail();

                if ($lockedContract->status !== 'active') {
                    throw new \RuntimeException('This contract is no longer active.');
                }

                if ($lockedMilestone->status === 'approved') {
                    throw new \RuntimeException('Cannot request revision on an already approved milestone.');
                }

                if ($lockedMilestone->status !== 'submitted') {
                    throw new \RuntimeException('Revisions can only be requested after the provider submits deliverables.');
                }

                $lockedMilestone->update([
                    'status' => 'revision_requested',
                    'revision_notes' => $validated['revision_notes'],
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        app(NotificationCenterService::class)->notifyUser(
            $contract->vendor?->user_id,
            '🔁',
            'Milestone Revision Requested',
            "Client requested updates on milestone \"{$milestone->title}\" for contract #{$contract->contract_number}.",
            route('services.contracts.show', $contract),
            'services_contract',
            'milestone_revision_' . $milestone->id . '_' . now()->timestamp
        );

        return back()->with('info', 'Revision request sent to provider for "' . $milestone->title . '".');
    }

    public function completeContract(Request $request, ProjectContract $contract): RedirectResponse
    {
        Gate::authorize('complete', $contract);

        $validated = $request->validate([
            'rating' => 'nullable|integer|min:1|max:5',
            'review_text' => 'nullable|string|max:2000',
        ]);

        try {
            DB::transaction(function () use ($contract, $validated) {
                $lockedContract = ProjectContract::whereKey($contract->id)->lockForUpdate()->firstOrFail();

                if ($lockedContract->status !== 'active') {
                    throw new \RuntimeException('This contract has already been completed or closed.');
                }

                $lockedContract->milestones()->where('status', '!=', 'approved')->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                ]);

                $updateData = [
                    'status' => 'completed',
                    'payment_status' => 'released',
                    'completed_at' => now(),
                ];

                if (!empty($validated['rating'])) {
                    $updateData['buyer_rating'] = (int) $validated['rating'];
                    $updateData['buyer_review'] = ($validated['review_text'] ?? null) ?: 'Excellent project delivery!';
                    $updateData['reviewed_at'] = now();
                }

                $lockedContract->update($updateData);
                $lockedContract->project?->update(['status' => 'completed']);
            });
        } catch (\RuntimeException $e) {
            return back()->with('info', $e->getMessage());
        }

        $contract->vendor?->recomputeServiceMetrics();

        app(NotificationCenterService::class)->notifyUser(
            $contract->vendor?->user_id,
            '🏆',
            'Contract Completed & Payout Released!',
            "Contract #{$contract->contract_number} has been marked completed by the client.",
            route('services.contracts.show', $contract),
            'services_contract',
            'contract_completed_' . $contract->id
        );

        return back()->with('success', 'Contract completed and verified review recorded!');
    }
}

