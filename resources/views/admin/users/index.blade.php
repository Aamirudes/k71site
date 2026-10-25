<x-layouts.admin title="Manage Users & Marketplace Accounts">
    <div class="space-y-6">
        
        <!-- Header Banner -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                        Admin Power Center
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Account Directory
                    </span>
                </div>
                <h2 class="text-2xl font-serif font-bold text-slate-900 mt-2">User Directory &amp; Access Controls</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Full administrative authority to manage accounts, update access roles, and permanently delete users or spam accounts.</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-3.5 py-2 rounded-2xl bg-slate-900 text-xs font-mono font-bold text-white shadow-xs">
                    {{ $users->total() ?? $users->count() }} Users Registered
                </span>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl p-4 text-xs sm:text-sm flex items-center gap-3 shadow-xs">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl p-4 text-xs sm:text-sm flex items-center gap-3 shadow-xs">
                <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Filter & Search Panel -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            
            <!-- Filter Tabs -->
            <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 pb-4">
                <a href="{{ route('admin.users.index') }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ !request('role') ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    All Users ({{ $counts['all'] }})
                </a>
                <a href="{{ route('admin.users.index', array_merge(request()->query(), ['role' => 'admin'])) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ request('role') === 'admin' ? 'bg-purple-600 text-white shadow-xs' : 'bg-purple-50 text-purple-700 hover:bg-purple-100' }}">
                    Administrators ({{ $counts['admins'] }})
                </a>
                <a href="{{ route('admin.users.index', array_merge(request()->query(), ['role' => 'vendor'])) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ request('role') === 'vendor' ? 'bg-amber-600 text-white shadow-xs' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                    Vendors / Creators ({{ $counts['vendors'] }})
                </a>
                <a href="{{ route('admin.users.index', array_merge(request()->query(), ['role' => 'customer'])) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ request('role') === 'customer' ? 'bg-blue-600 text-white shadow-xs' : 'bg-blue-50 text-blue-700 hover:bg-blue-100' }}">
                    Customers ({{ $counts['customers'] }})
                </a>
            </div>

            <!-- Search Form -->
            <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                @if(request('role'))
                    <input type="hidden" name="role" value="{{ request('role') }}">
                @endif
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, phone number, or boutique..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:bg-white transition">
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="px-5 py-2 bg-slate-900 text-white text-xs font-bold rounded-xl hover:bg-slate-800 transition shadow-xs">
                        Search
                    </button>
                    @if(request()->hasAny(['search', 'role']))
                        <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-slate-100 text-slate-600 text-xs font-semibold rounded-xl hover:bg-slate-200 transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/50 text-[11px] font-mono uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-6">User &amp; Profile</th>
                            <th class="py-3.5 px-4">Contact Info</th>
                            <th class="py-3.5 px-4">Role &amp; Permissions</th>
                            <th class="py-3.5 px-4">Affiliated Store</th>
                            <th class="py-3.5 px-4">Orders</th>
                            <th class="py-3.5 px-4">Registered</th>
                            <th class="py-3.5 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($users as $user)
                            <tr class="hover:bg-slate-50/60 transition group">
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-2xl bg-purple-100 text-purple-700 font-bold flex items-center justify-center shrink-0 border border-purple-200">
                                            {{ strtoupper(substr($user->first_name ?: ($user->name ?: 'U'), 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                                <span>{{ $user->first_name ? ($user->first_name . ' ' . $user->last_name) : ($user->name ?: 'Customer') }}</span>
                                                @if($user->id === auth()->id())
                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-purple-100 text-purple-800">YOU</span>
                                                @endif
                                            </div>
                                            <div class="text-[11px] text-slate-400 font-mono">ID: #{{ $user->id }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-4 px-4">
                                    <div class="text-slate-900 font-medium">{{ $user->email }}</div>
                                    <div class="text-slate-400 text-[11px]">{{ $user->phone ?: 'No phone provided' }}</div>
                                </td>

                                <td class="py-4 px-4">
                                    <form action="{{ route('admin.users.role', $user) }}" method="POST" class="inline-flex items-center gap-2">
                                        @csrf
                                        <select name="role" onchange="this.form.submit()" class="text-xs font-semibold py-1 px-2.5 rounded-lg border border-slate-200 bg-white cursor-pointer focus:ring-1 focus:ring-purple-600 focus:outline-none">
                                            <option value="customer" {{ ($user->role === 'customer' || empty($user->role)) ? 'selected' : '' }}>Customer</option>
                                            <option value="vendor" {{ $user->role === 'vendor' ? 'selected' : '' }}>Vendor</option>
                                            <option value="admin" {{ ($user->role === 'admin' || $user->is_admin) ? 'selected' : '' }}>Admin</option>
                                        </select>
                                    </form>
                                </td>

                                <td class="py-4 px-4">
                                    @if($user->vendor)
                                        <a href="{{ route('admin.vendors', ['search' => $user->vendor->shop_name]) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 font-bold hover:bg-amber-100 transition">
                                            <span>🏪</span> {{ $user->vendor->shop_name }}
                                        </a>
                                    @else
                                        <span class="text-slate-400 text-[11px]">—</span>
                                    @endif
                                </td>

                                <td class="py-4 px-4 font-mono font-bold text-slate-700">
                                    {{ $user->orders_count ?? 0 }}
                                </td>

                                <td class="py-4 px-4 text-slate-500 text-[11px] font-mono">
                                    {{ $user->created_at?->format('d M Y') ?? '—' }}
                                </td>

                                <td class="py-4 px-6 text-right">
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('⚠️ DANGER: Are you completely sure you want to permanently delete user \'{{ $user->name ?: $user->email }}\' (ID #{{ $user->id }})? This action cannot be undone.');" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-rose-500 hover:text-white hover:bg-rose-600 rounded-xl transition border border-rose-200 hover:border-rose-600 shadow-xs cursor-pointer" title="Delete User Permanently">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-[11px] text-slate-300 font-mono italic">Protected</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400 text-xs">
                                    No users found matching your search criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
