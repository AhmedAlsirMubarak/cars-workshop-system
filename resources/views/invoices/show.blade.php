<x-layouts.app title="Invoice {{ $invoice->invoice_number }}">

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
                    Issued {{ $invoice->issued_at?->format('d M Y') }}
                    @if($invoice->due_at)
                    · Due {{ $invoice->due_at->format('d M Y') }}
                    @endif
                </p>
            </div>
        </div>
        @if(!in_array($invoice->status, ['paid']))
        <button type="button" class="btn-primary"
            x-data @click="$dispatch('open-modal', 'record-payment')">
            Record Payment
        </button>
        @endif
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-6">

        {{-- Left column --}}
        <div class="xl:col-span-2 space-y-4 sm:space-y-6">

            {{-- Customer & Job info --}}
            <div class="card p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Customer</h3>
                    <p class="font-semibold text-gray-900">{{ $invoice->customer?->name }}</p>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $invoice->customer?->phone }}</p>
                    @if($invoice->customer?->email)
                    <p class="text-sm text-gray-400">{{ $invoice->customer->email }}</p>
                    @endif
                </div>
                <div>
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Job Order</h3>
                    @if($invoice->jobOrder)
                    <a href="{{ route('jobs.show', $invoice->jobOrder) }}"
                        class="font-mono font-semibold text-orange-500 hover:underline">
                        {{ $invoice->jobOrder->job_number }}
                    </a>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ $invoice->jobOrder->vehicle?->make }} {{ $invoice->jobOrder->vehicle?->make }}
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
                    <h3 class="font-semibold text-gray-900">Service Line Items</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Description</th><th>Type</th><th class="text-center">Qty</th><th class="text-end">Unit (OMR)</th><th class="text-end">Total (OMR)</th></tr></thead>
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
                    <h3 class="font-semibold text-gray-900">Parts Used</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Part</th><th>SKU</th><th class="text-center">Qty</th><th class="text-end">Unit (OMR)</th><th class="text-end">Total (OMR)</th></tr></thead>
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
                    <h3 class="font-semibold text-gray-900">Payment History</h3>
                </div>
                @if($invoice->payments?->isEmpty())
                <p class="px-5 py-8 text-center text-sm text-gray-400">No payments recorded yet.</p>
                @else
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Date</th><th>Method</th><th>Reference</th><th>Received By</th><th class="text-end">Amount (OMR)</th></tr></thead>
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
                <h3 class="font-semibold text-gray-900 mb-4">Summary</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Subtotal</span>
                        <span class="font-medium text-gray-900">{{ number_format($invoice->subtotal, 3) }} OMR</span>
                    </div>
                    @if($invoice->discount > 0)
                    <div class="flex justify-between text-red-600">
                        <span>Discount</span>
                        <span>-{{ number_format($invoice->discount, 3) }} OMR</span>
                    </div>
                    @endif
                    @if(isset($invoice->tax_amount) && $invoice->tax_amount > 0)
                    <div class="flex justify-between text-gray-700">
                        <span>Tax</span>
                        <span>{{ number_format($invoice->tax_amount, 3) }} OMR</span>
                    </div>
                    @endif
                    <div class="border-t border-gray-100 pt-2 flex justify-between text-base font-bold text-gray-900">
                        <span>Total</span>
                        <span>{{ number_format($invoice->total, 3) }} OMR</span>
                    </div>
                    <div class="flex justify-between text-green-600 font-medium">
                        <span>Amount Paid</span>
                        <span>{{ number_format($invoice->amount_paid, 3) }} OMR</span>
                    </div>
                    @php $balance = $invoice->total - $invoice->amount_paid; @endphp
                    <div class="border-t border-gray-100 pt-2 flex justify-between text-base font-bold {{ $balance > 0 ? 'text-red-600' : 'text-gray-400' }}">
                        <span>Balance Due</span>
                        <span>{{ number_format($balance, 3) }} OMR</span>
                    </div>
                </div>
            </div>

            @if($invoice->status !== 'paid' && $balance > 0)
            <div class="card p-4 bg-orange-50 border-orange-200">
                <p class="text-xs text-orange-700 font-medium">{{ number_format($balance, 3) }} OMR remaining</p>
                @if($invoice->due_at && $invoice->due_at->isPast())
                <p class="text-xs text-red-600 mt-1">Overdue since {{ $invoice->due_at->format('d M Y') }}</p>
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
                <h3 class="font-semibold text-gray-900">Record Payment</h3>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('invoices.record-payment', $invoice) }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount (OMR) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" required step="0.001" min="0.001"
                        max="{{ $invoice->total - $invoice->amount_paid }}"
                        value="{{ number_format($invoice->total - $invoice->amount_paid, 3, '.', '') }}"
                        class="input w-full">
                    <p class="text-xs text-gray-400 mt-1">Max: {{ number_format($invoice->total - $invoice->amount_paid, 3) }} OMR</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method <span class="text-red-500">*</span></label>
                    <select name="method" required class="input w-full">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cheque">Cheque</option>
                        <option value="online">Online</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reference</label>
                    <input type="text" name="reference" class="input w-full" placeholder="Transaction ID, cheque #…">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="input w-full" placeholder="Optional notes…"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary flex-1">Save Payment</button>
                    <button type="button" @click="open = false" class="btn-secondary flex-1">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    @endpush

</x-layouts.app>
