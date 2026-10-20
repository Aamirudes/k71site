<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorValidationController extends Controller
{
    public function checkSlug(Request $request)
    {
        $slug = strtolower($request->query('slug', ''));

        if (strlen($slug) < 3) {
            return response()->json(['available' => false, 'message' => 'Too short.']);
        }

        if (!preg_match('/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/', $slug)) {
            return response()->json(['available' => false, 'message' => 'Invalid format.']);
        }

        if (in_array($slug, config('marketplace.reserved_subdomains', []))) {
            return response()->json(['available' => false, 'message' => 'Reserved.']);
        }

        $isReserved = DB::table('reserved_slugs')
            ->where('slug', $slug)
            ->where(function ($q) {
                $q->whereNull('released_at')
                  ->orWhere('released_at', '>', now());
            })
            ->exists();

        if ($isReserved) {
            return response()->json(['available' => false, 'message' => 'Taken.']);
        }

        $isTakenByVendor = Vendor::where('slug', $slug)->exists();

        if ($isTakenByVendor) {
            return response()->json(['available' => false, 'message' => 'Taken.']);
        }

        return response()->json(['available' => true, 'message' => 'Available!']);
    }
}
