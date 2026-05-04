<x-layouts.app title="{{ __('Invoice') }} {{ $invoice->invoice_number }}">

    @php
        $itemsTotal  = $invoice->jobOrder?->items->sum(fn ($i) => (float)$i->quantity * (float)$i->unit_price) ?? 0;
        $partsTotal  = $invoice->jobOrder?->parts->sum(fn ($p) => (float)$p->quantity * (float)$p->unit_price) ?? 0;
        $labourCost  = (float)($invoice->jobOrder?->labour_cost ?? 0);
        $calcSubtotal = round($labourCost + $itemsTotal + $partsTotal, 3);
        $calcDiscount = (float)$invoice->discount;
        $taxRate      = (float)($invoice->jobOrder?->tax_rate ?? 0);
        $calcTax      = round(($calcSubtotal - $calcDiscount) * ($taxRate / 100), 3);
        $calcTotal    = round($calcSubtotal - $calcDiscount + $calcTax, 3);
        $calcBalance  = round($calcTotal - (float)$invoice->amount_paid, 3);

        // WhatsApp deep link
        $waPhone = preg_replace('/[^0-9]/', '', $invoice->customer?->phone ?? '');
        if (str_starts_with($waPhone, '00')) $waPhone = substr($waPhone, 2);
        if (strlen($waPhone) === 8) $waPhone = '968' . $waPhone; // local Omani number
        $waVehicle = $invoice->jobOrder?->vehicle;
        $waCustomer = $invoice->customer?->name ?? 'Customer';
        $waLines = [
            'Tsunami Garage',
            '',
            'Dear ' . $waCustomer . ',',
            'Your invoice is ready.',
            '',
            'Invoice : ' . $invoice->invoice_number,
            'Date    : ' . ($invoice->issued_at?->format('d M Y') ?? ''),
        ];
        if ($waVehicle) {
            $waLines[] = 'Vehicle : ' . trim("{$waVehicle->make} {$waVehicle->model}") . ($waVehicle->plate_number ? " ({$waVehicle->plate_number})" : '');
        }
        $waLines[] = '';
        if ($calcDiscount > 0) $waLines[] = 'Subtotal: ' . number_format($calcSubtotal, 3) . ' OMR';
        if ($calcDiscount > 0) $waLines[] = 'Discount: -' . number_format($calcDiscount, 3) . ' OMR';
        if ($calcTax > 0)      $waLines[] = 'Tax (' . number_format($taxRate, 1) . '%): ' . number_format($calcTax, 3) . ' OMR';
        $waLines[] = 'Total   : ' . number_format($calcTotal, 3) . ' OMR';
        $waLines[] = $calcBalance > 0 ? 'Balance : ' . number_format($calcBalance, 3) . ' OMR' : 'Status  : Paid in full';
        $waLines[] = '';
        $waLines[] = 'Thank you for choosing Tsunami Garage!';
        // Arabic section
        $waLines[] = '';
        $waLines[] = '─────────────────';
        $waLines[] = '';
        $waLines[] = 'تسونامي جراج';
        $waLines[] = '';
        $waLines[] = 'عزيزي ' . $waCustomer . '،';
        $waLines[] = 'فاتورتك جاهزة.';
        $waLines[] = '';
        $waLines[] = 'رقم الفاتورة : ' . $invoice->invoice_number;
        $waLines[] = 'التاريخ      : ' . ($invoice->issued_at?->format('d M Y') ?? '');
        if ($waVehicle) {
            $waLines[] = 'المركبة      : ' . trim("{$waVehicle->make} {$waVehicle->model}") . ($waVehicle->plate_number ? " ({$waVehicle->plate_number})" : '');
        }
        $waLines[] = '';
        if ($calcDiscount > 0) $waLines[] = 'المجموع الفرعي : ' . number_format($calcSubtotal, 3) . ' ريال عماني';
        if ($calcDiscount > 0) $waLines[] = 'الخصم          : -' . number_format($calcDiscount, 3) . ' ريال عماني';
        if ($calcTax > 0)      $waLines[] = 'الضريبة (' . number_format($taxRate, 1) . '%): ' . number_format($calcTax, 3) . ' ريال عماني';
        $waLines[] = 'الإجمالي     : ' . number_format($calcTotal, 3) . ' ريال عماني';
        $waLines[] = $calcBalance > 0 ? 'المبلغ المتبقي : ' . number_format($calcBalance, 3) . ' ريال عماني' : 'الحالة        : مدفوعة بالكامل';
        $waLines[] = '';
        $waLines[] = 'شكراً لاختيارك تسونامي جراج!';
        $waUrl = 'https://api.whatsapp.com/send?phone=' . $waPhone . '&text=' . rawurlencode(implode("\n", $waLines));
    @endphp

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('invoices.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-lg font-semibold text-gray-900 font-mono">{{ $invoice->invoice_number }}</h2>
                    @include('components.status-badge', ['status' => $invoice->status])
                </div>
                <p class="text-sm text-gray-400 mt-0.5">
                    {{ __('Issued') }} {{ $invoice->issued_at?->format('d M Y') }}
                    @if($invoice->due_at)
                    · {{ __('Due') }} {{ $invoice->due_at->format('d M Y') }}
                    @endif
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" x-data
                class="btn-secondary"
                @click="$dispatch('open-modal', 'edit-invoice')">
                {{ __('Edit') }}
            </button>
            <a href="{{ route('invoices.pdf', ['invoice' => $invoice, 'inline' => 1]) }}"
               target="_blank"
               class="btn-secondary flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                {{ __('Print') }}
            </a>
            <a href="{{ route('invoices.pdf', $invoice) }}"
               class="btn-secondary flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                {{ __('Download PDF') }}
            </a>
            <a href="{{ $waUrl }}" target="_blank" rel="noopener"
               class="btn-secondary flex items-center gap-1.5 !text-green-600 !border-green-300 hover:!bg-green-50">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                {{ __('WhatsApp') }}
            </a>
            @if($invoice->status !== 'paid')
            <button type="button" class="btn-primary"
                x-data @click="$dispatch('open-modal', 'record-payment')">
                {{ __('Record Payment') }}
            </button>
            @endif
            <button type="button" x-data
                class="btn-secondary text-red-500 hover:text-red-700 hover:border-red-300"
                @click="$dispatch('open-confirm-delete', '{{ route('invoices.destroy', $invoice) }}')">
                {{ __('Delete') }}
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-6">

        {{-- Left column --}}
        <div class="xl:col-span-2 space-y-4 sm:space-y-6">

            {{-- Customer & Job info --}}
            <div class="card p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">{{ __('Customer') }}</h3>
                    <p class="font-semibold text-gray-900">{{ $invoice->customer?->name }}</p>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $invoice->customer?->phone }}</p>
                    @if($invoice->customer?->email)
                    <p class="text-sm text-gray-400">{{ $invoice->customer->email }}</p>
                    @endif
                </div>
                <div>
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">{{ __('Job Order') }}</h3>
                    @if($invoice->jobOrder)
                    <a href="{{ route('jobs.show', $invoice->jobOrder) }}"
                        class="font-mono font-semibold text-orange-500 hover:underline">
                        {{ $invoice->jobOrder->job_number }}
                    </a>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ $invoice->jobOrder->vehicle?->make }} {{ $invoice->jobOrder->vehicle?->model }}
                        @if($invoice->jobOrder->vehicle?->plate_number)
                        · <span class="font-mono">{{ $invoice->jobOrder->vehicle->plate_number }}</span>
                        @endif
                    </p>
                    @else
                    <p class="text-sm text-gray-400">—</p>
                    @endif
                </div>
            </div>

            {{-- Line Items --}}
            @if($invoice->jobOrder?->items?->isNotEmpty())
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-gray-900">{{ __('Service Line Items') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>{{ __('Description') }}</th><th>{{ __('Type') }}</th><th class="text-center">{{ __('Qty') }}</th><th class="text-end">{{ __('Unit (OMR)') }}</th><th class="text-end">{{ __('Total (OMR)') }}</th></tr></thead>
                        <tbody>
                            @foreach($invoice->jobOrder->items as $item)
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
            @if($invoice->jobOrder?->parts?->isNotEmpty())
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-gray-900">{{ __('Parts Used') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>{{ __('Part') }}</th><th>{{ __('SKU') }}</th><th class="text-center">{{ __('Qty') }}</th><th class="text-end">{{ __('Unit (OMR)') }}</th><th class="text-end">{{ __('Total (OMR)') }}</th></tr></thead>
                        <tbody>
                            @foreach($invoice->jobOrder->parts as $jp)
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
            </div>
            @endif

            {{-- Payment History --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-gray-900">{{ __('Payment History') }}</h3>
                </div>
                @if($invoice->payments?->isEmpty())
                <p class="px-5 py-8 text-center text-sm text-gray-400">{{ __('No payments recorded yet.') }}</p>
                @else
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Method') }}</th><th>{{ __('Reference') }}</th><th>{{ __('Received By') }}</th><th class="text-end">{{ __('Amount (OMR)') }}</th></tr></thead>
                        <tbody>
                            @foreach($invoice->payments as $pmt)
                            <tr>
                                <td class="text-sm text-gray-700">{{ $pmt->paid_at?->format('d M Y H:i') }}</td>
                                <td class="capitalize text-gray-600 text-sm">{{ str_replace('_', ' ', $pmt->method) }}</td>
                                <td class="text-gray-500 text-xs font-mono">{{ $pmt->reference ?? '—' }}</td>
                                <td class="text-gray-500 text-sm">{{ $pmt->receivedBy?->name ?? '—' }}</td>
                                <td class="text-end font-bold text-green-600">{{ number_format($pmt->amount, 3) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

        {{-- Right: totals --}}
        <div class="space-y-4">
            <div class="card p-5">
                <h3 class="font-semibold text-gray-900 mb-4">{{ __('Summary') }}</h3>
                <div class="space-y-2 text-sm">
                    @if($labourCost > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('Labour') }}</span>
                        <span class="font-medium text-gray-900">{{ number_format($labourCost, 3) }} OMR</span>
                    </div>
                    @endif
                    @if($itemsTotal > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('Services') }}</span>
                        <span class="font-medium text-gray-900">{{ number_format($itemsTotal, 3) }} OMR</span>
                    </div>
                    @endif
                    @if($partsTotal > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('Parts') }}</span>
                        <span class="font-medium text-gray-900">{{ number_format($partsTotal, 3) }} OMR</span>
                    </div>
                    @endif
                    <div class="flex justify-between border-t border-gray-100 pt-2">
                        <span class="text-gray-500">{{ __('Subtotal') }}</span>
                        <span class="font-medium text-gray-900">{{ number_format($calcSubtotal, 3) }} OMR</span>
                    </div>
                    @if($calcDiscount > 0)
                    <div class="flex justify-between text-red-600">
                        <span>{{ __('Discount') }}</span>
                        <span>-{{ number_format($calcDiscount, 3) }} OMR</span>
                    </div>
                    @endif
                    @if($calcTax > 0)
                    <div class="flex justify-between text-gray-700">
                        <span>{{ __('Tax') }} ({{ number_format($taxRate, 1) }}%)</span>
                        <span>{{ number_format($calcTax, 3) }} OMR</span>
                    </div>
                    @endif
                    <div class="border-t border-gray-100 pt-2 flex justify-between text-base font-bold text-gray-900">
                        <span>{{ __('Total') }}</span>
                        <span>{{ number_format($calcTotal, 3) }} OMR</span>
                    </div>
                    <div class="flex justify-between text-green-600 font-medium">
                        <span>{{ __('Amount Paid') }}</span>
                        <span>{{ number_format($invoice->amount_paid, 3) }} OMR</span>
                    </div>
                    <div class="border-t border-gray-100 pt-2 flex justify-between text-base font-bold {{ $calcBalance > 0 ? 'text-red-600' : 'text-gray-400' }}">
                        <span>{{ __('Balance Due') }}</span>
                        <span>{{ number_format($calcBalance, 3) }} OMR</span>
                    </div>
                </div>
            </div>

            @if($invoice->status !== 'paid' && $calcBalance > 0)
            <div class="card p-4 bg-orange-50 border-orange-200">
                <p class="text-xs text-orange-700 font-medium">{{ number_format($calcBalance, 3) }} OMR {{ __('remaining') }}</p>
                @if($invoice->due_at && $invoice->due_at->isPast())
                <p class="text-xs text-red-600 mt-1">{{ __('Overdue since') }} {{ $invoice->due_at->format('d M Y') }}</p>
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- Record Payment Modal --}}
    @push('modals')
    <div x-data="{ open: false }"
        @open-modal.window="if ($event.detail === 'record-payment') open = true"
        x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        @keydown.escape.window="open = false">

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6" @click.stop>
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-semibold text-gray-900">{{ __('Record Payment') }}</h3>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('invoices.record-payment', $invoice) }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Amount (OMR)') }} <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" required step="0.001" min="0.001"
                        max="{{ $calcBalance }}"
                        value="{{ number_format($calcBalance, 3, '.', '') }}"
                        class="input w-full">
                    <p class="text-xs text-gray-400 mt-1">{{ __('Max') }}: {{ number_format($calcBalance, 3) }} OMR</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Payment Method') }} <span class="text-red-500">*</span></label>
                    <select name="method" required class="input w-full">
                        <option value="cash">{{ __('Cash') }}</option>
                        <option value="card">{{ __('Card') }}</option>
                        <option value="bank_transfer">{{ __('Bank Transfer') }}</option>
                        <option value="cheque">{{ __('Cheque') }}</option>
                        <option value="online">{{ __('Online') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Reference') }}</label>
                    <input type="text" name="reference" class="input w-full" placeholder="{{ __('Transaction ID, cheque #…') }}">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Notes') }}</label>
                    <textarea name="notes" rows="2" class="input w-full" placeholder="{{ __('Optional notes…') }}"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary flex-1">{{ __('Save Payment') }}</button>
                    <button type="button" @click="open = false" class="btn-secondary flex-1">{{ __('Cancel') }}</button>
                </div>
            </form>
        </div>
    </div>
    {{-- Edit Invoice Modal --}}
    <div x-data="{ open: false }"
        @open-modal.window="if ($event.detail === 'edit-invoice') open = true"
        x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        @keydown.escape.window="open = false">

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6" @click.stop>
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-semibold text-gray-900">{{ __('Edit Invoice') }}</h3>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('invoices.update', $invoice) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Status') }}</label>
                        <select name="status" required class="input w-full">
                            @foreach(['draft' => __('Draft'), 'sent' => __('Sent'), 'partial' => __('Partial'), 'paid' => __('Paid'), 'overdue' => __('Overdue')] as $val => $lbl)
                            <option value="{{ $val }}" @selected($invoice->status === $val)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Discount (OMR)') }}</label>
                        <input type="number" name="discount" step="0.001" min="0"
                            value="{{ number_format($invoice->discount, 3, '.', '') }}"
                            class="input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Issue Date') }}</label>
                        <input type="date" name="issued_at" required
                            value="{{ $invoice->issued_at?->format('Y-m-d') }}"
                            class="input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Due Date') }}</label>
                        <input type="date" name="due_at"
                            value="{{ $invoice->due_at?->format('Y-m-d') }}"
                            class="input w-full">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Notes') }}</label>
                    <textarea name="notes" rows="3" class="input w-full"
                        placeholder="{{ __('Internal notes…') }}">{{ $invoice->notes }}</textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary flex-1">{{ __('Save Changes') }}</button>
                    <button type="button" @click="open = false" class="btn-secondary flex-1">{{ __('Cancel') }}</button>
                </div>
            </form>
        </div>
    </div>
    @endpush

</x-layouts.app>
