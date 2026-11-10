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
        $slug = strtolower(trim((string) $request->query('slug', '')));

        if (strlen($slug) < 3) {
            return response()->json(['available' => false, 'message' => 'Too short.']);
        }

        if (strlen($slug) > 30) {
            return response()->json(['available' => false, 'message' => 'Max 30 characters allowed.']);
        }

        if (!preg_match('/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/', $slug)) {
            return response()->json(['available' => false, 'message' => 'Invalid format.']);
        }

        if (in_array($slug, config('marketplace.reserved_subdomains', []), true)) {
            return response()->json(['available' => false, 'message' => 'Reserved.']);
        }

        $isReserved = DB::table('reserved_slugs')
            ->where('slug', $slug)
            ->exists();

        if ($isReserved) {
            return response()->json(['available' => false, 'message' => 'Taken.']);
        }

        $isTakenByVendor = Vendor::withTrashed()->where('slug', $slug)->exists();

        if ($isTakenByVendor) {
            return response()->json(['available' => false, 'message' => 'Taken.']);
        }

        return response()->json(['available' => true, 'message' => 'Available!']);
    }
}
