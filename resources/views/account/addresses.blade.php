<x-layouts.public title="Saved Delivery Addresses" :fullWidth="true">
    <div x-data="{ editingId: null }">
        <section class="grail-hero-band text-[#EFE9F2] border-b border-white/10">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-10 sm:pt-9 sm:pb-12">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                    <div>
                        <a href="{{ route('account') }}" class="text-xs font-bold text-[#ECD2F3] hover:text-white inline-flex items-center gap-1.5 mb-2.5 transition-colors uppercase tracking-[0.14em]">
                            &larr; Back to Account
                        </a>
                        <h1 class="grail-display text-[clamp(1.85rem,4vw,2.75rem)] font-bold text-white tracking-tight">Saved Delivery Addresses</h1>
                        <p class="text-sm text-white/65 mt-1">Manage your Home, Work, and doorstep locations for K71 Food &amp; Marketplace orders.</p>
                    </div>
                    <a href="{{ route('food.index') }}" class="inline-flex items-center gap-2 rounded-full bg-[#ECD2F3] text-[#1A1018] hover:bg-white px-4 py-2.5 text-xs font-bold shadow-sm transition-colors self-start sm:self-auto">
                        <span>Explore K71 Food &rarr;</span>
                    </a>
                </div>
            </div>
        </section>

        <div class="grail-sheet -mt-4 sm:-mt-5 rounded-t-[28px] sm:rounded-t-[36px] min-h-screen">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-24 space-y-8">
                @if(session('success'))
                    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-3 shadow-xs">
                        <span class="text-emerald-600 font-bold">&#10003;</span>
                        <p class="font-medium text-sm">{{ session('success') }}</p>
                    </div>
                @endif

                @if(session('error'))
                    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center gap-3 shadow-xs">
                        <span class="text-rose-600 font-bold">!</span>
                        <p class="font-medium text-sm">{{ session('error') }}</p>
                    </div>
                @endif

                @if($errors->any())
                    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs space-y-1 shadow-xs">
                        <p class="font-bold text-sm">Please review the address form:</p>
                        <ul class="list-disc pl-5 space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Saved Addresses List -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="grail-display text-xl font-bold text-[#1A1018]">Your Saved Locations ({{ $addresses->count() }})</h2>
                    </div>

                    @if($addresses->isEmpty())
                        <div class="bg-white rounded-[24px] border border-dashed border-black/15 p-10 text-center">
                            <p class="text-sm font-bold text-[#1A1018]">No saved addresses yet</p>
                            <p class="text-xs text-[#7A6B78] mt-1">Add your first Home or Work address below to speed up K71 Food and checkout.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($addresses as $address)
                                <div class="bg-white rounded-[24px] border {{ $address->is_default ? 'border-[#623562] ring-2 ring-[#ECD2F3]/60' : 'border-black/8' }} p-5 shadow-[0_8px_24px_rgba(26,16,24,0.04)] flex flex-col justify-between gap-4">
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-2">
                                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FAF8F5] text-[#1A1018] border border-black/10">
                                                    {{ $address->label }}
                                                </span>
                                                @if($address->is_default)
                                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        Default
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                @if(!$address->is_default)
                                                    <form action="{{ route('addresses.default', $address) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-[#FAF8F5] hover:bg-[#ECD2F3]/40 text-[#623562] text-[11px] font-bold transition cursor-pointer">
                                                            Set Default
                                                        </button>
                                                    </form>
                                                @endif
                                                <button type="button"
                                                        @click="editingId = (editingId === {{ $address->id }} ? null : {{ $address->id }})"
                                                        class="px-2.5 py-1 rounded-lg bg-[#FAF8F5] hover:bg-black/5 text-[#1A1018] text-[11px] font-bold transition cursor-pointer">
                                                    <span x-text="editingId === {{ $address->id }} ? 'Close' : 'Edit'">Edit</span>
                                                </button>
                                                <form action="{{ route('addresses.destroy', $address) }}" method="POST" onsubmit="return confirm('Remove this saved address?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 text-[11px] font-bold transition cursor-pointer">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        <div>
                                            <p class="font-bold text-sm text-[#1A1018]">{{ $address->recipient_name }}</p>
                                            <p class="text-xs text-[#7A6B78] leading-relaxed mt-0.5">
                                                {{ $address->address_line_1 }}
                                                @if($address->address_line_2), {{ $address->address_line_2 }}@endif
                                                @if($address->landmark) (Near {{ $address->landmark }})@endif
                                            </p>
                                            <p class="text-xs text-[#7A6B78]">{{ $address->city }}, {{ $address->state }} - <span class="font-mono font-bold text-[#1A1018]">{{ $address->pincode }}</span></p>
                                            <p class="text-xs font-mono text-[#7A6B78] mt-1">Phone: {{ $address->phone }}</p>
                                        </div>
                                    </div>

                                    <!-- Inline Edit Form -->
                                    <form x-show="editingId === {{ $address->id }}" x-cloak
                                          action="{{ route('addresses.update', $address) }}" method="POST"
                                          class="pt-4 border-t border-black/8 space-y-3">
                                        @csrf
                                        @method('PUT')
                                        <div class="grid grid-cols-2 gap-2.5">
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase text-[#7A6B78] mb-1">Label</label>
                                                <input type="text" name="label" value="{{ $address->label }}" required maxlength="30" class="grail-input-light w-full rounded-xl px-3 py-2 text-xs">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase text-[#7A6B78] mb-1">Recipient Name</label>
                                                <input type="text" name="recipient_name" value="{{ $address->recipient_name }}" required maxlength="100" class="grail-input-light w-full rounded-xl px-3 py-2 text-xs">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase text-[#7A6B78] mb-1">Phone</label>
                                                <input type="text" name="phone" value="{{ $address->phone }}" required maxlength="15" class="grail-input-light w-full rounded-xl px-3 py-2 text-xs">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase text-[#7A6B78] mb-1">Pincode</label>
                                                <input type="text" name="pincode" value="{{ $address->pincode }}" required maxlength="6" class="grail-input-light w-full rounded-xl px-3 py-2 text-xs font-mono">
                                            </div>
                                            <div class="col-span-2">
                                                <label class="block text-[10px] font-bold uppercase text-[#7A6B78] mb-1">Address Line 1</label>
                                                <input type="text" name="address_line_1" value="{{ $address->address_line_1 }}" required maxlength="200" class="grail-input-light w-full rounded-xl px-3 py-2 text-xs">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase text-[#7A6B78] mb-1">Line 2 / Floor</label>
                                                <input type="text" name="address_line_2" value="{{ $address->address_line_2 }}" maxlength="200" class="grail-input-light w-full rounded-xl px-3 py-2 text-xs">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase text-[#7A6B78] mb-1">Landmark</label>
                                                <input type="text" name="landmark" value="{{ $address->landmark }}" maxlength="200" class="grail-input-light w-full rounded-xl px-3 py-2 text-xs">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase text-[#7A6B78] mb-1">City</label>
                                                <input type="text" name="city" value="{{ $address->city }}" required maxlength="100" class="grail-input-light w-full rounded-xl px-3 py-2 text-xs">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase text-[#7A6B78] mb-1">State</label>
                                                <input type="text" name="state" value="{{ $address->state }}" required maxlength="50" class="grail-input-light w-full rounded-xl px-3 py-2 text-xs">
                                            </div>
                                        </div>
                                        <label class="flex items-center gap-2 text-xs text-[#1A1018] font-medium cursor-pointer">
                                            <input type="checkbox" name="is_default" value="1" {{ $address->is_default ? 'checked' : '' }} class="rounded border-black/20 text-[#623562]">
                                            <span>Set as default delivery address</span>
                                        </label>
                                        <div class="flex justify-end gap-2">
                                            <button type="button" @click="editingId = null" class="px-3.5 py-1.5 rounded-xl text-xs font-bold text-[#7A6B78] hover:bg-black/5 cursor-pointer">Cancel</button>
                                            <button type="submit" class="px-4 py-1.5 rounded-xl bg-[#1A1018] text-[#ECD2F3] text-xs font-bold cursor-pointer">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Add New Address Form -->
                <div class="bg-white rounded-[28px] border border-black/8 p-6 sm:p-8 shadow-[0_12px_36px_rgba(26,16,24,0.06)]">
                    <h2 class="grail-display text-xl font-bold text-[#1A1018] mb-1">Add a New Address</h2>
                    <p class="text-xs text-[#7A6B78] mb-6">Your default address automatically syncs with K71 Food location selection.</p>

                    <form action="{{ route('addresses.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @csrf
                        <div>
                            <label for="label" class="block text-xs font-bold uppercase tracking-wider text-[#7A6B78] mb-1.5">Address Label <span class="text-rose-500">*</span></label>
                            <select name="label" id="label" class="grail-input-light w-full rounded-xl px-3.5 py-2.5 text-sm">
                                <option value="Home" {{ old('label') === 'Home' ? 'selected' : '' }}>Home</option>
                                <option value="Work" {{ old('label') === 'Work' ? 'selected' : '' }}>Work</option>
                                <option value="Hostel" {{ old('label') === 'Hostel' ? 'selected' : '' }}>Hostel</option>
                                <option value="Other" {{ old('label') === 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <div>
                            <label for="recipient_name" class="block text-xs font-bold uppercase tracking-wider text-[#7A6B78] mb-1.5">Recipient Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="recipient_name" id="recipient_name" required maxlength="100"
                                   value="{{ old('recipient_name', auth()->user()?->name) }}"
                                   placeholder="Full name for delivery"
                                   class="grail-input-light w-full rounded-xl px-3.5 py-2.5 text-sm">
                        </div>

                        <div>
                            <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-[#7A6B78] mb-1.5">Mobile Number <span class="text-rose-500">*</span></label>
                            <input type="text" name="phone" id="phone" required maxlength="15"
                                   value="{{ old('phone', auth()->user()?->phone) }}"
                                   placeholder="10-digit mobile number"
                                   class="grail-input-light w-full rounded-xl px-3.5 py-2.5 text-sm">
                        </div>

                        <div>
                            <label for="pincode" class="block text-xs font-bold uppercase tracking-wider text-[#7A6B78] mb-1.5">Pincode <span class="text-rose-500">*</span></label>
                            <input type="text" name="pincode" id="pincode" required maxlength="6" pattern="[0-9]{6}"
                                   value="{{ old('pincode') }}"
                                   placeholder="6-digit pincode"
                                   class="grail-input-light w-full rounded-xl px-3.5 py-2.5 text-sm font-mono">
                        </div>

                        <div class="sm:col-span-2">
                            <label for="address_line_1" class="block text-xs font-bold uppercase tracking-wider text-[#7A6B78] mb-1.5">Flat / House No. / Street <span class="text-rose-500">*</span></label>
                            <input type="text" name="address_line_1" id="address_line_1" required maxlength="200"
                                   value="{{ old('address_line_1') }}"
                                   placeholder="House no., building name, street"
                                   class="grail-input-light w-full rounded-xl px-3.5 py-2.5 text-sm">
                        </div>

                        <div>
                            <label for="address_line_2" class="block text-xs font-bold uppercase tracking-wider text-[#7A6B78] mb-1.5">Apartment / Area (Optional)</label>
                            <input type="text" name="address_line_2" id="address_line_2" maxlength="200"
                                   value="{{ old('address_line_2') }}"
                                   placeholder="Sector, colony, floor"
                                   class="grail-input-light w-full rounded-xl px-3.5 py-2.5 text-sm">
                        </div>

                        <div>
                            <label for="landmark" class="block text-xs font-bold uppercase tracking-wider text-[#7A6B78] mb-1.5">Landmark (Optional)</label>
                            <input type="text" name="landmark" id="landmark" maxlength="200"
                                   value="{{ old('landmark') }}"
                                   placeholder="Near metro, temple, school"
                                   class="grail-input-light w-full rounded-xl px-3.5 py-2.5 text-sm">
                        </div>

                        <div>
                            <label for="city" class="block text-xs font-bold uppercase tracking-wider text-[#7A6B78] mb-1.5">City <span class="text-rose-500">*</span></label>
                            <input type="text" name="city" id="city" required maxlength="100"
                                   value="{{ old('city') }}"
                                   placeholder="City name"
                                   class="grail-input-light w-full rounded-xl px-3.5 py-2.5 text-sm">
                        </div>

                        <div>
                            <label for="state" class="block text-xs font-bold uppercase tracking-wider text-[#7A6B78] mb-1.5">State <span class="text-rose-500">*</span></label>
                            <input type="text" name="state" id="state" required maxlength="50"
                                   value="{{ old('state') }}"
                                   placeholder="State name"
                                   class="grail-input-light w-full rounded-xl px-3.5 py-2.5 text-sm">
                        </div>

                        <div class="sm:col-span-2 flex items-center justify-between flex-wrap gap-3 pt-2">
                            <label class="flex items-center gap-2 text-xs font-medium text-[#1A1018] cursor-pointer">
                                <input type="checkbox" name="is_default" value="1" {{ old('is_default', $addresses->isEmpty()) ? 'checked' : '' }} class="rounded border-black/20 text-[#623562]">
                                <span>Make this my default delivery address</span>
                            </label>

                            <button type="submit" class="px-6 py-2.5 rounded-full bg-[#1A1018] hover:bg-black text-[#ECD2F3] text-xs font-bold transition shadow-sm cursor-pointer">
                                Save Address
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.public>

