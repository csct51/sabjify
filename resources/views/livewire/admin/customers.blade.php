<div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <p class="text-sm text-stone-400 mb-1">{{ $this->totalCustomers }} customers</p>
            <h2 class="text-lg font-semibold text-stone-900">Customers</h2>
        </div>
    </div>

    @error('toggle')
        <div class="mb-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
    @enderror

    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="customers-table" data-datatable class="w-full text-sm">
                <thead class="bg-stone-50 text-left text-xs uppercase tracking-wide text-stone-400">
                    <tr>
                        <th class="w-10 px-4 py-3 font-medium">#</th>
                        <th class="px-4 py-3 font-medium">Customer</th>
                        <th class="px-4 py-3 font-medium">Contact</th>
                        <th class="px-4 py-3 font-medium text-center">Number of Orders</th>
                        <th class="px-4 py-3 font-medium text-center">Total Order Amount</th>
                        <th class="px-4 py-3 font-medium">Joined</th>
                        <th class="px-4 py-3 font-medium text-center">Status</th>
                        <th class="px-4 py-3 font-medium text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($users as $user)
                        <tr class="hover:bg-stone-50" wire:key="user-{{ $user->id }}">
                            <td class="px-4 py-3"></td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.customers.show', $user) }}" wire:navigate class="flex items-center gap-3 group">
                                    <span class="flex items-center justify-center w-9 h-9 rounded-full bg-brand-100 text-brand-700 font-semibold text-sm">{{ $user->initials() }}</span>
                                    <p class="font-medium text-stone-900 group-hover:text-brand-600">{{ $user->name }}</p>
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-stone-600">+91 {{ $user->phone }}</p>
                                <p class="text-xs text-stone-400">{{ $user->email }}</p>
                            </td>
                            <td class="px-4 py-3 text-center text-stone-600">{{ $user->orders_count }}</td>
                            <td class="px-4 py-3 text-center text-stone-600">{{ \Illuminate\Support\Number::currency($user->orders_sum_total ?? 0, 'INR') }}</td>
                            <td class="px-4 py-3 text-stone-500">{{ $user->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium {{ $user->is_active ? 'text-green-600' : 'text-red-600' }}">
                                    <span class="w-2 h-2 rounded-full {{ $user->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                    {{ $user->is_active ? 'Active' : 'Blocked' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($user->is_active)
                                    <button type="button" data-confirm-message="Block {{ $user->name }}? They won't be able to log in." @click="$dispatch('confirm-modal', { message: $el.dataset.confirmMessage, action: () => $wire.toggleActive({{ $user->id }}) })" class="rounded-lg border border-red-200 hover:bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600">Block</button>
                                @else
                                    <button type="button" data-confirm-message="Unblock {{ $user->name }}? They'll be able to log in again." @click="$dispatch('confirm-modal', { message: $el.dataset.confirmMessage, action: () => $wire.toggleActive({{ $user->id }}) })" class="rounded-lg border border-green-200 hover:bg-green-50 px-3 py-1.5 text-xs font-medium text-green-600">Unblock</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
