<x-layouts.admin title="Expiry, Grace Period & Reminder Automation">
    @include('admin.vendor-management._nav')

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Settings Form -->
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 space-y-4">
            <h3 class="text-sm font-black text-zinc-950">Global Expiry, Visibility &amp; Reminder Policy</h3>

            <form method="POST" action="{{ route('admin.vendor_management.automation.settings') }}" class="space-y-3.5">
                @csrf

                <label class="flex items-start gap-2.5 p-3 rounded-xl border border-zinc-200 bg-zinc-50 text-xs">
                    <input type="checkbox" name="enforce_subscription_visibility" value="1" @checked($settings['enforce_subscription_visibility']) class="mt-0.5">
                    <span>
                        <strong class="block text-zinc-950">Enforce Strict Subscription Visibility &amp; Feature Gating</strong>
                        <span class="text-zinc-500">When enabled, verified vendors require an active/grace subscription in a sector before their storefront, listings, or write actions are publicly active.</span>
                    </span>
                </label>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-zinc-700 mb-1">Default Grace Period (Days)</label>
                        <input type="number" name="default_grace_days" min="0" max="90" value="{{ $settings['default_grace_days'] }}" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-zinc-700 mb-1">Reminder Offsets (Days Before)</label>
                        <input type="text" name="default_reminder_days" value="{{ $settings['default_reminder_days'] }}" placeholder="7,3,1" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                    </div>
                </div>

                <label class="flex items-center justify-between text-xs p-2.5 rounded-xl border border-zinc-200">
                    <span>Keep storefront publicly visible during grace period</span>
                    <input type="checkbox" name="default_grace_keep_visible" value="1" @checked($settings['default_grace_keep_visible'])>
                </label>

                <label class="flex items-center justify-between text-xs p-2.5 rounded-xl border border-zinc-200">
                    <span>Allow creating/editing items during grace period</span>
                    <input type="checkbox" name="default_grace_keep_features" value="1" @checked($settings['default_grace_keep_features'])>
                </label>

                <div>
                    <label class="block text-[11px] font-bold text-zinc-700 mb-1">WhatsApp Renewal Message Template</label>
                    <textarea name="whatsapp_renewal_template" rows="3" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs">{{ $settings['whatsapp_renewal_template'] }}</textarea>
                    <p class="text-[10px] text-zinc-400 mt-1">Placeholders: {contact_person}, {shop_name}, {sector_name}, {plan_name}, {expiry_date}, {days_remaining}</p>
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl bg-zinc-950 text-white text-xs font-bold hover:bg-zinc-800">Save Automation Settings</button>
            </form>
        </div>

        <!-- Cron Instructions & Manual Sweep -->
        <div class="space-y-6">
            <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-black text-zinc-950">Scheduled Expiry Processor &amp; Cron Setup</h3>
                    <form method="POST" action="{{ route('admin.vendor_management.automation.run_now') }}">
                        @csrf
                        <button type="submit" class="px-3.5 py-2 rounded-xl bg-zinc-950 text-white text-xs font-bold hover:bg-zinc-800">
                            Run Expiry Processor Now
                        </button>
                    </form>
                </div>
                <p class="text-xs text-zinc-600">
                    Last Successful Run: <strong>{{ $settings['last_expiry_run_at'] ?: 'Never run yet' }}</strong>
                </p>
                <div class="rounded-xl bg-zinc-950 text-zinc-100 p-3 font-mono text-[11px] space-y-1 overflow-x-auto">
                    <p class="text-zinc-400"># Hostinger / Linux Crontab (runs hourly scheduler + JIT expiry guard):</p>
                    <p>* * * * * cd {{ base_path() }} && php artisan schedule:run >> /dev/null 2>&1</p>
                    <p class="text-zinc-400 mt-1"># Or direct manual command:</p>
                    <p>php artisan subscriptions:process-expiry</p>
                </div>
            </div>

            <!-- Job Execution Logs -->
            <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 space-y-3">
                <h3 class="text-sm font-black text-zinc-950">Recent Scheduler Job Runs</h3>
                <div class="divide-y divide-zinc-100 text-xs">
                    @forelse($jobRuns as $jr)
                        <div class="py-2 flex items-center justify-between gap-2">
                            <div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $jr->status === 'success' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">{{ $jr->status }}</span>
                                <span class="ml-1.5 text-zinc-700">{{ $jr->summary ?: $jr->error_message }}</span>
                            </div>
                            <span class="text-[10px] font-mono text-zinc-400">{{ $jr->started_at?->format('d M H:i') }}</span>
                        </div>
                    @empty
                        <p class="py-4 text-center text-zinc-400">No job runs recorded yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Deduplicated Subscription Notification Log -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 space-y-3">
        <h3 class="text-sm font-black text-zinc-950">Subscription Notifications &amp; Expiry Reminders Generated</h3>
        <div class="divide-y divide-zinc-100 text-xs">
            @forelse($notifications as $notif)
                <div class="py-2.5 flex items-start justify-between gap-3">
                    <div>
                        <span class="font-bold text-zinc-950">{{ $notif->title }}</span>
                        <span class="text-zinc-500">• {{ $notif->vendor?->shop_name ?? 'Vendor #' . $notif->vendor_id }}</span>
                        <p class="text-[11px] text-zinc-600 mt-0.5">{{ $notif->message }}</p>
                    </div>
                    <span class="text-[10px] font-mono text-zinc-400 shrink-0">{{ $notif->created_at?->format('d M Y H:i') }}</span>
                </div>
            @empty
                <p class="py-6 text-center text-zinc-400">No subscription notifications sent yet.</p>
            @endforelse
        </div>
    </div>
</x-layouts.admin>
