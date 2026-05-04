<x-layouts.app title="{{ __('Inventory') }}">

    {{-- Filters --}}
    <form method="GET" action="{{ route('inventory.index') }}" class="flex flex-wrap items-center gap-3 mb-6">
        <div x-data="searchBox()">
            <input type="text" name="search" x-model="query" @input="debounce()"
                placeholder="{{ __('Search SKU or name…') }}" class="input w-56"
                value="{{ request('search') }}">
        </div>

        <select name="category" onchange="this.form.submit()" class="input w-40">
            <option value="">{{ __('All categories') }}</option>
            @foreach($categories as $cat)
            <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ $cat }}</option>
            @endforeach
        </select>

        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
            <input type="checkbox" name="low_stock" value="1" onchange="this.form.submit()"
                @checked(request('low_stock')) class="rounded border-gray-300 text-orange-500">
            {{ __('Low stock only') }}
        </label>

        <div class="ms-auto flex items-center gap-2">
            <a href="{{ route('inventory.export', request()->only('search', 'category', 'low_stock')) }}"
                class="btn-secondary flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                {{ __('Export CSV') }}
            </a>
            <button type="button" class="btn-primary" x-data @click="$dispatch('open-modal', 'add-part')">
                + {{ __('Add Part') }}
            </button>
        </div>
    </form>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="hidden sm:block overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('SKU') }}</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Category') }}</th>
                        <th class="text-end">{{ __('Cost (OMR)') }}</th>
                        <th class="text-end">{{ __('Selling (OMR)') }}</th>
                        <th class="text-center">{{ __('Stock') }}</th>
                        <th class="text-center">{{ __('Reorder') }}</th>
                        <th class="text-center">{{ __('Status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($parts as $part)
                    @php $isLow = $part->quantity_in_stock <= $part->reorder_level; @endphp
                    <tr class="{{ $isLow ? 'bg-amber-50/50' : '' }}">
                        <td class="font-mono text-xs font-semibold text-gray-600">{{ $part->sku }}</td>
                        <td>
                            <p class="font-medium text-gray-900">{{ $part->name }}</p>
                            @if($part->brand)
                            <p class="text-xs text-gray-400">{{ $part->brand }}</p>
                            @endif
                        </td>
                        <td class="text-sm text-gray-500">{{ $part->category ?? '—' }}</td>
                        <td class="text-end text-sm text-gray-700">{{ number_format($part->cost_price, 3) }}</td>
                        <td class="text-end text-sm font-semibold text-gray-900">{{ number_format($part->selling_price, 3) }}</td>
                        <td class="text-center font-bold {{ $isLow ? 'text-red-600' : 'text-gray-900' }}">{{ $part->quantity_in_stock }}</td>
                        <td class="text-center text-gray-400 text-sm">{{ $part->reorder_level }}</td>
                        <td class="text-center">
                            @if($isLow)
                            <span class="badge-orange">{{ __('Low Stock') }}</span>
                            @else
                            <span class="badge-green">{{ __('OK') }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center gap-2" x-data="{ editOpen: false }">
                                <button @click="editOpen = !editOpen" class="text-xs text-gray-400 hover:text-orange-500 transition">{{ __('Edit') }}</button>
                                <button type="button"
                                    class="text-xs text-red-400 hover:text-red-600 transition"
                                    @click="$dispatch('open-confirm-delete', '{{ route('inventory.destroy', $part) }}')">
                                    {{ __('Delete') }}
                                </button>
                                {{-- Inline edit row --}}
                                <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
                                    @keydown.escape.window="editOpen = false">
                                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6" @click.stop>
                                        <h3 class="font-semibold text-gray-900 mb-4">{{ __('Edit') }} — {{ $part->name }}</h3>
                                        <form method="POST" action="{{ route('inventory.update', $part) }}" class="space-y-3">
                                            @csrf
                                            @method('PUT')
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Name') }}</label>
                                                    <input type="text" name="name" value="{{ $part->name }}" required class="input w-full text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Category') }}</label>
                                                    <input type="text" name="category" value="{{ $part->category }}" class="input w-full text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Cost (OMR)') }}</label>
                                                    <input type="number" name="cost_price" value="{{ $part->cost_price }}" step="0.001" min="0" required class="input w-full text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Selling (OMR)') }}</label>
                                                    <input type="number" name="selling_price" value="{{ $part->selling_price }}" step="0.001" min="0" required class="input w-full text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Stock Qty') }}</label>
                                                    <input type="number" name="quantity_in_stock" value="{{ $part->quantity_in_stock }}" min="0" required class="input w-full text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Reorder Level') }}</label>
                                                    <input type="number" name="reorder_level" value="{{ $part->reorder_level }}" min="0" required class="input w-full text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Supplier') }}</label>
                                                    <input type="text" name="supplier" value="{{ $part->supplier }}" class="input w-full text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Location') }}</label>
                                                    <input type="text" name="location" value="{{ $part->location }}" class="input w-full text-sm">
                                                </div>
                                            </div>
                                            <div class="flex gap-3 pt-2">
                                                <button type="submit" class="btn-primary flex-1">{{ __('Save') }}</button>
                                                <button type="button" @click="editOpen = false" class="btn-secondary flex-1">{{ __('Cancel') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-12 text-center text-sm text-gray-400">{{ __('No parts found.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile --}}
        <div class="sm:hidden divide-y divide-gray-50">
            @forelse($parts as $part)
            @php $isLow = $part->quantity_in_stock <= $part->reorder_level; @endphp
            <div class="p-4 {{ $isLow ? 'bg-amber-50/50' : '' }}">
                <div class="flex justify-between gap-3 mb-1">
                    <div>
                        <p class="font-medium text-gray-900">{{ $part->name }}</p>
                        <p class="text-xs text-gray-400 font-mono">{{ $part->sku }}</p>
                    </div>
                    <div class="text-end">
                        <p class="font-bold {{ $isLow ? 'text-red-600' : 'text-gray-900' }}">{{ $part->quantity_in_stock }} {{ __('in stock') }}</p>
                        <p class="text-xs text-gray-400">{{ number_format($part->selling_price, 3) }} OMR</p>
                    </div>
                </div>
                @if($isLow)
                <span class="badge-orange">{{ __('Low Stock') }}</span>
                @endif
            </div>
            @empty
            <p class="py-12 text-center text-sm text-gray-400">{{ __('No parts found.') }}</p>
            @endforelse
        </div>
    </div>

    @include('components.pagination', ['paginator' => $parts])

    {{-- Add Part Modal --}}
    @push('modals')
    <div x-data="{ open: false }"
        @open-modal.window="if ($event.detail === 'add-part') open = true"
        x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        @keydown.escape.window="open = false">

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 overflow-y-auto max-h-[90vh]"
            @click.stop>
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-semibold text-gray-900">{{ __('Add Part / Component') }}</h3>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('inventory.store') }}" class="space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('SKU') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="sku" required class="input w-full text-sm" placeholder="PART-001">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('Name') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required class="input w-full text-sm" placeholder="{{ __('Part name') }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('Category') }}</label>
                        <input type="text" name="category" class="input w-full text-sm" placeholder="{{ __('e.g. Brakes') }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('Brand') }}</label>
                        <input type="text" name="brand" class="input w-full text-sm" placeholder="{{ __('Brand name') }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('Cost Price (OMR)') }} <span class="text-red-500">*</span></label>
                        <input type="number" name="cost_price" required step="0.001" min="0" class="input w-full text-sm" placeholder="0.000">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('Selling Price (OMR)') }} <span class="text-red-500">*</span></label>
                        <input type="number" name="selling_price" required step="0.001" min="0" class="input w-full text-sm" placeholder="0.000">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('Stock Qty') }} <span class="text-red-500">*</span></label>
                        <input type="number" name="quantity_in_stock" required min="0" value="0" class="input w-full text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('Reorder Level') }} <span class="text-red-500">*</span></label>
                        <input type="number" name="reorder_level" required min="0" value="5" class="input w-full text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('Location') }}</label>
                        <input type="text" name="location" class="input w-full text-sm" placeholder="{{ __('Shelf / bin') }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('Supplier') }}</label>
                        <input type="text" name="supplier" class="input w-full text-sm" placeholder="{{ __('Supplier name') }}">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('Description') }}</label>
                    <textarea name="description" rows="2" class="input w-full text-sm" placeholder="{{ __('Optional notes…') }}"></textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary flex-1">{{ __('Add Part') }}</button>
                    <button type="button" @click="open = false" class="btn-secondary flex-1">{{ __('Cancel') }}</button>
                </div>
            </form>
        </div>
    </div>
    @endpush

</x-layouts.app>
