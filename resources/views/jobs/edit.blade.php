<x-layouts.app title="{{ __('Edit Job') }} {{ $job->job_number }}">

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('jobs.show', $job) }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ __('Edit Job Order') }}</h2>
            <p class="text-sm text-gray-400 font-mono">{{ $job->job_number }}</p>
        </div>
    </div>

    <div class="max-w-3xl"
        x-data="{
            customerId: '{{ $job->customer_id }}',
            items: {{ Illuminate\Support\Js::from($job->items->map(fn ($i) => ['description' => $i->description, 'type' => $i->type, 'qty' => (float)$i->quantity, 'price' => (float)$i->unit_price])) }},
            labourCost: {{ (float)($job->labour_cost ?? 0) }},
            get itemsTotal() {
                return this.items.reduce((s, i) => s + (parseFloat(i.qty || 0) * parseFloat(i.price || 0)), 0);
            },
            get grandTotal() {
                return this.itemsTotal + parseFloat(this.labourCost || 0);
            },
            addItem() {
                this.items.push({ description: '', type: 'labour', qty: 1, price: 0 });
            },
            removeItem(idx) {
                this.items.splice(idx, 1);
            }
        }">

        <form method="POST" action="{{ route('jobs.update', $job) }}" class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="from_edit" value="1">

            @if($errors->any())
            <div class="rounded-xl bg-red-50 border border-red-200 p-4 text-sm text-red-700 space-y-1">
                @foreach($errors->all() as $err)
                <p>{{ $err }}</p>
                @endforeach
            </div>
            @endif

            {{-- Customer & Vehicle --}}
            <div class="card p-5">
                <h3 class="font-semibold text-gray-900 mb-4">{{ __('Customer & Vehicle') }}</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Customer') }} <span class="text-red-500">*</span></label>
                        <select name="customer_id" required class="input w-full" x-model="customerId">
                            <option value="">{{ __('Select customer…') }}</option>
                            @foreach($customers as $c)
                            <option value="{{ $c->id }}" @selected($job->customer_id == $c->id)>{{ $c->name }} — {{ $c->phone }}</option>
                            @endforeach
                        </select>
                        @error('customer_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Vehicle') }} <span class="text-red-500">*</span></label>
                        <select name="vehicle_id" required class="input w-full">
                            <option value="">{{ __('Select vehicle…') }}</option>
                            @foreach($customers as $c)
                            @foreach($c->vehicles ?? [] as $v)
                            <option value="{{ $v->id }}"
                                data-customer="{{ $c->id }}"
                                @selected($job->vehicle_id == $v->id)>
                                {{ $v->make }} {{ $v->model }} — {{ $v->plate_number }}
                            </option>
                            @endforeach
                            @endforeach
                        </select>
                        @error('vehicle_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Assigned Staff') }}</label>
                        @php $assignedIds = old('staff_ids', $job->assignedStaff->pluck('id')->toArray()); @endphp
                        <div class="border border-gray-300 rounded-xl divide-y divide-gray-100 max-h-44 overflow-y-auto">
                            @forelse($staff as $s)
                            <label class="flex items-center gap-3 px-3 py-2 hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" name="staff_ids[]" value="{{ $s->id }}"
                                    @checked(in_array($s->id, $assignedIds))
                                    class="rounded border-gray-300 text-orange-500 focus:ring-orange-400">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900">{{ $s->display_name }}</p>
                                    @if($s->specialization)
                                    <p class="text-xs text-gray-400">{{ $s->specialization }}</p>
                                    @endif
                                </div>
                            </label>
                            @empty
                            <p class="px-3 py-3 text-sm text-gray-400">{{ __('No active staff.') }}</p>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Priority') }}</label>
                        <select name="priority" class="input w-full">
                            @foreach(['low' => __('Low'), 'normal' => __('Normal'), 'high' => __('High'), 'urgent' => __('Urgent')] as $val => $lbl)
                            <option value="{{ $val }}" @selected($job->priority === $val)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Status') }}</label>
                        <select name="status" class="input w-full">
                            @foreach(['pending' => __('Pending'), 'in_progress' => __('In Progress'), 'waiting_parts' => __('Waiting Parts'), 'completed' => __('Completed'), 'cancelled' => __('Cancelled')] as $val => $lbl)
                            <option value="{{ $val }}" @selected($job->status === $val)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Promised Date') }}</label>
                        <input type="date" name="promised_at"
                            value="{{ $job->promised_at?->format('Y-m-d') }}"
                            class="input w-full">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Mileage In (km)') }}</label>
                        <input type="number" name="mileage_in"
                            value="{{ $job->mileage_in }}"
                            min="0" class="input w-full" placeholder="{{ __('Current odometer') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Discount (OMR)') }}</label>
                        <input type="number" name="discount"
                            value="{{ number_format($job->discount, 3, '.', '') }}"
                            min="0" step="0.001" class="input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Tax Rate (%)') }}</label>
                        <input type="number" name="tax_rate"
                            value="{{ number_format($job->tax_rate, 2, '.', '') }}"
                            min="0" max="100" step="0.01" class="input w-full">
                    </div>
                </div>
            </div>

            {{-- Work Details --}}
            <div class="card p-5">
                <h3 class="font-semibold text-gray-900 mb-4">{{ __('Work Details') }}</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Customer Complaint') }}</label>
                        <textarea name="complaint" rows="3" class="input w-full"
                            placeholder="{{ __("Describe the customer's complaint…") }}">{{ $job->complaint }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Initial Diagnosis') }}</label>
                        <textarea name="diagnosis" rows="3" class="input w-full"
                            placeholder="{{ __("Technician's initial diagnosis…") }}">{{ $job->diagnosis }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Work Performed') }}</label>
                        <textarea name="work_performed" rows="3" class="input w-full"
                            placeholder="{{ __('What was done…') }}">{{ $job->work_performed }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Recommendations') }}</label>
                        <textarea name="recommendations" rows="2" class="input w-full"
                            placeholder="{{ __('Future recommendations…') }}">{{ $job->recommendations }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Service Items --}}
            <div class="card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-900">{{ __('Service Items') }}</h3>
                    <button type="button" @click="addItem()" class="btn-secondary btn-sm">+ {{ __('Add Item') }}</button>
                </div>

                <div class="space-y-3">
                    <template x-for="(item, idx) in items" :key="idx">
                        <div class="grid grid-cols-12 gap-2 items-end">
                            <div class="col-span-5">
                                <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Description') }}</label>
                                <input type="text" :name="`items[${idx}][description]`" x-model="item.description"
                                    required class="input w-full text-sm" placeholder="{{ __('Service description') }}">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Type') }}</label>
                                <select :name="`items[${idx}][type]`" x-model="item.type" class="input w-full text-sm">
                                    <option value="labour">{{ __('Labour') }}</option>
                                    <option value="part">{{ __('Part') }}</option>
                                    <option value="service">{{ __('Service') }}</option>
                                    <option value="other">{{ __('Other') }}</option>
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Qty') }}</label>
                                <input type="number" :name="`items[${idx}][quantity]`" x-model="item.qty"
                                    min="0.01" step="0.01" required class="input w-full text-sm">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Unit (OMR)') }}</label>
                                <input type="number" :name="`items[${idx}][unit_price]`" x-model="item.price"
                                    min="0" step="0.001" required class="input w-full text-sm">
                            </div>
                            <div class="col-span-1 flex justify-end pb-1">
                                <button type="button" @click="removeItem(idx)"
                                    class="text-red-400 hover:text-red-600 transition p-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>

                    <template x-if="items.length === 0">
                        <p class="text-sm text-gray-400 text-center py-4">{{ __('No items. Click "Add Item" to add service lines.') }}</p>
                    </template>
                </div>

                {{-- Labour Cost + Total --}}
                <div class="mt-5 pt-4 border-t border-gray-100">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <label class="text-sm font-medium text-gray-700 whitespace-nowrap">{{ __('Labour Cost (OMR)') }}</label>
                            <input type="number" name="labour_cost" x-model="labourCost"
                                min="0" step="0.001"
                                class="input w-36 text-sm">
                        </div>
                        <div class="text-end">
                            <p class="text-xs text-gray-400">{{ __('Estimated Total') }}</p>
                            <p class="text-xl font-bold text-gray-900">
                                <span x-text="grandTotal.toFixed(3)">0.000</span> OMR
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="btn-primary">{{ __('Save Changes') }}</button>
                <a href="{{ route('jobs.show', $job) }}" class="btn-secondary">{{ __('Cancel') }}</a>
                <button type="button"
                    class="ms-auto text-sm text-red-500 hover:text-red-700 transition"
                    x-data @click="$dispatch('open-confirm-delete', '{{ route('jobs.destroy', $job) }}')">
                    {{ __('Delete Job') }}
                </button>
            </div>
        </form>
    </div>

</x-layouts.app>

@push('scripts')
<script>
document.querySelector('[name="customer_id"]')?.addEventListener('change', function () {
    const cid = this.value;
    const vSelect = document.querySelector('[name="vehicle_id"]');
    Array.from(vSelect.options).forEach(opt => {
        if (!opt.value) return;
        opt.hidden = cid && opt.dataset.customer !== cid;
    });
    vSelect.value = '';
});
</script>
@endpush
