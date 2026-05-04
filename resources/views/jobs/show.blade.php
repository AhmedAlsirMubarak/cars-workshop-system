<x-layouts.app title="{{ __('Job') }} {{ $job->job_number }}">

    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('jobs.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900 font-mono">{{ $job->job_number }}</h2>
                    @include('components.status-badge', ['status' => $job->status])
                    @include('components.status-badge', ['status' => $job->priority])
                </div>
                <p class="text-sm text-gray-400 mt-0.5">{{ __('Created') }} {{ $job->created_at->format('d M Y H:i') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('jobs.edit', $job) }}" class="btn-secondary btn-sm">{{ __('Edit') }}</a>
            <button type="button" x-data
                class="btn-secondary btn-sm text-red-500 hover:text-red-700 hover:border-red-300"
                @click="$dispatch('open-confirm-delete', '{{ route('jobs.destroy', $job) }}')">
                {{ __('Delete') }}
            </button>
            @if($job->invoice)
            <a href="{{ route('invoices.show', $job->invoice) }}" class="btn-secondary btn-sm">{{ __('View Invoice') }}</a>
            @else
            <form method="POST" action="{{ route('invoices.generate-from-job', $job) }}">
                @csrf
                <button type="submit" class="btn-primary btn-sm">{{ __('Generate Invoice') }}</button>
            </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-6">

        {{-- Left / Main --}}
        <div class="xl:col-span-2 space-y-4 sm:space-y-6">

            {{-- Job Details --}}
            <div class="card p-5">
                <h3 class="font-semibold text-gray-900 mb-4">{{ __('Job Details') }}</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">{{ __('Customer') }}</p>
                        <a href="{{ route('customers.show', $job->customer) }}"
                            class="font-medium text-orange-500 hover:underline">{{ $job->customer?->name }}</a>
                        <p class="text-gray-500 text-xs mt-0.5">{{ $job->customer?->phone }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">{{ __('Vehicle') }}</p>
                        <a href="{{ route('vehicles.show', $job->vehicle) }}"
                            class="font-medium text-orange-500 hover:underline">
                            {{ $job->vehicle?->make }} {{ $job->vehicle?->model }}
                        </a>
                        <p class="text-xs text-gray-500 font-mono mt-0.5">{{ $job->vehicle?->plate_number }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">{{ __('Assigned Staff') }}</p>
                        @if($job->assignedStaff->isEmpty())
                        <p class="font-medium text-gray-400">{{ __('Unassigned') }}</p>
                        @else
                        <div class="flex flex-wrap gap-1 mt-0.5">
                            @foreach($job->assignedStaff as $s)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg bg-orange-50 text-orange-700 text-xs font-medium">{{ $s->display_name }}</span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">{{ __('Promised Date') }}</p>
                        <p class="font-medium text-gray-900">{{ $job->promised_at?->format('d M Y') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">{{ __('Mileage In') }}</p>
                        <p class="font-medium text-gray-900">{{ $job->mileage_in ? number_format($job->mileage_in) . ' km' : '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">{{ __('Mileage Out') }}</p>
                        <p class="font-medium text-gray-900">{{ $job->mileage_out ? number_format($job->mileage_out) . ' km' : '—' }}</p>
                    </div>
                    @if($job->started_at)
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">{{ __('Started') }}</p>
                        <p class="font-medium text-gray-900">{{ $job->started_at->format('d M Y H:i') }}</p>
                    </div>
                    @endif
                    @if($job->completed_at)
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">{{ __('Completed') }}</p>
                        <p class="font-medium text-gray-900">{{ $job->completed_at->format('d M Y H:i') }}</p>
                    </div>
                    @endif
                </div>

                <div class="mt-4 space-y-3">
                    @if($job->complaint)
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">{{ __('Customer Complaint') }}</p>
                        <p class="text-sm text-gray-700 bg-gray-50 rounded-xl p-3">{{ $job->complaint }}</p>
                    </div>
                    @endif
                    @if($job->diagnosis)
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">{{ __('Diagnosis') }}</p>
                        <p class="text-sm text-gray-700 bg-gray-50 rounded-xl p-3">{{ $job->diagnosis }}</p>
                    </div>
                    @endif
                    @if($job->work_performed)
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">{{ __('Work Performed') }}</p>
                        <p class="text-sm text-gray-700 bg-gray-50 rounded-xl p-3">{{ $job->work_performed }}</p>
                    </div>
                    @endif
                    @if($job->recommendations)
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">{{ __('Recommendations') }}</p>
                        <p class="text-sm text-gray-700 bg-gray-50 rounded-xl p-3">{{ $job->recommendations }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Line Items --}}
            @if($job->items->isNotEmpty())
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-gray-900">{{ __('Service Items') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>{{ __('Description') }}</th><th>{{ __('Type') }}</th><th class="text-center">{{ __('Qty') }}</th><th class="text-end">{{ __('Unit (OMR)') }}</th><th class="text-end">{{ __('Total (OMR)') }}</th></tr></thead>
                        <tbody>
                            @foreach($job->items as $item)
                            <tr>
                                <td class="text-gray-900">{{ $item->description }}</td>
                                <td class="capitalize text-gray-500 text-xs">{{ $item->type }}</td>
                                <td class="text-center text-gray-700">{{ $item->quantity }}</td>
                                <td class="text-end text-gray-700">{{ number_format($item->unit_price, 3) }}</td>
                                <td class="text-end font-semibold text-gray-900">{{ number_format($item->quantity * $item->unit_price, 3) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Parts Used --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-gray-900">{{ __('Parts Used') }}</h3>
                    <button type="button" class="btn-secondary btn-sm"
                        x-data @click="$dispatch('open-modal', 'add-part')">
                        + {{ __('Add Part') }}
                    </button>
                </div>
                @if($job->parts->isEmpty())
                <p class="px-5 py-8 text-center text-sm text-gray-400">{{ __('No parts added yet.') }}</p>
                @else
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>{{ __('Part') }}</th><th>{{ __('SKU') }}</th><th class="text-center">{{ __('Qty') }}</th><th class="text-end">{{ __('Unit (OMR)') }}</th><th class="text-end">{{ __('Total (OMR)') }}</th></tr></thead>
                        <tbody>
                            @foreach($job->parts as $jp)
                            <tr>
                                <td class="font-medium text-gray-900">{{ $jp->part?->name }}</td>
                                <td class="font-mono text-xs text-gray-500">{{ $jp->part?->sku }}</td>
                                <td class="text-center text-gray-700">{{ $jp->quantity }}</td>
                                <td class="text-end text-gray-700">{{ number_format($jp->unit_price, 3) }}</td>
                                <td class="text-end font-semibold text-gray-900">{{ number_format($jp->quantity * $jp->unit_price, 3) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

        {{-- Right sidebar --}}
        <div class="space-y-4">

            {{-- Update Status --}}
            <div class="card p-5">
                <h3 class="font-semibold text-gray-900 mb-4">{{ __('Update Job') }}</h3>
                <form method="POST" action="{{ route('jobs.update', $job) }}" class="space-y-3">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Status') }}</label>
                        <select name="status" class="input w-full text-sm">
                            @foreach(['pending' => __('Pending'), 'in_progress' => __('In Progress'), 'waiting_parts' => __('Waiting Parts'), 'completed' => __('Completed'), 'cancelled' => __('Cancelled')] as $val => $lbl)
                            <option value="{{ $val }}" @selected($job->status === $val)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Priority') }}</label>
                        <select name="priority" class="input w-full text-sm">
                            @foreach(['low' => __('Low'), 'normal' => __('Normal'), 'high' => __('High'), 'urgent' => __('Urgent')] as $val => $lbl)
                            <option value="{{ $val }}" @selected($job->priority === $val)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Work Performed') }}</label>
                        <textarea name="work_performed" rows="3" class="input w-full text-sm"
                            placeholder="{{ __('What was done…') }}">{{ $job->work_performed }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Recommendations') }}</label>
                        <textarea name="recommendations" rows="2" class="input w-full text-sm"
                            placeholder="{{ __('Future recommendations…') }}">{{ $job->recommendations }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Mileage Out (km)') }}</label>
                        <input type="number" name="mileage_out" value="{{ $job->mileage_out }}" min="0" class="input w-full text-sm">
                    </div>

                    <button type="submit" class="btn-primary w-full">{{ __('Save Changes') }}</button>
                </form>
            </div>

            {{-- Totals --}}
            <div class="card p-5">
                <h3 class="font-semibold text-gray-900 mb-3">{{ __('Cost Summary') }}</h3>
                @php
                    $jobItemsTotal = $job->items->sum(fn ($i) => (float)$i->quantity * (float)$i->unit_price);
                @endphp
                <div class="space-y-2 text-sm">
                    @if($job->labour_cost > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('Labour') }}</span>
                        <span class="font-medium text-gray-900">{{ number_format($job->labour_cost, 3) }} OMR</span>
                    </div>
                    @endif
                    @if($jobItemsTotal > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('Services') }}</span>
                        <span class="font-medium text-gray-900">{{ number_format($jobItemsTotal, 3) }} OMR</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('Parts') }}</span>
                        <span class="font-medium text-gray-900">{{ number_format($job->parts_cost, 3) }} OMR</span>
                    </div>
                    @if($job->discount > 0)
                    <div class="flex justify-between text-red-600">
                        <span>{{ __('Discount') }}</span>
                        <span>-{{ number_format($job->discount, 3) }} OMR</span>
                    </div>
                    @endif
                    @if($job->tax_rate > 0)
                    <div class="flex justify-between text-gray-600">
                        <span>{{ __('Tax') }} ({{ number_format($job->tax_rate, 1) }}%)</span>
                        <span>{{ number_format($job->tax_amount, 3) }} OMR</span>
                    </div>
                    @endif
                    <div class="border-t border-gray-100 pt-2 flex justify-between font-bold text-gray-900">
                        <span>{{ __('Total') }}</span>
                        <span>{{ number_format($job->total, 3) }} OMR</span>
                    </div>
                </div>
            </div>

            {{-- Invoice status --}}
            @if($job->invoice)
            <div class="card p-5">
                <h3 class="font-semibold text-gray-900 mb-3">{{ __('Invoice') }}</h3>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-mono text-sm text-orange-500">{{ $job->invoice->invoice_number }}</p>
                        @include('components.status-badge', ['status' => $job->invoice->status])
                    </div>
                    <a href="{{ route('invoices.show', $job->invoice) }}" class="btn-secondary btn-sm">{{ __('View') }}</a>
                </div>
            </div>
            @endif
        </div>
    </div>

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

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6" @click.stop>
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-semibold text-gray-900">{{ __('Add Part to Job') }}</h3>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('jobs.add-part', $job) }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Part') }} <span class="text-red-500">*</span></label>
                    <select name="part_id" required class="input w-full">
                        <option value="">{{ __('Select part…') }}</option>
                        @foreach($parts as $p)
                        <option value="{{ $p->id }}" data-price="{{ $p->selling_price }}">
                            {{ $p->name }} — {{ $p->sku }} ({{ $p->quantity_in_stock }} {{ __('in stock') }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Quantity') }} <span class="text-red-500">*</span></label>
                        <input type="number" name="quantity" required min="0.01" step="0.01" value="1" class="input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Unit Price (OMR)') }} <span class="text-red-500">*</span></label>
                        <input type="number" name="unit_price" id="partUnitPrice" required min="0" step="0.001" value="0.000" class="input w-full">
                    </div>
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

@push('scripts')
<script>
document.querySelector('[name="part_id"]')?.addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    const price = opt.dataset.price ?? '0';
    document.getElementById('partUnitPrice').value = parseFloat(price).toFixed(3);
});
</script>
@endpush
