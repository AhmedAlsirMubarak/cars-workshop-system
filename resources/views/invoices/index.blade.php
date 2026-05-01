<x-layouts.app title="Invoices">

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="card p-5">
            <p class="text-xs text-gray-400 font-medium mb-1">Outstanding Balance</p>
            <p class="text-2xl font-bold text-red-600">{{ number_format($summary['total_outstanding'], 3) }} <span class="text-sm font-normal text-gray-400">OMR</span></p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-gray-400 font-medium mb-1">Paid This Month</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($summary['total_paid_month'], 3) }} <span class="text-sm font-normal text-gray-400">OMR</span></p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-gray-400 font-medium mb-1">Overdue Invoices</p>
            <p class="text-2xl font-bold {{ $summary['overdue_count'] > 0 ? 'text-orange-600' : 'text-gray-900' }}">
                {{ $summary['overdue_count'] }}
            </p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('invoices.index') }}" class="flex flex-wrap items-center gap-3 mb-6">
        <div x-data="searchBox()">
            <input type="text" name="search" x-model="query" @input="debounce()"
                placeholder="Invoice # or customer…" class="input w-60"
                value="{{ request('search') }}">
        </div>
        <select name="status" onchange="this.form.submit()" class="input w-36">
            <option value="">All statuses</option>
            @foreach(['draft' => 'Draft', 'sent' => 'Sent', 'partial' => 'Partial', 'paid' => 'Paid', 'overdue' => 'Overdue'] as $val => $lbl)
            <option value="{{ $val }}" @selected(request('status') === $val)>{{ $lbl }}</option>
            @endforeach
        </select>
        <a href="{{ route('invoices.index') }}" class="text-xs text-gray-400 hover:text-gray-600">Clear</a>
    </form>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="hidden sm:block overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Customer</th>
                        <th>Job #</th>
                        <th>Status</th>
                        <th class="text-end">Total (OMR)</th>
                        <th class="text-end">Paid (OMR)</th>
                        <th class="text-end">Balance (OMR)</th>
                        <th>Due Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                    @php $balance = $inv->total - $inv->amount_paid; $overdue = $inv->due_at && $inv->due_at->isPast() && !in_array($inv->status, ['paid', 'cancelled']); @endphp
                    <tr class="cursor-pointer {{ $overdue ? 'bg-red-50/40' : '' }}" onclick="location='{{ route('invoices.show', $inv) }}'">
                        <td class="font-mono text-xs font-semibold text-orange-500">{{ $inv->invoice_number }}</td>
                        <td class="font-medium text-gray-900">{{ $inv->customer?->name }}</td>
                        <td class="font-mono text-xs text-gray-500">{{ $inv->jobOrder?->job_number }}</td>
                        <td>@include('components.status-badge', ['status' => $inv->status])</td>
                        <td class="text-end font-semibold text-gray-900">{{ number_format($inv->total, 3) }}</td>
                        <td class="text-end text-green-600">{{ number_format($inv->amount_paid, 3) }}</td>
                        <td class="text-end font-bold {{ $balance > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ number_format($balance, 3) }}</td>
                        <td class="text-sm {{ $overdue ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                            {{ $inv->due_at?->format('d M Y') ?? '—' }}
                        </td>
                        <td>
                            <a href="{{ route('invoices.show', $inv) }}" class="text-xs text-orange-500 hover:underline" onclick="event.stopPropagation()">View →</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-12 text-center text-sm text-gray-400">No invoices found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile --}}
        <div class="sm:hidden divide-y divide-gray-50">
            @forelse($invoices as $inv)
            @php $balance = $inv->total - $inv->amount_paid; @endphp
            <a href="{{ route('invoices.show', $inv) }}" class="flex items-start justify-between gap-3 p-4 hover:bg-gray-50 transition">
                <div>
                    <p class="font-mono text-xs font-semibold text-orange-500">{{ $inv->invoice_number }}</p>
                    <p class="font-medium text-gray-900 mt-0.5">{{ $inv->customer?->name }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Due {{ $inv->due_at?->format('d M Y') ?? '—' }}</p>
                </div>
                <div class="text-end shrink-0">
                    @include('components.status-badge', ['status' => $inv->status])
                    <p class="font-bold text-gray-900 mt-1">{{ number_format($inv->total, 3) }} OMR</p>
                    @if($balance > 0)
                    <p class="text-xs text-red-600">Balance: {{ number_format($balance, 3) }}</p>
                    @endif
                </div>
            </a>
            @empty
            <p class="py-12 text-center text-sm text-gray-400">No invoices found.</p>
            @endforelse
        </div>
    </div>

    @include('components.pagination', ['paginator' => $invoices])

</x-layouts.app>
