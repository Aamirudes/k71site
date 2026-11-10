<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorValidationController extends Controller
{
    public function check(Request $request)
    {
        $slug = strtolower(trim((string) $request->query('slug', '')));

        if (!$slug || strlen($slug) < 3) {
            return response()->json([
                'available' => false,
                'message' => 'Slug must be at least 3 characters.'
            ]);
        }

        if (strlen($slug) > 30) {
            return response()->json([
                'available' => false,
                'message' => 'Slug cannot exceed 30 characters.'
            ]);
        }

        // Ensure slug is properly formatted (matches registration rule)
        if (!preg_match('/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/', $slug)) {
            return response()->json([
                'available' => false,
                'message' => 'Invalid characters. Use lowercase letters, numbers, and hyphens between characters only.'
            ]);
        }

        if (in_array($slug, config('marketplace.reserved_subdomains', []), true)) {
            return response()->json([
                'available' => false,
                'message' => 'This URL is unavailable (reserved).'
            ]);
        }

        // Check if reserved
        $isReserved = DB::table('reserved_slugs')->where('slug', $slug)->exists();
        if ($isReserved) {
            return response()->json([
                'available' => false,
                'message' => 'This URL is unavailable (reserved).'
            ]);
        }

        // Check if exists in vendors (including soft-deleted due to UNIQUE index on slug)
        $vendorExists = Vendor::withTrashed()->where('slug', $slug)->exists();
        if ($vendorExists) {
            return response()->json([
                'available' => false,
                'message' => 'This URL is already taken by another store.'
            ]);
        }

        return response()->json([
            'available' => true,
            'message' => 'URL is available!'
        ]);
    }

    public function checkUsername(Request $request)
    {
        $username = strtolower(trim((string) $request->query('username', '')));

        if (!$username || strlen($username) < 3) {
            return response()->json([
                'available' => false,
                'message' => 'Username must be at least 3 characters.'
            ]);
        }

        if (strlen($username) > 20) {
            return response()->json([
                'available' => false,
                'message' => 'Username cannot exceed 20 characters.'
            ]);
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return response()->json([
                'available' => false,
                'message' => 'Use letters, numbers, and underscores only.'
            ]);
        }

        $userExists = \App\Models\User::withTrashed()->where('username', $username)->exists();

        if ($userExists) {
            return response()->json([
                'available' => false,
                'message' => 'This username is already taken.'
            ]);
        }

        return response()->json([
            'available' => true,
            'message' => 'Username is available!'
        ]);
    }
}
