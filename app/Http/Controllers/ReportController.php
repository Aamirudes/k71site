<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Report;
use App\Models\Review;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Submit a trust & safety report against a product, vendor, or review.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'reportable_type' => 'required|in:product,vendor,review',
            'reportable_id' => 'required|integer',
            'reason' => 'required|string|max:50',
            'details' => 'nullable|string|max:1000',
        ]);

        $typeMap = [
            'product' => Product::class,
            'vendor' => Vendor::class,
            'review' => Review::class,
        ];

        $modelClass = $typeMap[$validated['reportable_type']];
        $model = $modelClass::findOrFail($validated['reportable_id']);

        Report::create([
            'user_id' => auth()->id(),
            'reportable_type' => $modelClass,
            'reportable_id' => $model->id,
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Thank you for your report. Our trust & safety team will review it promptly.');
    }
}
