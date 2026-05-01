<x-layouts.app title="Payroll">
    {{-- Header + month picker --}}
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Payroll</h2>
            <p class="text-sm text-gray-500 mt-0.5">
                {{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y') }}
            </p>
        </div>
        <form method="GET" action="{{ route('payroll.index') }}" class="sm:ms-auto flex flex-wrap items-center gap-2">
            <select name="month" onchange="this.form.submit()" class="input w-36">
                @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" @selected($m == $month)>{{ \Carbon\Carbon::createFromDate(null, $m, 1)->format('F') }}</option>
                @endfor
            </select>
            <select name="year" onchange="this.form.submit()" class="input w-24">
                @for($y = date('Y'); $y >= date('Y') - 4; $y--)
                <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                @endfor
            </select>
            <a href="{{ route('payroll.attendance.index') }}" class="btn-secondary">Attendance</a>
            <a href="{{ route('payroll.advances.index') }}" class="btn-secondary">Advances</a>
            <a href="{{ route('payroll.report') }}" class="btn-secondary">Report</a>
        </form>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4 mb-6">
        @foreach([
            ['label' => 'Total Gross',      'value' => number_format($summary['total_gross'], 3).' OMR',  'color' => 'text-gray-900'],
            ['label' => 'Total Deductions', 'value' => number_format($summary['total_deductions'], 3).' OMR', 'color' => 'text-red-500'],
            ['label' => 'Total Net',        'value' => number_format($summary['total_net'], 3).' OMR',    'color' => 'text-orange-600'],
            ['label' => 'Draft / Approved', 'value' => ($summary['count_draft'] + $summary['count_approved']), 'color' => 'text-gray-700'],
            ['label' => 'Paid',             'value' => $summary['count_paid'],                            'color' => 'text-green-600'],
        ] as $kpi)
        <div class="card p-4 sm:p-5">
            <p class="text-xs text-gray-400 mb-1">{{ $kpi['label'] }}</p>
            <p class="text-lg sm:text-xl font-bold {{ $kpi['color'] }}">{{ $kpi['value'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Missing payslips banner --}}
    @if($missingStaff->count())
    <div x-data="{ working_days: 26 }" class="card p-4 sm:p-5 mb-4 border-amber-200 bg-amber-50">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="flex items-center gap-3 flex-1">
                <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18A9 9 0 0012 3z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-amber-800">{{ $missingStaff->count() }} employees have no payslip yet</p>
                    <p class="text-xs text-amber-600">Set working days and generate all at once</p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <input x-model="working_days" type="number" min="20" max="31" class="input w-20 text-sm" placeholder="Days">
                <form method="POST" action="{{ route('payroll.generate-all') }}">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="year"  value="{{ $year }}">
                    <input type="hidden" name="working_days" :value="working_days">
                    <button type="submit" class="btn-primary text-sm whitespace-nowrap">Generate All</button>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Payroll table --}}
    <div class="card overflow-hidden">
        <div class="card-header">
            <h3 class="font-semibold text-gray-900">Payslips <span class="text-gray-400 font-normal">({{ $payrolls->count() }})</span></h3>
        </div>
        <div class="hidden sm:block overflow-x-auto">
            <table class="table">
                <thead><tr>
                    <th>Employee</th><th class="text-end">Basic</th><th class="text-end">Bonus</th><th class="text-end">Deductions</th><th class="text-end">Net</th><th>Method</th><th>Status</th><th></th>
                </tr></thead>
                <tbody>
                    @forelse($payrolls as $p)
                    <tr class="cursor-pointer" onclick="location='{{ route('payroll.show', $p) }}'">
                        <td>
                            <p class="font-medium text-gray-900">{{ $p->staff?->user?->name }}</p>
                            <p class="text-xs text-gray-400">{{ $p->staff?->employee_id }}</p>
                        </td>
                        <td class="text-end text-gray-700">{{ number_format($p->basic_salary, 3) }}</td>
                        <td class="text-end">
                            @if($p->bonus > 0)<span class="text-green-600 font-medium">+{{ number_format($p->bonus, 3) }}</span>
                            @else<span class="text-gray-300">—</span>@endif
                        </td>
                        <td class="text-end text-red-500 font-medium">
                            @if($p->total_deductions > 0)-{{ number_format($p->total_deductions, 3) }}
                            @else<span class="text-gray-300">—</span>@endif
                        </td>
                        <td class="text-end font-bold text-orange-600">{{ number_format($p->net_salary, 3) }}</td>
                        <td>
                            @php
                                $methodBadge = ['bank_transfer' => 'badge-blue', 'cash' => 'badge-green', 'split' => 'badge-purple'];
                                $methodLabel = ['bank_transfer' => '🏦 Bank', 'cash' => '💵 Cash', 'split' => '⚡ Split'];
                            @endphp
                            <span class="{{ $methodBadge[$p->payment_method] ?? 'badge-gray' }}">
                                {{ $methodLabel[$p->payment_method] ?? $p->payment_method }}
                            </span>
                        </td>
                        <td>@include('components.status-badge', ['status' => $p->status])</td>
                        <td>
                            <a href="{{ route('payroll.show', $p) }}" class="text-xs text-orange-500 hover:underline" onclick="event.stopPropagation()">View →</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="py-12 text-center text-sm text-gray-400">No payslips generated yet. Click "Generate All" above.</td></tr>
                    @endforelse

                    {{-- Missing staff rows --}}
                    @foreach($missingStaff as $s)
                    <tr class="opacity-50">
                        <td>
                            <p class="font-medium text-gray-900">{{ $s->user->name }}</p>
                            <p class="text-xs text-gray-400">{{ $s->employee_id }}</p>
                        </td>
                        <td class="text-end text-gray-500">{{ number_format($s->basic_salary, 3) }}</td>
                        <td colspan="4"><span class="text-xs text-gray-400 italic">No payslip yet</span></td>
                        <td></td>
                        <td>
                            <form method="POST" action="{{ route('payroll.generate') }}" x-data="{ days: 26 }">
                                @csrf
                                <input type="hidden" name="staff_id" value="{{ $s->id }}">
                                <input type="hidden" name="month" value="{{ $month }}">
                                <input type="hidden" name="year" value="{{ $year }}">
                                <input type="hidden" name="working_days" value="26">
                                <button type="submit" class="text-xs bg-orange-100 hover:bg-orange-200 text-orange-700 px-3 py-1.5 rounded-lg transition">Generate</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="sm:hidden divide-y divide-gray-50">
            @forelse($payrolls as $p)
            <a href="{{ route('payroll.show', $p) }}" class="flex items-start justify-between gap-3 p-4 hover:bg-gray-50 transition">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                        <p class="font-semibold text-gray-900">{{ $p->staff?->user?->name }}</p>
                        @include('components.status-badge', ['status' => $p->status])
                    </div>
                    <p class="text-xs text-gray-400">{{ $p->staff?->employee_id }}</p>
                    @if($p->total_deductions > 0)
                    <p class="text-xs text-red-500 mt-1">Deductions: -{{ number_format($p->total_deductions, 3) }} OMR</p>
                    @endif
                </div>
                <div class="text-end shrink-0">
                    <p class="font-bold text-orange-600 text-lg">{{ number_format($p->net_salary, 3) }}</p>
                    <p class="text-xs text-gray-400">OMR</p>
                </div>
            </a>
            @empty
            <p class="py-12 text-center text-sm text-gray-400">No payslips this month.</p>
            @endforelse
        </div>
    </div>
</x-layouts.app>
