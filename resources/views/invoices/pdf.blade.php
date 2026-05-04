<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>{{ $invoice->invoice_number }}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #1f2937; background: #fff; }
.page { padding: 40px 48px; }

/* Header */
.header { margin-bottom: 32px; overflow: hidden; }
.header .brand { float: left; }
.header .invoice-meta { float: right; text-align: right; }
.header::after { content: ''; display: table; clear: both; }
.brand-name { font-size: 22px; font-weight: 700; color: #111827; }
.brand-tagline { font-size: 11px; color: #6b7280; margin-top: 2px; }

.invoice-number { font-size: 18px; font-weight: 700; color: #ea580c; }
.invoice-meta p { color: #6b7280; font-size: 11px; margin-top: 3px; }
.invoice-meta strong { color: #111827; }

/* Divider */
.divider { border: none; border-top: 2px solid #f97316; margin: 20px 0; }

/* Billing info */
.billing { overflow: hidden; margin-bottom: 24px; }
.billing .col { float: left; width: 50%; }
.billing::after { content: ''; display: table; clear: both; }
.section-label { font-size: 10px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; }
.billing p { font-size: 12px; color: #374151; margin-top: 2px; }
.billing .name { font-weight: 700; font-size: 13px; color: #111827; }

/* Status badge */
.badge { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
.badge-draft    { background: #f3f4f6; color: #6b7280; }
.badge-sent     { background: #dbeafe; color: #1d4ed8; }
.badge-partial  { background: #fef3c7; color: #92400e; }
.badge-paid     { background: #d1fae5; color: #065f46; }
.badge-overdue  { background: #fee2e2; color: #991b1b; }

/* Tables */
table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
th { background: #f9fafb; padding: 8px 10px; text-align: left; font-size: 10px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #e5e7eb; }
td { padding: 8px 10px; font-size: 12px; color: #374151; border-bottom: 1px solid #f3f4f6; }
th.right, td.right { text-align: right; }
th.center, td.center { text-align: center; }
.table-section-title { font-size: 11px; font-weight: 700; color: #111827; margin-bottom: 6px; }

/* Totals */
.totals-wrap { overflow: hidden; margin-top: 8px; }
.totals-table { float: right; width: 280px; }
.totals-table::after { content: ''; display: table; clear: both; }
.totals-table td { border-bottom: none; padding: 4px 10px; }
.totals-table .total-row td { border-top: 2px solid #e5e7eb; font-weight: 700; font-size: 13px; color: #111827; padding-top: 8px; }
.totals-table .balance-row td { border-top: 1px solid #e5e7eb; font-weight: 700; font-size: 13px; padding-top: 8px; }
.totals-table .balance-row.due td { color: #dc2626; }
.totals-table .balance-row.clear td { color: #9ca3af; }
.totals-table .paid-row td { color: #059669; font-weight: 600; }
.totals-wrap::after { content: ''; display: table; clear: both; }

/* Payment history */
.section-title { font-size: 11px; font-weight: 700; color: #111827; margin: 20px 0 6px; }

/* Footer */
.footer { margin-top: 40px; border-top: 1px solid #e5e7eb; padding-top: 16px; text-align: center; font-size: 10px; color: #9ca3af; }
</style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="header">
        <div class="brand">
            <div class="brand-name">Tsunami Garage</div>
            <div class="brand-tagline">Professional Auto Workshop</div>
        </div>
        <div class="invoice-meta">
            <div class="invoice-number">{{ $invoice->invoice_number }}</div>
            @php
                $statusClass = match($invoice->status) {
                    'draft'   => 'badge-draft',
                    'sent'    => 'badge-sent',
                    'partial' => 'badge-partial',
                    'paid'    => 'badge-paid',
                    default   => 'badge-overdue',
                };
            @endphp
            <span class="badge {{ $statusClass }}">{{ ucfirst($invoice->status) }}</span>
            <p>Issued: <strong>{{ $invoice->issued_at?->format('d M Y') ?? '—' }}</strong></p>
            @if($invoice->due_at)
            <p>Due: <strong>{{ $invoice->due_at->format('d M Y') }}</strong></p>
            @endif
        </div>
    </div>

    <hr class="divider">

    {{-- Billing info --}}
    <div class="billing">
        <div class="col">
            <p class="section-label">Bill To</p>
            <p class="name">{{ $invoice->customer?->name }}</p>
            @if($invoice->customer?->phone)
            <p>{{ $invoice->customer->phone }}</p>
            @endif
            @if($invoice->customer?->email)
            <p>{{ $invoice->customer->email }}</p>
            @endif
        </div>
        @if($invoice->jobOrder)
        <div class="col">
            <p class="section-label">Job Order</p>
            <p class="name">{{ $invoice->jobOrder->job_number }}</p>
            @if($invoice->jobOrder->vehicle)
            <p>{{ $invoice->jobOrder->vehicle->make }} {{ $invoice->jobOrder->vehicle->model }}</p>
            @if($invoice->jobOrder->vehicle->plate_number)
            <p>{{ $invoice->jobOrder->vehicle->plate_number }}</p>
            @endif
            @endif
        </div>
        @endif
    </div>

    @php
        $itemsTotal   = $invoice->jobOrder?->items->sum(fn ($i) => (float)$i->quantity * (float)$i->unit_price) ?? 0;
        $partsTotal   = $invoice->jobOrder?->parts->sum(fn ($p) => (float)$p->quantity * (float)$p->unit_price) ?? 0;
        $labourCost   = (float)($invoice->jobOrder?->labour_cost ?? 0);
        $subtotal     = round($labourCost + $itemsTotal + $partsTotal, 3);
        $discount     = (float)$invoice->discount;
        $taxRate      = (float)($invoice->jobOrder?->tax_rate ?? 0);
        $taxAmount    = round(($subtotal - $discount) * ($taxRate / 100), 3);
        $total        = round($subtotal - $discount + $taxAmount, 3);
        $amountPaid   = (float)$invoice->amount_paid;
        $balance      = round($total - $amountPaid, 3);
    @endphp

    {{-- Service Items --}}
    @if($invoice->jobOrder?->items?->isNotEmpty())
    <p class="table-section-title">Service Items</p>
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Type</th>
                <th class="center">Qty</th>
                <th class="right">Unit (OMR)</th>
                <th class="right">Total (OMR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->jobOrder->items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td style="text-transform:capitalize;color:#6b7280">{{ $item->type }}</td>
                <td class="center">{{ $item->quantity }}</td>
                <td class="right">{{ number_format($item->unit_price, 3) }}</td>
                <td class="right" style="font-weight:600">{{ number_format($item->quantity * $item->unit_price, 3) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Parts --}}
    @if($invoice->jobOrder?->parts?->isNotEmpty())
    <p class="table-section-title">Parts Used</p>
    <table>
        <thead>
            <tr>
                <th>Part</th>
                <th>SKU</th>
                <th class="center">Qty</th>
                <th class="right">Unit (OMR)</th>
                <th class="right">Total (OMR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->jobOrder->parts as $jp)
            <tr>
                <td style="font-weight:600">{{ $jp->part?->name }}</td>
                <td style="font-family:monospace;color:#6b7280;font-size:11px">{{ $jp->part?->sku }}</td>
                <td class="center">{{ $jp->quantity }}</td>
                <td class="right">{{ number_format($jp->unit_price, 3) }}</td>
                <td class="right" style="font-weight:600">{{ number_format($jp->quantity * $jp->unit_price, 3) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Totals --}}
    <div class="totals-wrap">
        <table class="totals-table">
            <tbody>
                @if($labourCost > 0)
                <tr><td style="color:#6b7280">Labour</td><td class="right">{{ number_format($labourCost, 3) }}</td></tr>
                @endif
                @if($itemsTotal > 0)
                <tr><td style="color:#6b7280">Services</td><td class="right">{{ number_format($itemsTotal, 3) }}</td></tr>
                @endif
                @if($partsTotal > 0)
                <tr><td style="color:#6b7280">Parts</td><td class="right">{{ number_format($partsTotal, 3) }}</td></tr>
                @endif
                @if($discount > 0)
                <tr><td style="color:#dc2626">Discount</td><td class="right" style="color:#dc2626">-{{ number_format($discount, 3) }}</td></tr>
                @endif
                @if($taxAmount > 0)
                <tr><td style="color:#6b7280">Tax ({{ number_format($taxRate, 1) }}%)</td><td class="right">{{ number_format($taxAmount, 3) }}</td></tr>
                @endif
                <tr class="total-row">
                    <td>Total (OMR)</td>
                    <td class="right">{{ number_format($total, 3) }}</td>
                </tr>
                @if($amountPaid > 0)
                <tr class="paid-row">
                    <td>Amount Paid</td>
                    <td class="right">{{ number_format($amountPaid, 3) }}</td>
                </tr>
                @endif
                <tr class="balance-row {{ $balance > 0 ? 'due' : 'clear' }}">
                    <td>Balance Due (OMR)</td>
                    <td class="right">{{ number_format($balance, 3) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Payment history --}}
    @if($invoice->payments?->isNotEmpty())
    <p class="section-title">Payment History</p>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Method</th>
                <th>Reference</th>
                <th class="right">Amount (OMR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->payments as $pmt)
            <tr>
                <td>{{ $pmt->paid_at?->format('d M Y') }}</td>
                <td style="text-transform:capitalize">{{ str_replace('_', ' ', $pmt->method) }}</td>
                <td style="font-family:monospace;font-size:11px;color:#6b7280">{{ $pmt->reference ?? '—' }}</td>
                <td class="right" style="font-weight:700;color:#059669">{{ number_format($pmt->amount, 3) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($invoice->notes)
    <p class="section-title">Notes</p>
    <p style="font-size:11px;color:#6b7280;background:#f9fafb;padding:10px;border-radius:4px">{{ $invoice->notes }}</p>
    @endif

    <div class="footer">
        <p>Tsunami Garage &mdash; Thank you for your business!</p>
        <p style="margin-top:4px">Generated on {{ now()->format('d M Y H:i') }}</p>
    </div>
</div>
</body>
</html>
