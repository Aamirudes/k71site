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
        // Support single full name input from modal
        if ($request->has('name') && !$request->has('first_name')) {
            $parts = explode(' ', trim($request->input('name')), 2);
            $request->merge([
                'first_name' => $parts[0] ?: 'User',
                'last_name' => $parts[1] ?? 'Buyer'
            ]);
        }

        // Auto-generate unique username if not provided
        if (!$request->filled('username') && $request->filled('email')) {
            $baseUser = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode('@', $request->input('email'))[0]));
            if (strlen($baseUser) < 3) $baseUser = 'user';
            $generated = substr($baseUser, 0, 14) . rand(100, 999);
            while (User::where('username', $generated)->exists()) {
                $generated = substr($baseUser, 0, 14) . rand(100, 9999);
            }
            $request->merge(['username' => $generated]);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'username' => ['required', 'string', 'max:20', 'unique:users', 'regex:/^[a-zA-Z0-9_]+$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
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
        return view('auth.vendor-onboarding');
    }

    public function vendorStore(Request $request)
    {
        $isLoggedIn = auth()->check();

        $rules = [
            'shop_name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'min:3', 'max:30', 'regex:/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/', 'unique:vendors,slug', 'unique:reserved_slugs,slug'],
            'vendor_type' => ['required', 'string', 'in:clothing,mens,food,feasto,services,used,machinery,electronics,automotive,digital_services,local_services,real_estate,home_living,beauty,groceries,toys_sports,shorts,b2b'],
        ];

        if (!$isLoggedIn) {
            $rules = array_merge($rules, [
                'first_name' => ['required', 'string', 'max:50'],
                'last_name' => ['required', 'string', 'max:50'],
                'username' => ['required', 'string', 'max:20', 'unique:users', 'regex:/^[a-zA-Z0-9_]+$/'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'phone' => ['required', 'string', 'min:10', 'max:15'],
                'password' => ['required', Password::defaults()],
            ]);
        } else {
            $rules['phone'] = ['nullable', 'string', 'min:10', 'max:15'];
        }

        $validated = $request->validate($rules);

        // Check if slug is in config reserved list
        if (in_array(strtolower($validated['slug']), config('marketplace.reserved_subdomains'))) {
            return back()->withErrors(['slug' => 'This shop name is reserved and cannot be used.'])->withInput();
        }

        DB::transaction(function () use ($validated, $isLoggedIn, $request) {
            if (!$isLoggedIn) {
                $user = User::create([
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'username' => strtolower($validated['username']),
                    'email' => strtolower($validated['email']),
                    'phone' => $validated['phone'],
                    'password' => Hash::make($validated['password']),
                    'terms_accepted_at' => now(),
                ]);

                // Attach roles
                $roles = Role::whereIn('name', [UserRole::Customer->value, UserRole::Vendor->value])->get();
                $user->roles()->attach($roles);

                $emailToVerify = $user->email;
            } else {
                $user = auth()->user();
                // Ensure they have vendor role
                if (!$user->hasRole(UserRole::Vendor->value)) {
                    $vendorRole = Role::where('name', UserRole::Vendor->value)->first();
                    $user->roles()->attach($vendorRole);
                }
                
                if ($request->filled('phone') && !$user->phone) {
                    $user->update(['phone' => $validated['phone']]);
                }
                
                $emailToVerify = $user->email;
            }

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

            $vendor = Vendor::create([
                'user_id' => $user->id,
                'slug' => strtolower($validated['slug']),
                'shop_name' => $validated['shop_name'],
                'vendor_type' => $vendorType,
                'pickup_address' => $pickupAddress,
                'status' => $autoApprove ? VendorStatus::Approved->value : VendorStatus::Pending->value,
                'approved_at' => $autoApprove ? now() : null,
                'slug_reserved_at' => !$isLoggedIn ? now() : null, // Reserved during verification if new user
            ]);

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
            } elseif ($vendorType === 'services') {
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

        if (!$isLoggedIn || !auth()->user()->email_verified_at) {
            return redirect()->route('otp.show');
        }

        return redirect()->route('vendor.dashboard')->with('success', 'Welcome to your vendor dashboard!');
    }
}
