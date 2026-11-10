<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect(Request $request)
    {
        $role = $request->query('role', 'customer');
        $request->session()->put('socialite_role', $role);
        
        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['login' => 'Google login failed.']);
        }

        $email = strtolower(trim((string) ($googleUser->email ?? '')));
        if (empty($email)) {
            return redirect()->route('login')->withErrors(['login' => 'Google account did not provide an email address.']);
        }

        $user = User::withTrashed()->where('email', $email)->first();
        if ($user && $user->trashed()) {
            return redirect()->route('login')->withErrors(['login' => 'This account has been deactivated.']);
        }

        $roleRequested = $request->session()->pull('socialite_role', 'customer');

        if ($user) {
            // Update google id if not set
            if (!$user->google_id && !empty($googleUser->id)) {
                $user->update(['google_id' => $googleUser->id]);
            }
        } else {
            // Register new user safely within VARCHAR column limits
            $rawName = trim((string) ($googleUser->name ?? ''));
            $names = $rawName !== '' ? explode(' ', $rawName, 2) : ['Google', 'User'];
            $firstName = Str::limit(trim($names[0]) ?: 'User', 50, '');
            $lastName = Str::limit(trim($names[1] ?? ''), 50, '');

            // Generate guaranteed <= 20 char ASCII username (handles Unicode/emoji names cleanly)
            $asciiBase = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', Str::ascii($firstName . $lastName)));
            if (strlen($asciiBase) < 3) {
                $asciiBase = 'user';
            }
            $baseUsername = substr($asciiBase, 0, 20);
            $username = $baseUsername;
            $counter = 1;
            while (User::withTrashed()->where('username', $username)->exists()) {
                $suffix = (string) $counter;
                $username = substr($asciiBase, 0, max(1, 20 - strlen($suffix))) . $suffix;
                $counter++;
            }

            $user = User::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'username' => $username,
                'email' => $email,
                'password' => Hash::make(Str::random(24)),
                'google_id' => $googleUser->id ?? null,
                'email_verified_at' => now(), // Auto verify since it's from Google
                'terms_accepted_at' => now(),
            ]);

            $customerRole = Role::firstOrCreate(['name' => UserRole::Customer->value]);
            $roleIds = [$customerRole->id];
            if ($roleRequested === 'vendor') {
                $vendorRole = Role::firstOrCreate(['name' => UserRole::Vendor->value]);
                $roleIds[] = $vendorRole->id;
            }
            $user->roles()->syncWithoutDetaching($roleIds);
            $user->unsetRelation('roles');
        }

        if ($user->is_blocked) {
            return redirect()->route('login')->withErrors(['login' => 'This account has been blocked.']);
        }

        Auth::login($user, true);

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        if ($roleRequested === 'vendor' || $user->hasRole(UserRole::Vendor->value)) {
            if (!$user->vendor) {
                return redirect()->route('vendor.register');
            }
            return redirect()->route('vendor.dashboard');
        }

        return redirect()->intended(route('home'));
    }
}
