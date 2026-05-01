<x-layouts.app title="New Job Order">

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('jobs.index') }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h2 class="text-lg font-semibold text-gray-900">New Job Order</h2>
    </div>

    <div class="max-w-3xl"
        x-data="{
            customerId: '',
            items: [],
            labourCost: 0,
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

        <form method="POST" action="{{ route('jobs.store') }}" class="space-y-6">
            @csrf

            @if($errors->any())
            <div class="rounded-xl bg-red-50 border border-red-200 p-4 text-sm text-red-700 space-y-1">
                @foreach($errors->all() as $err)
                <p>{{ $err }}</p>
                @endforeach
            </div>
            @endif

            {{-- Customer & Vehicle --}}
            <div class="card p-5">
                <h3 class="font-semibold text-gray-900 mb-4">Customer &amp; Vehicle</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Customer <span class="text-red-500">*</span></label>
                        <select name="customer_id" required class="input w-full" x-model="customerId">
                            <option value="">Select customer…</option>
                            @foreach($customers as $c)
                            <option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>{{ $c->name }} — {{ $c->phone }}</option>
                            @endforeach
                        </select>
                        @error('customer_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle <span class="text-red-500">*</span></label>
                        <select name="vehicle_id" required class="input w-full">
                            <option value="">Select vehicle…</option>
                            @foreach($customers as $c)
                            @foreach($c->vehicles ?? [] as $v)
                            <option value="{{ $v->id }}"
                                data-customer="{{ $c->id }}"
                                @selected(old('vehicle_id', $selected_vehicle_id) == $v->id)>
                                {{ $v->make }} {{ $v->model }} — {{ $v->plate_number }}
                            </option>
                            @endforeach
                            @endforeach
                        </select>
                        @error('vehicle_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Assigned Technician</label>
                        <select name="staff_id" class="input w-full">
                            <option value="">Unassigned</option>
                            @foreach($staff as $s)
                            <option value="{{ $s->id }}" @selected(old('staff_id') == $s->id)>{{ $s->user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                        <select name="priority" class="input w-full">
                            @foreach(['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent'] as $val => $lbl)
                            <option value="{{ $val }}" @selected(old('priority', 'normal') === $val)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Promised Date</label>
                        <input type="date" name="promised_at" value="{{ old('promised_at') }}" class="input w-full">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mileage In (km)</label>
                        <input type="number" name="mileage_in" value="{{ old('mileage_in') }}" min="0" class="input w-full" placeholder="Current odometer">
                    </div>

                </div>
            </div>

            {{-- Complaint & Diagnosis --}}
            <div class="card p-5">
                <h3 class="font-semibold text-gray-900 mb-4">Work Details</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Customer Complaint</label>
                        <textarea name="complaint" rows="3" class="input w-full"
                            placeholder="Describe the customer's complaint…">{{ old('complaint') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Initial Diagnosis</label>
                        <textarea name="diagnosis" rows="3" class="input w-full"
                            placeholder="Technician's initial diagnosis…">{{ old('diagnosis') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Line Items --}}
            <div class="card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-900">Service Items</h3>
                    <button type="button" @click="addItem()" class="btn-secondary btn-sm">+ Add Item</button>
                </div>

                <div class="space-y-3">
                    <template x-for="(item, idx) in items" :key="idx">
                        <div class="grid grid-cols-12 gap-2 items-end">
                            <div class="col-span-5">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                                <input type="text" :name="`items[${idx}][description]`" x-model="item.description"
                                    required class="input w-full text-sm" placeholder="Service description">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                                <select :name="`items[${idx}][type]`" x-model="item.type" class="input w-full text-sm">
                                    <option value="labour">Labour</option>
                                    <option value="part">Part</option>
                                    <option value="service">Service</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Qty</label>
                                <input type="number" :name="`items[${idx}][quantity]`" x-model="item.qty"
                                    min="0.01" step="0.01" required class="input w-full text-sm">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Unit (OMR)</label>
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
                        <p class="text-sm text-gray-400 text-center py-4">No items added yet. Click "Add Item" to start.</p>
                    </template>
                </div>

                {{-- Labour Cost + Total --}}
                <div class="mt-5 pt-4 border-t border-gray-100">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Labour Cost (OMR)</label>
                            <input type="number" name="labour_cost" x-model="labourCost"
                                min="0" step="0.001" value="{{ old('labour_cost', 0) }}"
                                class="input w-36 text-sm">
                        </div>
                        <div class="text-end">
                            <p class="text-xs text-gray-400">Estimated Total</p>
                            <p class="text-xl font-bold text-gray-900">
                                <span x-text="grandTotal.toFixed(3)">0.000</span> OMR
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="btn-primary">Create Job Order</button>
                <a href="{{ route('jobs.index') }}" class="btn-secondary">Cancel</a>
            </div>

        </form>
    </div>

</x-layouts.app>

@push('scripts')
<script>
// Eager-load vehicles per customer into the vehicle select
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
