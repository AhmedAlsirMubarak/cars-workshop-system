<x-layouts.app title="{{ __('Payroll Report') }}">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">{{ __('Payroll Report') }}</h2>
            <p class="text-sm text-gray-500 mt-0.5">{{ __('Annual payroll summary for') }} {{ $year }}</p>
        </div>
        <form method="GET" action="{{ route('payroll.report') }}" class="sm:ms-auto flex items-center gap-2">
            <select name="year" onchange="this.form.submit()" class="input w-28">
                @for($y = date('Y'); $y >= date('Y') - 4; $y--)
                <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                @endfor
            </select>
            <a href="{{ route('payroll.index') }}" class="btn-secondary">{{ __('Payroll') }}</a>
        </form>
    </div>

    {{-- Annual summary cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4 mb-6">
        @foreach([
            ['label' => __('Annual Gross'),      'value' => number_format($annualTotals['gross'], 3).' OMR',      'color' => 'text-gray-900'],
            ['label' => __('Total Deductions'),  'value' => number_format($annualTotals['deductions'], 3).' OMR', 'color' => 'text-red-500'],
            ['label' => __('Annual Net'),        'value' => number_format($annualTotals['net'], 3).' OMR',        'color' => 'text-orange-600'],
            ['label' => __('Via Bank'),          'value' => number_format($annualTotals['bank'], 3).' OMR',       'color' => 'text-blue-600'],
            ['label' => __('Via Cash'),          'value' => number_format($annualTotals['cash'], 3).' OMR',       'color' => 'text-green-600'],
        ] as $kpi)
        <div class="card p-4 sm:p-5">
            <p class="text-xs text-gray-400 mb-1">{{ $kpi['label'] }}</p>
            <p class="text-base sm:text-lg font-bold {{ $kpi['color'] }}">{{ $kpi['value'] }}</p>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 sm:gap-6">

        {{-- Monthly breakdown --}}
        <div class="card overflow-hidden">
            <div class="card-header">
                <h3 class="font-semibold text-gray-900">{{ __('Monthly Breakdown') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr>
                        <th>{{ __('Month') }}</th>
                        <th class="text-end">{{ __('Gross') }}</th>
                        <th class="text-end">{{ __('Deductions') }}</th>
                        <th class="text-end">{{ __('Net') }}</th>
                        <th class="text-center">{{ __('Paid') }}</th>
                    </tr></thead>
                    <tbody>
                        @foreach($monthlyTotals as $row)
                        @php $hasData = $row['count_total'] > 0; @endphp
                        <tr class="{{ !$hasData ? 'opacity-40' : '' }}">
                            <td class="font-medium text-gray-900">
                                <a href="{{ route('payroll.index', ['month' => $row['month'], 'year' => $year]) }}"
                                    class="{{ $hasData ? 'text-orange-500 hover:underline' : 'pointer-events-none' }}">
                                    {{ \Carbon\Carbon::createFromDate($year, $row['month'], 1)->format('F') }}
                                </a>
                            </td>
                            <td class="text-end text-gray-700">{{ $hasData ? number_format($row['total_gross'], 3) : '—' }}</td>
                            <td class="text-end {{ $row['total_deductions'] > 0 ? 'text-red-500 font-medium' : 'text-gray-300' }}">
                                {{ $hasData && $row['total_deductions'] > 0 ? '-'.number_format($row['total_deductions'], 3) : '—' }}
                            </td>
                            <td class="text-end font-bold text-orange-600">{{ $hasData ? number_format($row['total_net'], 3) : '—' }}</td>
                            <td class="text-center">
                                @if($hasData)
                                <span class="text-xs font-medium {{ $row['count_paid'] === $row['count_total'] ? 'text-green-600' : 'text-amber-600' }}">
                                    {{ $row['count_paid'] }}/{{ $row['count_total'] }}
                                </span>
                                @else
                                <span class="text-gray-300 text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 font-bold">
                            <td class="text-gray-900">{{ __('Total') }}</td>
                            <td class="text-end text-gray-900">{{ number_format($annualTotals['gross'], 3) }}</td>
                            <td class="text-end text-red-500">{{ $annualTotals['deductions'] > 0 ? '-'.number_format($annualTotals['deductions'], 3) : '—' }}</td>
                            <td class="text-end text-orange-600">{{ number_format($annualTotals['net'], 3) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Per-staff annual totals --}}
        <div class="card overflow-hidden">
            <div class="card-header">
                <h3 class="font-semibold text-gray-900">{{ __('Per Employee') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr>
                        <th>{{ __('Employee') }}</th>
                        <th class="text-end">{{ __('Annual Gross') }}</th>
                        <th class="text-end">{{ __('Annual Net') }}</th>
                        <th class="text-center">{{ __('Months Paid') }}</th>
                    </tr></thead>
                    <tbody>
                        @forelse($staffTotals as $row)
                        <tr>
                            <td>
                                <p class="font-medium text-gray-900">{{ $row['name'] }}</p>
                                <p class="text-xs text-gray-400">{{ $row['employee_id'] }}</p>
                            </td>
                            <td class="text-end text-gray-700">
                                {{ $row['annual_gross'] > 0 ? number_format($row['annual_gross'], 3) : '—' }}
                            </td>
                            <td class="text-end font-bold text-orange-600">
                                {{ $row['annual_net'] > 0 ? number_format($row['annual_net'], 3) : '—' }}
                            </td>
                            <td class="text-center">
                                <span class="text-sm font-medium {{ $row['months_paid'] === 12 ? 'text-green-600' : 'text-gray-700' }}">
                                    {{ $row['months_paid'] }}/12
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="py-10 text-center text-sm text-gray-400">{{ __('No staff found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</x-layouts.app>
