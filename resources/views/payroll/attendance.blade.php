<x-layouts.app title="{{ __('Attendance') }}">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">{{ __('Attendance') }}</h2>
            <p class="text-sm text-gray-500 mt-0.5">{{ __('Daily entry & monthly summary') }}</p>
        </div>
        <div class="sm:ms-auto flex flex-wrap items-center gap-2">
            <a href="{{ route('payroll.index') }}" class="btn-secondary">{{ __('Payroll') }}</a>
            <a href="{{ route('payroll.advances.index') }}" class="btn-secondary">{{ __('Advances') }}</a>
        </div>
    </div>

    {{-- Tabs --}}
    <div x-data="{ tab: 'daily' }" class="space-y-5">

        <div class="flex gap-1 bg-gray-100 rounded-xl p-1 w-fit">
            <button @click="tab='daily'"
                :class="tab==='daily' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-1.5 rounded-lg text-sm font-medium transition">
                {{ __('Daily Entry') }}
            </button>
            <button @click="tab='monthly'"
                :class="tab==='monthly' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-1.5 rounded-lg text-sm font-medium transition">
                {{ __('Monthly Summary') }}
            </button>
        </div>

        {{-- ── Daily Entry Tab ── --}}
        <div x-show="tab==='daily'">
            <form method="POST" action="{{ route('payroll.attendance.store') }}">
                @csrf

                {{-- Date picker --}}
                <div class="flex flex-wrap items-center gap-3 mb-4">
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-700">{{ __('Date') }}</label>
                        <input type="date" name="date" value="{{ $date }}"
                            onchange="this.form.submit()"
                            max="{{ today()->toDateString() }}"
                            class="input w-44">
                    </div>
                    <p class="text-sm text-gray-400">{{ \Carbon\Carbon::parse($date)->format('l, d F Y') }}</p>
                </div>

                <div class="card overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead><tr>
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Check In') }}</th>
                                <th>{{ __('Check Out') }}</th>
                                <th>{{ __('Hours') }}</th>
                                <th>{{ __('Notes') }}</th>
                            </tr></thead>
                            <tbody>
                                @foreach($allStaff as $s)
                                @php $rec = $dailyRecords->get($s->id) @endphp
                                <tr>
                                    <td>
                                        <input type="hidden" name="records[{{ $loop->index }}][staff_id]" value="{{ $s->id }}">
                                        <p class="font-medium text-gray-900">{{ $s->display_name }}</p>
                                        <p class="text-xs text-gray-400">{{ $s->employee_id }}</p>
                                    </td>
                                    <td>
                                        <select name="records[{{ $loop->index }}][status]" class="input w-36 text-sm">
                                            @foreach(['present' => __('Present'), 'absent' => __('Absent'), 'half_day' => __('Half Day'), 'leave' => __('Leave'), 'holiday' => __('Holiday')] as $val => $lbl)
                                            <option value="{{ $val }}" @selected(($rec->status ?? 'present') === $val)>{{ $lbl }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="time" name="records[{{ $loop->index }}][check_in]"
                                            value="{{ $rec->check_in ?? '' }}"
                                            class="input w-28 text-sm">
                                    </td>
                                    <td>
                                        <input type="time" name="records[{{ $loop->index }}][check_out]"
                                            value="{{ $rec->check_out ?? '' }}"
                                            class="input w-28 text-sm">
                                    </td>
                                    <td class="text-gray-500 text-sm">
                                        {{ $rec?->hours_worked ? number_format($rec->hours_worked, 1).'h' : '—' }}
                                    </td>
                                    <td>
                                        <input type="text" name="records[{{ $loop->index }}][notes]"
                                            value="{{ $rec->notes ?? '' }}"
                                            placeholder="{{ __('Optional') }}"
                                            class="input w-36 text-sm">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($allStaff->isEmpty())
                    <p class="py-10 text-center text-sm text-gray-400">{{ __('No active staff found.') }}</p>
                    @else
                    <div class="px-5 py-3 border-t border-gray-50 flex justify-end">
                        <button type="submit" class="btn-primary">{{ __('Save Attendance') }}</button>
                    </div>
                    @endif
                </div>
            </form>
        </div>

        {{-- ── Monthly Summary Tab ── --}}
        <div x-show="tab==='monthly'" x-cloak>
            <form method="GET" action="{{ route('payroll.attendance.index') }}" class="flex flex-wrap items-center gap-2 mb-4">
                <label class="text-sm font-medium text-gray-700">{{ __('Month') }}</label>
                <select name="month" onchange="this.form.submit()" class="input w-36">
                    @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" @selected($m == $month)>{{ \Carbon\Carbon::createFromDate(null, $m, 1)->format('F') }}</option>
                    @endfor
                </select>
                <select name="year" onchange="this.form.submit()" class="input w-24">
                    @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                    <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                    @endfor
                </select>
            </form>

            <div class="card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr>
                            <th>{{ __('Employee') }}</th>
                            <th class="text-center text-green-600">{{ __('Present') }}</th>
                            <th class="text-center text-orange-500">{{ __('Half Day') }}</th>
                            <th class="text-center text-red-500">{{ __('Absent') }}</th>
                            <th class="text-center text-purple-500">{{ __('Leave') }}</th>
                            <th class="text-center text-blue-500">{{ __('Holiday') }}</th>
                            <th class="text-center">{{ __('Effective Days') }}</th>
                            <th class="text-end">{{ __('Monthly Salary') }}</th>
                        </tr></thead>
                        <tbody>
                            @forelse($monthlySummary as $row)
                            <tr>
                                <td>
                                    <p class="font-medium text-gray-900">{{ $row['name'] }}</p>
                                    <p class="text-xs text-gray-400">{{ $row['employee_id'] }}</p>
                                </td>
                                <td class="text-center font-semibold text-green-600">{{ $row['present'] }}</td>
                                <td class="text-center font-semibold text-orange-500">{{ $row['half_day'] }}</td>
                                <td class="text-center font-semibold text-red-500">{{ $row['absent'] }}</td>
                                <td class="text-center font-semibold text-purple-500">{{ $row['leave'] }}</td>
                                <td class="text-center font-semibold text-blue-500">{{ $row['holiday'] }}</td>
                                <td class="text-center font-bold text-gray-900">{{ $row['effective_days'] }}</td>
                                <td class="text-end text-gray-700 font-medium">{{ number_format($row['basic_salary'], 3) }} OMR</td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="py-12 text-center text-sm text-gray-400">{{ __('No attendance records for this period.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</x-layouts.app>
