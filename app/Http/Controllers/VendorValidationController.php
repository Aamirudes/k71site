<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorValidationController extends Controller
{
    public function check(Request $request)
    {
        $slug = $request->query('slug');

        if (!$slug || strlen($slug) < 3) {
            return response()->json([
                'available' => false,
                'message' => 'Slug must be at least 3 characters.'
            ]);
        }
        
        // Ensure slug is properly formatted
        if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
            return response()->json([
                'available' => false,
                'message' => 'Invalid characters. Use lowercase letters, numbers, and hyphens only.'
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

        // Check if exists in vendors
        $vendorExists = Vendor::where('slug', $slug)->exists();
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
        $username = $request->query('username');

        if (!$username || strlen($username) < 3) {
            return response()->json([
                'available' => false,
                'message' => 'Username must be at least 3 characters.'
            ]);
        }
        
        if (!preg_match('/^[a-zA-Z0-9_.-]+$/', $username)) {
            return response()->json([
                'available' => false,
                'message' => 'Use letters, numbers, dots, dashes, or underscores only.'
            ]);
        }

        $userExists = \App\Models\User::where('username', $username)->exists();
        
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
