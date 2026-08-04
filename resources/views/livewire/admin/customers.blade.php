<div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <p class="text-sm text-stone-400 mb-1">{{ $this->totalCustomers }} customers</p>
            <h2 class="text-lg font-semibold text-stone-900">Customers</h2>
        </div>
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search name, phone or email..." class="w-full sm:w-80 rounded-xl border border-stone-300 px-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
    </div>

    @error('toggle')
        <div class="mb-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
    @enderror

    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 text-left text-xs uppercase tracking-wide text-stone-400">
                    <tr>
                        <th class="px-4 py-3 font-medium">Customer</th>
                        <th class="px-4 py-3 font-medium">Contact</th>
                        <th class="px-4 py-3 font-medium text-center">Role</th>
                        <th class="px-4 py-3 font-medium text-center">Orders</th>
                        <th class="px-4 py-3 font-medium">Joined</th>
                        <th class="px-4 py-3 font-medium text-center">Status</th>
                        <th class="px-4 py-3 font-medium text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($users as $user)
                        <tr class="hover:bg-stone-50" wire:key="user-{{ $user->id }}">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex items-center justify-center w-9 h-9 rounded-full bg-brand-100 text-brand-700 font-semibold text-sm">{{ $user->initials() }}</span>
                                    <p class="font-medium text-stone-900">{{ $user->name }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-stone-600">+91 {{ $user->phone }}</p>
                                <p class="text-xs text-stone-400">{{ $user->email }}</p>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium {{ $user->isAdmin() ? 'border-violet-200 bg-violet-50 text-violet-700' : 'border-stone-200 bg-stone-50 text-stone-600' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-stone-600">{{ $user->orders_count }}</td>
                            <td class="px-4 py-3 text-stone-500">{{ $user->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-center">
                                <button type="button" wire:click="toggleActive({{ $user->id }})" class="inline-flex items-center gap-1.5 text-xs font-medium {{ $user->is_active ? 'text-green-600' : 'text-red-600' }}">
                                    <span class="w-2 h-2 rounded-full {{ $user->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                    {{ $user->is_active ? 'Active' : 'Blocked' }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($user->is_active)
                                    <button type="button" wire:click="toggleActive({{ $user->id }})" wire:confirm="Block {{ $user->name }}? They won't be able to log in." class="text-red-600 hover:text-red-700 font-medium text-xs">Block</button>
                                @else
                                    <button type="button" wire:click="toggleActive({{ $user->id }})" class="text-green-600 hover:text-green-700 font-medium text-xs">Unblock</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-16 text-center text-stone-400">No customers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-stone-100">
            {{ $users->links() }}
        </div>
    </div>
</div>
