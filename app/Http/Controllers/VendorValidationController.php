<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorValidationController extends Controller
{
    public function check(Request $request)
    {
        $slug = (string) $request->query('slug', '');
        $ignoreVendor = null;
        if (auth()->check() && $request->boolean('allow_own')) {
            $ignoreVendor = auth()->user()->vendor;
        }

        $result = app(\App\Services\Services\SubdomainManagerService::class)->checkAvailability($slug, $ignoreVendor);

        return response()->json([
            'available' => $result['available'],
            'slug' => $result['slug'],
            'message' => $result['message'],
            'preview_url' => $result['preview_url'] ?? null,
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
