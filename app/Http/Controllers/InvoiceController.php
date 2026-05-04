<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Invoice;
use App\Models\JobOrder;

class InvoiceController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
    {
        $invoices = Invoice::with(['customer', 'jobOrder'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->search, fn ($q, $s) => $q
                ->where('invoice_number', 'like', "%{$s}%")
                ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$s}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total_outstanding' => Invoice::whereIn('status', ['sent', 'partial'])->sum('total')
                                 - Invoice::whereIn('status', ['sent', 'partial'])->sum('amount_paid'),

            'total_paid_month'  => Invoice::where('status', 'paid')
                ->whereMonth('updated_at', now()->month)
                ->sum('total'),

            'overdue_count' => Invoice::where('due_at', '<', today())
                ->whereIn('status', ['sent', 'partial'])
                ->count(),
        ];

        return view('invoices.index', compact('invoices', 'summary'));
    }

    public function show(Invoice $invoice): \Illuminate\View\View
    {
        $invoice->load(['customer', 'jobOrder.items', 'jobOrder.parts.part', 'payments.receivedBy']);

        return view('invoices.show', compact('invoice'));
    }

    public function generateFromJob(JobOrder $job): RedirectResponse
    {
        if ($job->invoice) {
            return redirect()->route('invoices.show', $job->invoice);
        }

        $job->load('items');

        $itemsSum  = $job->items->sum(fn ($i) => (float) $i->quantity * (float) $i->unit_price);
        $subtotal  = round((float) $job->labour_cost + (float) $job->parts_cost + $itemsSum, 3);
        $discount  = (float) $job->discount;
        $taxAmount = round(($subtotal - $discount) * ((float) $job->tax_rate / 100), 3);
        $total     = round($subtotal - $discount + $taxAmount, 3);

        $invoice = Invoice::create([
            'job_order_id' => $job->id,
            'customer_id'  => $job->customer_id,
            'subtotal'     => $subtotal,
            'discount'     => $discount,
            'tax_amount'   => $taxAmount,
            'total'        => $total,
            'amount_paid'  => 0,
            'status'       => 'draft',
            'issued_at'    => today(),
            'due_at'       => today()->addDays(7),
        ]);

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', __('app.invoices.generated', ['number' => $invoice->invoice_number]));
    }

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $data = $request->validate([
            'status'    => 'required|in:draft,sent,partial,paid,overdue',
            'issued_at' => 'required|date',
            'due_at'    => 'nullable|date',
            'discount'  => 'nullable|numeric|min:0',
            'notes'     => 'nullable|string',
        ]);

        // Recalculate total when discount changes
        if (isset($data['discount']) && $invoice->jobOrder) {
            $invoice->load('jobOrder.items');
            $itemsSum  = $invoice->jobOrder->items->sum(fn ($i) => (float)$i->quantity * (float)$i->unit_price);
            $subtotal  = round((float)$invoice->jobOrder->labour_cost + (float)$invoice->jobOrder->parts_cost + $itemsSum, 3);
            $discount  = (float)$data['discount'];
            $taxAmount = round(($subtotal - $discount) * ((float)$invoice->jobOrder->tax_rate / 100), 3);
            $data['subtotal']   = $subtotal;
            $data['tax_amount'] = $taxAmount;
            $data['total']      = round($subtotal - $discount + $taxAmount, 3);
        }

        $invoice->update($data);

        return back()->with('success', 'Invoice updated.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $invoice->payments()->delete();
        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'Invoice deleted.');
    }

    public function pdf(Invoice $invoice)
    {
        $invoice->load(['customer', 'jobOrder.items', 'jobOrder.parts.part', 'payments.receivedBy']);

        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'))
            ->setPaper('a4', 'portrait');

        $filename = $invoice->invoice_number . '.pdf';

        return request()->boolean('inline')
            ? $pdf->stream($filename)
            : $pdf->download($filename);
    }

    public function recordPayment(Request $request, Invoice $invoice): RedirectResponse
    {
        $data = $request->validate([
            'amount'    => 'required|numeric|min:0.01|max:' . $invoice->balance_due,
            'method'    => 'required|in:cash,card,bank_transfer,cheque,online',
            'reference' => 'nullable|string|max:100',
            'notes'     => 'nullable|string',
        ]);

        $invoice->payments()->create([
            ...$data,
            'paid_at'     => now(),
            'received_by' => auth()->id(),
        ]);

        $invoice->increment('amount_paid', $data['amount']);

        $newStatus = $invoice->fresh()->amount_paid >= $invoice->total ? 'paid' : 'partial';
        $invoice->update(['status' => $newStatus]);

        return back()->with('success', __('app.invoices.payment_saved'));
    }
}
