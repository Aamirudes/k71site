@php
    $presetKey = $presetKey ?? ($providerSite['preset_key'] ?? 'velora');
    $ps = $ps ?? ($providerSite['tokens'] ?? \App\Services\Services\ServiceWebsitePresenter::presetTokens($presetKey, $theme ?? ($providerSite['theme'] ?? [])));
    $isSubdomainHost = $isSubdomainHost ?? (request()->attributes->has('current_vendor') && !request()->is('s/*'));
    $enquireAction = $enquireAction ?? ($isSubdomainHost ? '/enquire' : route('storefront.enquire', $vendor->slug));
    $contact = $providerSite['contact'] ?? [];
    $id = $providerSite['identity'] ?? [];
    $services = $providerSite['services'] ?? collect();
@endphp

@if(($providerSite['section_visibility']['contact'] ?? true))
<section id="contact" data-section-id="contact" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="{{ $ps['card'] }} p-6 sm:p-10 lg:p-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
            <div class="lg:col-span-5 space-y-5">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-[0.2em] {{ $ps['muted'] }} block mb-1">
                        Direct Consultation &amp; Quotes
                    </span>
                    <h2 class="text-2xl sm:text-4xl {{ $ps['heading'] }}">
                        {{ $providerSite['nav_labels']['contact'] ?? 'Get In Touch' }}
                    </h2>
                    <p class="text-xs sm:text-sm {{ $ps['muted'] }} mt-2 leading-relaxed">
                        Share your scope, timeline, or project goals directly with {{ $id['name'] ?? $vendor->shop_name }}.
                    </p>
                </div>

                <div class="space-y-3 text-xs sm:text-sm">
                    @if(!empty($contact['show_email']) && !empty($contact['email']))
                        <div class="p-3.5 {{ $ps['surface_alt'] }} flex items-center justify-between gap-3">
                            <span class="{{ $ps['muted'] }}">Email</span>
                            <a href="mailto:{{ $contact['email'] }}" class="font-bold hover:underline break-all">{{ $contact['email'] }}</a>
                        </div>
                    @endif

                    @if(!empty($contact['show_phone']) && !empty($contact['phone']))
                        <div class="p-3.5 {{ $ps['surface_alt'] }} flex items-center justify-between gap-3">
                            <span class="{{ $ps['muted'] }}">Phone / WhatsApp</span>
                            <a href="tel:{{ $contact['phone'] }}" class="font-bold hover:underline">{{ $contact['phone'] }}</a>
                        </div>
                    @endif

                    @if(!empty($contact['show_city']) && !empty($contact['city']))
                        <div class="p-3.5 {{ $ps['surface_alt'] }} flex items-center justify-between gap-3">
                            <span class="{{ $ps['muted'] }}">Location</span>
                            <span class="font-bold">{{ $contact['city'] }}</span>
                        </div>
                    @endif
                </div>

                @if(!empty($contact['allow_k71_chat']))
                    <div class="pt-1">
                        @auth
                            <a href="{{ route('messages.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-xs {{ $ps['btn_secondary'] }}">
                                <span>💬</span>
                                <span>Message on K71 Inbox</span>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-xs {{ $ps['btn_secondary'] }}">
                                <span>💬</span>
                                <span>Sign in for K71 Real-Time Chat</span>
                            </a>
                        @endauth
                    </div>
                @endif
            </div>

            <div class="lg:col-span-7">
                @if(session('success'))
                    <div class="mb-4 p-4 rounded-2xl bg-emerald-500/15 border border-emerald-500/40 text-emerald-700 dark:text-emerald-300 text-xs sm:text-sm font-semibold">
                        ✓ {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 p-4 rounded-2xl bg-rose-500/15 border border-rose-500/40 text-rose-700 dark:text-rose-300 text-xs sm:text-sm font-semibold">
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 p-4 rounded-2xl bg-rose-500/15 border border-rose-500/40 text-rose-700 dark:text-rose-300 text-xs space-y-1">
                        @foreach($errors->all() as $err)
                            <div>• {{ $err }}</div>
                        @endforeach
                    </div>
                @endif

                @if(!empty($contact['allow_direct_enquiry']))
                    <form action="{{ $enquireAction }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider mb-1.5 {{ $ps['muted'] }}">Your Name *</label>
                                <input type="text" name="name" required value="{{ old('name', auth()->user()?->name) }}"
                                       placeholder="Full name"
                                       class="w-full px-4 py-2.5 text-xs sm:text-sm border {{ $ps['input'] }}">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider mb-1.5 {{ $ps['muted'] }}">Email Address *</label>
                                <input type="email" name="email" required value="{{ old('email', auth()->user()?->email) }}"
                                       placeholder="you@company.com"
                                       class="w-full px-4 py-2.5 text-xs sm:text-sm border {{ $ps['input'] }}">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider mb-1.5 {{ $ps['muted'] }}">Phone (Optional)</label>
                                <input type="text" name="phone" value="{{ old('phone', auth()->user()?->phone) }}"
                                       placeholder="+91 ..."
                                       class="w-full px-4 py-2.5 text-xs sm:text-sm border {{ $ps['input'] }}">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider mb-1.5 {{ $ps['muted'] }}">Estimated Budget (₹)</label>
                                <input type="number" name="budget" min="0" step="100" value="{{ old('budget') }}"
                                       placeholder="e.g. 15000"
                                       class="w-full px-4 py-2.5 text-xs sm:text-sm border {{ $ps['input'] }}">
                            </div>
                        </div>

                        @if($services->isNotEmpty())
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider mb-1.5 {{ $ps['muted'] }}">Service of Interest (Optional)</label>
                                <select name="service_product_id" class="w-full px-4 py-2.5 text-xs sm:text-sm border {{ $ps['input'] }}">
                                    <option value="">General Project Enquiry</option>
                                    @foreach($services as $svc)
                                        <option value="{{ $svc->id }}" @selected(old('service_product_id') == $svc->id)>
                                            {{ $svc->title }} (From ₹{{ number_format((float)($svc->gigPackages->min('price') ?? $svc->base_price)) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider mb-1.5 {{ $ps['muted'] }}">Project Subject *</label>
                            <input type="text" name="subject" required value="{{ old('subject') }}"
                                   placeholder="e.g. Brand identity & website launch"
                                   class="w-full px-4 py-2.5 text-xs sm:text-sm border {{ $ps['input'] }}">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider mb-1.5 {{ $ps['muted'] }}">Project Brief &amp; Requirements *</label>
                            <textarea name="message" rows="4" required
                                      placeholder="Describe what you need built, target timeline, and key deliverables..."
                                      class="w-full px-4 py-3 text-xs sm:text-sm border {{ $ps['input'] }}">{{ old('message') }}</textarea>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                            <span class="text-[11px] {{ $ps['muted'] }}">Direct message sent safely via K71 Services.</span>
                            <button type="submit" class="px-6 py-3 text-xs sm:text-sm transition cursor-pointer {{ $ps['btn_primary'] }}">
                                Send Enquiry →
                            </button>
                        </div>
                    </form>
                @else
                    <div class="{{ $ps['surface_alt'] }} p-6 text-center space-y-2">
                        <p class="text-sm font-bold">Direct enquiry form is paused by the provider.</p>
                        <p class="text-xs {{ $ps['muted'] }}">Please select a package above or reach out using the listed contact details.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endif
