<x-layouts.app title="{{ __('Invoices') }}">

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="card p-5">
            <p class="text-xs text-gray-400 font-medium mb-1">{{ __('Outstanding Balance') }}</p>
            <p class="text-2xl font-bold text-red-600">{{ number_format($summary['total_outstanding'], 3) }} <span class="text-sm font-normal text-gray-400">OMR</span></p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-gray-400 font-medium mb-1">{{ __('Paid This Month') }}</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($summary['total_paid_month'], 3) }} <span class="text-sm font-normal text-gray-400">OMR</span></p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-gray-400 font-medium mb-1">{{ __('Overdue Invoices') }}</p>
            <p class="text-2xl font-bold {{ $summary['overdue_count'] > 0 ? 'text-orange-600' : 'text-gray-900' }}">
                {{ $summary['overdue_count'] }}
            </p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('invoices.index') }}" class="flex flex-wrap items-center gap-3 mb-6">
        <div x-data="searchBox()">
            <input type="text" name="search" x-model="query" @input="debounce()"
                placeholder="{{ __('Invoice # or customer…') }}" class="input w-60"
                value="{{ request('search') }}">
        </div>
        <select name="status" onchange="this.form.submit()" class="input w-36">
            <option value="">{{ __('All statuses') }}</option>
            @foreach(['draft' => __('Draft'), 'sent' => __('Sent'), 'partial' => __('Partial'), 'paid' => __('Paid'), 'overdue' => __('Overdue')] as $val => $lbl)
            <option value="{{ $val }}" @selected(request('status') === $val)>{{ $lbl }}</option>
            @endforeach
        </select>
        <a href="{{ route('invoices.index') }}" class="text-xs text-gray-400 hover:text-gray-600">{{ __('Clear') }}</a>
    </form>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="hidden sm:block overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Invoice #') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Job #') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-end">{{ __('Total (OMR)') }}</th>
                        <th class="text-end">{{ __('Paid (OMR)') }}</th>
                        <th class="text-end">{{ __('Balance (OMR)') }}</th>
                        <th>{{ __('Due Date') }}</th>
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
                            @php
                                $wPhone = preg_replace('/[^0-9]/', '', $inv->customer?->phone ?? '');
                                if (str_starts_with($wPhone, '00')) $wPhone = substr($wPhone, 2);
                                if (strlen($wPhone) === 8) $wPhone = '968' . $wPhone;
                                $wCustomer = $inv->customer?->name ?? 'Customer';
                                $wText = implode("\n", [
                                    'Tsunami Garage',
                                    '',
                                    'Dear ' . $wCustomer . ',',
                                    'Invoice : ' . $inv->invoice_number,
                                    'Total   : ' . number_format($inv->total, 3) . ' OMR',
                                    $balance > 0 ? 'Balance : ' . number_format($balance, 3) . ' OMR' : 'Status  : Paid in full',
                                    '',
                                    'Thank you for choosing Tsunami Garage!',
                                    '',
                                    '─────────────────',
                                    '',
                                    'تسونامي جراج',
                                    '',
                                    'عزيزي ' . $wCustomer . '،',
                                    'رقم الفاتورة : ' . $inv->invoice_number,
                                    'الإجمالي     : ' . number_format($inv->total, 3) . ' ريال عماني',
                                    $balance > 0 ? 'المبلغ المتبقي : ' . number_format($balance, 3) . ' ريال عماني' : 'الحالة : مدفوعة بالكامل',
                                    '',
                                    'شكراً لاختيارك تسونامي جراج!',
                                ]);
                                $wUrl = 'https://api.whatsapp.com/send?phone=' . $wPhone . '&text=' . rawurlencode($wText);
                            @endphp
                            <div class="flex items-center gap-3" onclick="event.stopPropagation()">
                                <a href="{{ $wUrl }}" target="_blank" rel="noopener"
                                   title="{{ __('Send via WhatsApp') }}"
                                   class="text-green-500 hover:text-green-700 transition">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('invoices.show', $inv) }}" class="text-xs text-orange-500 hover:underline">{{ __('View') }} →</a>
                                <button type="button" class="text-xs text-red-400 hover:text-red-600 transition"
                                    @click.stop="$dispatch('open-confirm-delete', '{{ route('invoices.destroy', $inv) }}')">
                                    {{ __('Delete') }}
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-12 text-center text-sm text-gray-400">{{ __('No invoices found.') }}</td>
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
                    <p class="text-xs text-gray-400 mt-0.5">{{ __('Due') }} {{ $inv->due_at?->format('d M Y') ?? '—' }}</p>
                </div>
                <div class="text-end shrink-0">
                    @include('components.status-badge', ['status' => $inv->status])
                    <p class="font-bold text-gray-900 mt-1">{{ number_format($inv->total, 3) }} OMR</p>
                    @if($balance > 0)
                    <p class="text-xs text-red-600">{{ __('Balance') }}: {{ number_format($balance, 3) }}</p>
                    @endif
                </div>
            </a>
            @empty
            <p class="py-12 text-center text-sm text-gray-400">{{ __('No invoices found.') }}</p>
            @endforelse
        </div>
    </div>

    @include('components.pagination', ['paginator' => $invoices])

</x-layouts.app>
