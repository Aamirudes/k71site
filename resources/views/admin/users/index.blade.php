<x-layouts.admin title="User Directory &amp; Access Controls">
    <div class="space-y-6">
        
        <!-- Header Banner -->
        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-zinc-200/80 shadow-xs flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-[10px] font-mono uppercase font-bold tracking-wider px-2 py-0.5 rounded bg-zinc-100 text-zinc-700">
                        Account Directory
                    </span>
                    <span class="text-xs text-zinc-400">• Access Permissions</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-bold text-zinc-950 tracking-tight">User Directory &amp; Access Controls</h2>
                <p class="text-xs sm:text-sm text-zinc-500 mt-0.5">Manage customer accounts, assign administrative roles, and audit marketplace participants.</p>
            </div>
            <div class="flex items-center gap-2.5 shrink-0">
                <span class="px-3.5 py-1.5 rounded-lg bg-zinc-950 text-xs font-mono font-bold text-white shadow-xs">
                    {{ $users->total() ?? $users->count() }} Registered Accounts
                </span>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-4 text-xs sm:text-sm flex items-center gap-3 shadow-xs">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-xl p-4 text-xs sm:text-sm flex items-center gap-3 shadow-xs">
                <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Filter & Search Panel -->
        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-zinc-200/80 shadow-xs space-y-4">
            
            <!-- Filter Tabs -->
            <div class="flex flex-wrap items-center gap-2 border-b border-zinc-100 pb-4">
                <a href="{{ route('admin.users.index') }}" 
                   class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ !request('role') ? 'bg-zinc-950 text-white shadow-xs' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200' }}">
                    All Accounts ({{ $counts['all'] }})
                </a>
                <a href="{{ route('admin.users.index', array_merge(request()->query(), ['role' => 'admin'])) }}" 
                   class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ request('role') === 'admin' ? 'bg-zinc-950 text-white shadow-xs' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200' }}">
                    Administrators ({{ $counts['admins'] }})
                </a>
                <a href="{{ route('admin.users.index', array_merge(request()->query(), ['role' => 'vendor'])) }}" 
                   class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ request('role') === 'vendor' ? 'bg-zinc-950 text-white shadow-xs' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200' }}">
                    Vendors &amp; Creators ({{ $counts['vendors'] }})
                </a>
                <a href="{{ route('admin.users.index', array_merge(request()->query(), ['role' => 'customer'])) }}" 
                   class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ request('role') === 'customer' ? 'bg-zinc-950 text-white shadow-xs' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200' }}">
                    Customers ({{ $counts['customers'] }})
                </a>
            </div>

            <!-- Search Form -->
            <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                @if(request('role'))
                    <input type="hidden" name="role" value="{{ request('role') }}">
                @endif
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, phone number..." class="w-full pl-10 pr-4 py-2 bg-zinc-50 border border-zinc-200 rounded-lg text-xs font-medium text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-950 focus:bg-white transition">
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="px-5 py-2 bg-zinc-950 text-white text-xs font-semibold rounded-lg hover:bg-zinc-800 transition shadow-xs cursor-pointer">
                        Search
                    </button>
                    @if(request()->hasAny(['search', 'role']))
                        <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-zinc-100 text-zinc-600 text-xs font-semibold rounded-lg hover:bg-zinc-200 transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto no-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-zinc-100 bg-zinc-50/80 text-[10px] font-mono uppercase tracking-wider text-zinc-500">
                            <th class="py-3 px-5">User &amp; Profile</th>
                            <th class="py-3 px-4">Contact Info</th>
                            <th class="py-3 px-4">Role &amp; Permissions</th>
                            <th class="py-3 px-4">Affiliated Store</th>
                            <th class="py-3 px-4">Orders</th>
                            <th class="py-3 px-4">Registered</th>
                            <th class="py-3 px-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 text-xs">
                        @forelse($users as $user)
                            <tr class="hover:bg-zinc-50 transition group">
                                <td class="py-3.5 px-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-zinc-100 text-zinc-900 font-bold flex items-center justify-center shrink-0 border border-zinc-200 text-xs">
                                            {{ strtoupper(substr($user->first_name ?: ($user->name ?: 'U'), 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-zinc-950 flex items-center gap-1.5">
                                                <span>{{ $user->first_name ? ($user->first_name . ' ' . $user->last_name) : ($user->name ?: 'Customer') }}</span>
                                                @if($user->id === auth()->id())
                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-bold bg-zinc-950 text-white">YOU</span>
                                                @endif
                                            </div>
                                            <div class="text-[10px] text-zinc-400 font-mono">ID: #{{ $user->id }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="text-zinc-900 font-medium">{{ $user->email }}</div>
                                    <div class="text-zinc-400 text-[11px]">{{ $user->phone ?: '—' }}</div>
                                </td>

                                <td class="py-3.5 px-4">
                                    <form action="{{ route('admin.users.role', $user) }}" method="POST" class="inline-flex items-center gap-2">
                                        @csrf
                                        <select name="role" onchange="this.form.submit()" class="text-xs font-semibold py-1 px-2 rounded-lg border border-zinc-200 bg-white text-zinc-900 cursor-pointer focus:ring-2 focus:ring-zinc-950 focus:outline-none">
                                            <option value="customer" {{ ($user->role === 'customer' || empty($user->role)) ? 'selected' : '' }}>Customer</option>
                                            <option value="vendor" {{ $user->role === 'vendor' ? 'selected' : '' }}>Vendor</option>
                                            <option value="admin" {{ ($user->role === 'admin' || $user->is_admin) ? 'selected' : '' }}>Admin</option>
                                        </select>
                                    </form>
                                </td>

                                <td class="py-3.5 px-4">
                                    @if($user->vendor)
                                        <a href="{{ route('admin.vendors', ['search' => $user->vendor->shop_name]) }}" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-zinc-100 text-zinc-800 border border-zinc-200 font-semibold hover:bg-zinc-200 transition">
                                            <span>🏪</span> {{ $user->vendor->shop_name }}
                                        </a>
                                    @else
                                        <span class="text-zinc-400 text-[11px]">—</span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 font-mono font-semibold text-zinc-800">
                                    {{ $user->orders_count ?? 0 }}
                                </td>

                                <td class="py-3.5 px-4 text-zinc-500 font-mono text-[11px]">
                                    {{ $user->created_at ? $user->created_at->format('M d, Y') : '—' }}
                                </td>

                                <td class="py-3.5 px-5 text-right">
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete user {{ $user->name }}? This action cannot be undone.');" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-zinc-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Delete User">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-zinc-300 text-xs font-mono">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-zinc-400">
                                    <svg class="w-8 h-8 mx-auto text-zinc-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    <p class="font-medium text-xs">No users found matching your criteria.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="p-4 border-t border-zinc-100 bg-zinc-50/50">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

    </div>
</x-layouts.admin>
