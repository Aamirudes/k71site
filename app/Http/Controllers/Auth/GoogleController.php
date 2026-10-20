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

        $user = User::where('email', strtolower($googleUser->email))->first();
        $roleRequested = $request->session()->pull('socialite_role', 'customer');

        if ($user) {
            // Update google id if not set
            if (!$user->google_id) {
                $user->update(['google_id' => $googleUser->id]);
            }
        } else {
            // Register new user
            $names = explode(' ', $googleUser->name, 2);
            $firstName = $names[0];
            $lastName = $names[1] ?? '';
            
            // Generate unique username
            $baseUsername = Str::slug($firstName . $lastName);
            $username = $baseUsername;
            $counter = 1;
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }

            $user = User::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'username' => $username,
                'email' => strtolower($googleUser->email),
                'password' => Hash::make(Str::random(24)),
                'google_id' => $googleUser->id,
                'email_verified_at' => now(), // Auto verify since it's from Google
                'terms_accepted_at' => now(),
            ]);

            $rolesToAttach = [Role::where('name', UserRole::Customer->value)->first()];
            if ($roleRequested === 'vendor') {
                $rolesToAttach[] = Role::where('name', UserRole::Vendor->value)->first();
            }
            $user->roles()->attach($rolesToAttach);
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
