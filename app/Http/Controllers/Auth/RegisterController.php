<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Enums\VendorStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Role;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function __construct(private OtpService $otpService) {}

    public function show()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        // Normalize email & username case/whitespace before validation to prevent case-mismatch 500s
        if ($request->filled('email')) {
            $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);
        }
        if ($request->filled('username')) {
            $request->merge(['username' => strtolower(trim((string) $request->input('username')))]);
        }

        // Support single full name input from modal
        if ($request->has('name') && !$request->has('first_name')) {
            $parts = explode(' ', trim((string) $request->input('name')), 2);
            $request->merge([
                'first_name' => Str::limit(trim($parts[0]) ?: 'User', 50, ''),
                'last_name' => Str::limit(trim($parts[1] ?? 'Buyer') ?: 'Buyer', 50, ''),
            ]);
        } else {
            if ($request->filled('first_name')) {
                $request->merge(['first_name' => trim((string) $request->input('first_name'))]);
            }
            if ($request->filled('last_name')) {
                $request->merge(['last_name' => trim((string) $request->input('last_name'))]);
            }
        }

        // Auto-generate unique username (max 20 chars, checks soft-deleted rows too) if not provided
        if (!$request->filled('username') && $request->filled('email')) {
            $emailLocal = explode('@', (string) $request->input('email'))[0] ?? 'user';
            $baseUser = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', Str::ascii($emailLocal)));
            if (strlen($baseUser) < 3) {
                $baseUser = 'user';
            }
            $basePrefix = substr($baseUser, 0, 14);
            $generated = $basePrefix . random_int(100, 999);
            while (User::withTrashed()->where('username', $generated)->exists()) {
                $generated = $basePrefix . random_int(1000, 99999);
                $generated = substr($generated, 0, 20);
            }
            $request->merge(['username' => $generated]);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'username' => ['required', 'string', 'min:3', 'max:20', 'unique:users,username', 'regex:/^[a-zA-Z0-9_]+$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
        ]);

        $createdUser = null;
        DB::transaction(function () use ($validated, $request, &$createdUser) {
            $createdUser = User::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'username' => strtolower($validated['username']),
                'email' => strtolower($validated['email']),
                'password' => Hash::make($validated['password']),
                'email_verified_at' => null, // Enforce 2-step OTP email confirmation
                'terms_accepted_at' => now(),
            ]);

            $customerRole = Role::firstOrCreate(['name' => UserRole::Customer->value]);
            $createdUser->roles()->syncWithoutDetaching([$customerRole->id]);
            $createdUser->unsetRelation('roles');

            // Merge guest cart
            \App\Models\Cart::mergeGuestCart($createdUser->id, $request->session()->getId());

            try {
                $this->otpService->generateAndSend($createdUser->email, 'registration', $createdUser->id);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Buyer OTP send notice: " . $e->getMessage());
            }

            $request->session()->put('verify_email', $createdUser->email);
            $request->session()->put('verify_purpose', 'registration');
            $request->session()->put('login_role', 'customer');
        });

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'requires_otp' => true,
                'redirect_url' => route('otp.show'),
                'message' => 'A 6-digit verification code has been sent to your email. Please verify to continue.',
                'csrf_token' => csrf_token(),
                'email' => $createdUser->email,
            ]);
        }

        return redirect()->route('otp.show')->with('success', 'A 6-digit verification code has been sent to your email. Please verify to complete your registration.');
    }

    public function vendorShow()
    {
        if (auth()->check() && auth()->user()->vendor) {
            $user = auth()->user();
            $vendorRole = Role::firstOrCreate(['name' => UserRole::Vendor->value]);
            $user->roles()->syncWithoutDetaching([$vendorRole->id]);
            $user->unsetRelation('roles');

            return redirect()->route('vendor.dashboard')->with('info', 'Your store is already active on K71.');
        }

        return view('auth.vendor-onboarding');
    }

    public function vendorStore(Request $request)
    {
        $isLoggedIn = auth()->check();

        if ($isLoggedIn) {
            $existingUser = auth()->user();
            if ($existingUser->vendor) {
                $vendorRole = Role::firstOrCreate(['name' => UserRole::Vendor->value]);
                $existingUser->roles()->syncWithoutDetaching([$vendorRole->id]);
                $existingUser->unsetRelation('roles');

                return redirect()->route('vendor.dashboard')->with('info', 'Your store is already registered and active.');
            }
        }

        // Normalize fields before validation
        $mergeData = [];
        if ($request->filled('slug')) {
            $mergeData['slug'] = strtolower(trim((string) $request->input('slug')));
        }
        if ($request->filled('shop_name')) {
            $mergeData['shop_name'] = trim((string) $request->input('shop_name'));
        }
        if ($request->filled('username')) {
            $mergeData['username'] = strtolower(trim((string) $request->input('username')));
        }
        if ($request->filled('email')) {
            $mergeData['email'] = strtolower(trim((string) $request->input('email')));
        }
        if ($request->filled('first_name')) {
            $mergeData['first_name'] = trim((string) $request->input('first_name'));
        }
        if ($request->filled('last_name')) {
            $mergeData['last_name'] = trim((string) $request->input('last_name'));
        }
        if ($request->filled('phone')) {
            $rawPhone = preg_replace('/[\s\-().]+/', '', trim((string) $request->input('phone')));
            if (strlen($rawPhone) > 15) {
                $digits = preg_replace('/[^0-9]/', '', $rawPhone);
                if (strlen($digits) >= 10) {
                    $rawPhone = substr($digits, -10);
                }
            }
            $mergeData['phone'] = $rawPhone;
        }
        if (!empty($mergeData)) {
            $request->merge($mergeData);
        }

        $rules = [
            'shop_name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'min:3', 'max:30', 'regex:/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/', 'unique:vendors,slug', 'unique:reserved_slugs,slug'],
            'vendor_type' => ['required', 'string', 'in:clothing,mens,food,feasto,services,service,used,machinery,electronics,automotive,digital_services,local_services,real_estate,home_living,beauty,groceries,kirana,footwear,marble,toys_sports,shorts,b2b'],
        ];

        if (!$isLoggedIn) {
            $rules = array_merge($rules, [
                'first_name' => ['required', 'string', 'max:50'],
                'last_name' => ['required', 'string', 'max:50'],
                'username' => ['required', 'string', 'min:3', 'max:20', 'unique:users,username', 'regex:/^[a-zA-Z0-9_]+$/'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                'phone' => ['required', 'string', 'min:10', 'max:15'],
                'password' => ['required', Password::defaults()],
            ]);
        } else {
            $rules['phone'] = ['nullable', 'string', 'min:10', 'max:15'];
        }

        $validated = $request->validate($rules);

        $normalizedSlug = strtolower($validated['slug']);

        // Check if slug is in config reserved list or owned by a soft-deleted vendor
        if (in_array($normalizedSlug, config('marketplace.reserved_subdomains', []), true)) {
            return back()->withErrors(['slug' => 'This shop name is reserved and cannot be used.'])->withInput();
        }
        if (Vendor::withTrashed()->where('slug', $normalizedSlug)->exists()) {
            return back()->withErrors(['slug' => 'This shop URL is already taken and cannot be reused.'])->withInput();
        }

        $targetUser = null;
        DB::transaction(function () use ($validated, $isLoggedIn, $request, $normalizedSlug, &$targetUser) {
            $customerRole = Role::firstOrCreate(['name' => UserRole::Customer->value]);
            $vendorRole = Role::firstOrCreate(['name' => UserRole::Vendor->value]);

            if (!$isLoggedIn) {
                $user = User::create([
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'username' => strtolower($validated['username']),
                    'email' => strtolower($validated['email']),
                    'phone' => Str::limit($validated['phone'], 15, ''),
                    'password' => Hash::make($validated['password']),
                    'terms_accepted_at' => now(),
                ]);

                $user->roles()->syncWithoutDetaching([$customerRole->id, $vendorRole->id]);
                $user->unsetRelation('roles');

                \App\Models\Cart::mergeGuestCart($user->id, $request->session()->getId());
                $emailToVerify = $user->email;
            } else {
                $user = auth()->user();
                $user->roles()->syncWithoutDetaching([$customerRole->id, $vendorRole->id]);
                $user->unsetRelation('roles');

                if ($request->filled('phone') && !$user->phone) {
                    $user->update(['phone' => Str::limit($validated['phone'], 15, '')]);
                }

                $emailToVerify = $user->email;
            }

            $targetUser = $user;
            $autoApprove = config('marketplace.vendor.auto_approve');

            $pickupAddress = null;
            if ($request->filled('pincode') || $request->filled('address')) {
                $pickupAddress = [
                    'line_1' => $request->input('address', ''),
                    'city' => $request->input('city', ''),
                    'state' => $request->input('state', ''),
                    'pincode' => $request->input('pincode', ''),
                    'contact_phone' => $validated['phone'] ?? $user->phone ?? '',
                ];
            }

            $vendorType = $validated['vendor_type'] ?? $request->input('vendor_type', 'clothing');
            $vendorPayload = [
                'user_id' => $user->id,
                'slug' => $normalizedSlug,
                'shop_name' => $validated['shop_name'],
                'vendor_type' => $vendorType,
                'pickup_address' => $pickupAddress,
                'status' => $autoApprove ? VendorStatus::Approved->value : VendorStatus::Pending->value,
                'approved_at' => $autoApprove ? now() : null,
                'slug_reserved_at' => !$isLoggedIn ? now() : null, // Reserved during verification if new user
            ];

            // Reuse/restore trashed vendor if user previously deleted their store (prevents vendors_user_id_unique 500)
            $trashedVendor = Vendor::onlyTrashed()->where('user_id', $user->id)->first();
            if ($trashedVendor) {
                $trashedVendor->restore();
                $trashedVendor->update($vendorPayload);
                $vendor = $trashedVendor->fresh();
            } else {
                $vendor = Vendor::create($vendorPayload);
            }
            $user->unsetRelation('vendor');

            // Initialize vertical-tailored settings
            $settingData = [
                'vendor_id' => $vendor->id,
                'cod_enabled' => true,
                'upi_enabled' => true,
            ];
            if ($vendorType === 'food') {
                $settingData['is_restaurant'] = true;
                $settingData['is_pure_veg'] = false;
                $settingData['avg_prep_time_mins'] = 25;
                $settingData['food_delivery_fee'] = 4000;
            } elseif (in_array($vendorType, ['services', 'service', 'local_services', 'digital_services'], true)) {
                $settingData['service_experience_years'] = 5;
                $settingData['service_radius_km'] = 15;
            }
            \App\Models\VendorSetting::firstOrCreate(['vendor_id' => $vendor->id], $settingData);

            // If auto-approved and has complete address, register with Shiprocket
            if ($autoApprove && !empty($pickupAddress['line_1']) && !empty($pickupAddress['pincode']) && strlen($pickupAddress['pincode']) === 6) {
                try {
                    app(\App\Services\Shipping\ShiprocketService::class)->ensurePickupLocation($vendor);
                } catch (\Throwable $e) {}
            }

            if (!$isLoggedIn || !$user->email_verified_at) {
                try {
                    $this->otpService->generateAndSend($emailToVerify, 'registration', $user->id);
                } catch (\Exception $e) {
                    // Ignore cooldown exception
                }
                $request->session()->put('verify_email', $emailToVerify);
                $request->session()->put('verify_purpose', 'registration');
                $request->session()->put('login_role', 'vendor');
            }
        });

        if (!$isLoggedIn || !$targetUser?->email_verified_at) {
            return redirect()->route('otp.show');
        }

        return redirect()->route('vendor.dashboard')->with('success', 'Welcome to your vendor dashboard!');
    }
}
