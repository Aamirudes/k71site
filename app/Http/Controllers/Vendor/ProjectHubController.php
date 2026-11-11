<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\ProjectContract;
use App\Models\ProjectProposal;
use App\Models\ServiceProject;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectHubController extends Controller
{
    protected function resolveVendor(Request $request)
    {
        $vendor = $request->attributes->get('current_vendor') ?? auth()->user()?->vendor;
        if (!$vendor) {
            abort(403, 'Vendor account required.');
        }

        return $vendor;
    }

    public function projectsIndex(Request $request): View
    {
        $vendor = $this->resolveVendor($request);

        $openBriefs = ServiceProject::with(['category', 'buyer'])
            ->where('status', 'open')
            ->where('buyer_id', '!=', auth()->id())
            ->withCount('proposals')
            ->latest('id')
            ->take(18)
            ->get();

        $myProposals = ProjectProposal::with(['project.buyer', 'project.category'])
            ->where('vendor_id', $vendor->id)
            ->latest('id')
            ->paginate(15);

        return view('vendor.projects.index', compact('vendor', 'openBriefs', 'myProposals'));
    }

    public function contractsIndex(Request $request): View
    {
        $vendor = $this->resolveVendor($request);
        $status = $request->query('status');

        $contracts = ProjectContract::with(['project', 'buyer', 'milestones'])
            ->where('vendor_id', $vendor->id)
            ->when($status && in_array($status, ['active', 'completed', 'cancelled'], true), fn ($q) => $q->where('status', $status))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('vendor.contracts.index', compact('vendor', 'contracts', 'status'));
    }
}

