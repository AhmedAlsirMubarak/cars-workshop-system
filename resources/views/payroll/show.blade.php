<x-layouts.app :title="__('Payslip') . ' · ' . $payroll->staff?->user?->name">

    {{-- Top bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-6">
        <a href="{{ route('payroll.index') }}" class="flex items-center gap-1 text-sm text-gray-400 hover:text-gray-700 transition w-fit">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            {{ __('Back') }}
        </a>
        <div class="sm:ms-auto flex flex-wrap items-center gap-2">
            @include('components.status-badge', ['status' => $payroll->status])
            @if($payroll->status === 'draft')
            <form method="POST" action="{{ route('payroll.approve', $payroll) }}">
                @csrf @method('PATCH')
                <button class="btn-secondary btn-sm">✓ {{ __('Approve') }}</button>
            </form>
            @endif
            @if($payroll->status === 'approved')
            <button x-data @click="$dispatch('open-pay-modal')" class="btn-primary btn-sm">💳 {{ __('Mark as Paid') }}</button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        <div class="lg:col-span-2 space-y-4">

            {{-- Employee header --}}
            <div class="card p-5 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                    <div class="flex items-center gap-4 flex-1">
                        <div class="w-14 h-14 rounded-2xl bg-orange-500 flex items-center justify-center text-white font-bold text-xl shrink-0 shadow-md shadow-orange-200">
                            {{ strtoupper(substr($payroll->staff?->user?->name ?? 'U', 0, 1)) }}
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">{{ $payroll->staff?->user?->name }}</h2>
                            <p class="text-sm text-gray-500">{{ $payroll->staff?->employee_id }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $payroll->staff?->specialization ?? '—' }}</p>
                        </div>
                    </div>
                    <div class="text-start sm:text-end">
                        <p class="text-3xl font-black text-orange-600">{{ number_format($payroll->net_salary, 3) }} <span class="text-lg font-semibold">OMR</span></p>
                        <p class="text-sm text-gray-400 mt-0.5">{{ __('Net') }} · {{ \Carbon\Carbon::createFromDate($payroll->year, $payroll->month, 1)->format('F Y') }}</p>
                        @if($payroll->paid_on)
                        <p class="text-xs text-green-600 mt-1 font-medium">✓ {{ __('Paid on') }} {{ $payroll->paid_on->format('d M Y') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Earnings --}}
            <div class="card p-5 sm:p-6">
                <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span> {{ __('Earnings') }}
                </h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ __('Basic Salary') }}</span>
                        <span class="font-semibold text-gray-900">{{ number_format($payroll->basic_salary, 3) }} OMR</span>
                    </div>
                    @if($payroll->bonus > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ __('Bonus') }} @if($payroll->bonus_note)<span class="text-gray-400"> – {{ $payroll->bonus_note }}</span>@endif</span>
                        <span class="font-semibold text-green-600">+{{ number_format($payroll->bonus, 3) }} OMR</span>
                    </div>
                    @endif
                    <div class="flex justify-between pt-2 border-t border-gray-100 font-semibold">
                        <span class="text-gray-900">{{ __('Gross') }}</span>
                        <span class="text-gray-900">{{ number_format($payroll->gross_salary, 3) }} OMR</span>
                    </div>
                </div>
            </div>

            {{-- Deductions --}}
            <div class="card p-5 sm:p-6">
                <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span> {{ __('Deductions') }}
                </h3>
                <div class="space-y-3 text-sm">
                    @if($payroll->days_absent > 0 || $payroll->days_half > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ __('Absent') }} ({{ $payroll->days_absent }}d{{ $payroll->days_half > 0 ? ' + '.$payroll->days_half.'½' : '' }})</span>
                        <span class="text-red-500 font-medium">-{{ number_format($payroll->absence_deduction, 3) }} OMR</span>
                    </div>
                    @endif
                    @if($payroll->advance_deduction > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ __('Salary Advance Deducted') }}</span>
                        <span class="text-red-500 font-medium">-{{ number_format($payroll->advance_deduction, 3) }} OMR</span>
                    </div>
                    @endif
                    @if($payroll->other_deduction > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ __('Other') }} @if($payroll->other_deduction_note) – {{ $payroll->other_deduction_note }}@endif</span>
                        <span class="text-red-500 font-medium">-{{ number_format($payroll->other_deduction, 3) }} OMR</span>
                    </div>
                    @endif
                    @if($payroll->total_deductions == 0)
                    <p class="text-gray-400 italic text-center py-2">{{ __('No deductions this month.') }}</p>
                    @endif
                    <div class="flex justify-between pt-2 border-t border-gray-100 font-semibold text-red-500">
                        <span>{{ __('Total Deductions') }}</span>
                        <span>-{{ number_format($payroll->total_deductions, 3) }} OMR</span>
                    </div>
                </div>
            </div>

            {{-- Net result --}}
            <div class="card p-5 sm:p-6 bg-gradient-to-r from-orange-50 to-amber-50 border-orange-200">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <p class="text-sm text-orange-600 font-medium">{{ __('Net Payable') }}</p>
                        <p class="text-4xl font-black text-orange-600 mt-1">{{ number_format($payroll->net_salary, 3) }} <span class="text-xl">OMR</span></p>
                    </div>
                    <div class="space-y-1 text-sm text-end">
                        @if(in_array($payroll->payment_method, ['bank_transfer', 'split']))
                        <p><span class="text-gray-500">🏦 {{ __('Bank') }}:</span> <strong class="text-gray-900">{{ number_format($payroll->bank_amount, 3) }} OMR</strong></p>
                        @endif
                        @if(in_array($payroll->payment_method, ['cash', 'split']))
                        <p><span class="text-gray-500">💵 {{ __('Cash') }}:</span> <strong class="text-gray-900">{{ number_format($payroll->cash_amount, 3) }} OMR</strong></p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Adjustments form (draft/approved only) --}}
            @if($payroll->status !== 'paid')
            <div class="card p-5 sm:p-6">
                <h3 class="font-semibold text-gray-900 mb-4">{{ __('Adjust Bonus & Deductions') }}</h3>
                <form method="POST" action="{{ route('payroll.update', $payroll) }}" class="space-y-4">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div><label class="label">{{ __('Bonus (OMR)') }}</label><input type="number" name="bonus" step="0.001" min="0" value="{{ old('bonus', $payroll->bonus) }}" class="input"></div>
                        <div><label class="label">{{ __('Bonus Note') }}</label><input type="text" name="bonus_note" value="{{ old('bonus_note', $payroll->bonus_note) }}" class="input" placeholder="{{ __('Optional') }}"></div>
                        <div><label class="label">{{ __('Other Deduction (OMR)') }}</label><input type="number" name="other_deduction" step="0.001" min="0" value="{{ old('other_deduction', $payroll->other_deduction) }}" class="input"></div>
                        <div><label class="label">{{ __('Deduction Note') }}</label><input type="text" name="other_deduction_note" value="{{ old('other_deduction_note', $payroll->other_deduction_note) }}" class="input" placeholder="{{ __('Optional') }}"></div>
                    </div>
                    <div>
                        <label class="label">{{ __('Payment Method') }}</label>
                        <div class="flex flex-wrap gap-2" x-data="{ method: '{{ $payroll->payment_method }}' }">
                            @foreach(['bank_transfer' => '🏦 Bank Transfer', 'cash' => '💵 Cash', 'split' => '⚡ Split'] as $val => $lbl)
                            <label :class="method === '{{ $val }}' ? 'bg-orange-500 border-orange-500 text-white' : 'bg-white border-gray-200 text-gray-700 hover:border-orange-300'"
                                class="flex items-center gap-2 px-4 py-2.5 rounded-xl border text-sm font-medium cursor-pointer transition">
                                <input type="radio" name="payment_method" value="{{ $val }}" x-model="method" @change="method='{{ $val }}'" class="sr-only" {{ $payroll->payment_method === $val ? 'checked' : '' }}>
                                {{ $lbl }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="label">{{ __('Notes') }}</label>
                        <textarea name="notes" class="input h-20 resize-none">{{ old('notes', $payroll->notes) }}</textarea>
                    </div>
                    <button type="submit" class="btn-primary">{{ __('Save Changes') }}</button>
                </form>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-4">
            {{-- Attendance --}}
            <div class="card p-5">
                <h3 class="font-semibold text-gray-900 text-sm mb-4">{{ __('Attendance This Month') }}</h3>
                <div class="grid grid-cols-3 gap-2 text-center mb-4">
                    <div class="bg-green-50 rounded-xl p-3">
                        <p class="text-2xl font-bold text-green-600">{{ $payroll->days_present }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ __('Present') }}</p>
                    </div>
                    <div class="bg-red-50 rounded-xl p-3">
                        <p class="text-2xl font-bold text-red-600">{{ $payroll->days_absent }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ __('Absent') }}</p>
                    </div>
                    <div class="bg-orange-50 rounded-xl p-3">
                        <p class="text-2xl font-bold text-orange-600">{{ $payroll->days_half }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ __('Half Day') }}</p>
                    </div>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600"><span>{{ __('Working Days') }}</span><strong class="text-gray-900">{{ $payroll->working_days }}</strong></div>
                    <div class="flex justify-between text-gray-600"><span>{{ __('Hours Worked') }}</span><strong class="text-gray-900">{{ $payroll->hours_worked }}h</strong></div>
                    <div class="mt-3">
                        @php $rate = $payroll->working_days > 0 ? round(($payroll->days_present + $payroll->days_half * 0.5) / $payroll->working_days * 100) : 0 @endphp
                        <div class="flex justify-between text-xs text-gray-500 mb-1"><span>{{ __('Attendance Rate') }}</span><span>{{ $rate }}%</span></div>
                        <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-2 rounded-full {{ $rate >= 90 ? 'bg-green-500' : ($rate >= 75 ? 'bg-orange-500' : 'bg-red-500') }}" style="width: {{ $rate }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bank info --}}
            <div class="card p-5">
                <h3 class="font-semibold text-gray-900 text-sm mb-3">{{ __('Bank Details') }}</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">{{ __('Bank') }}</span><span class="font-medium text-gray-900">{{ $payroll->staff?->bank_name ?? '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">{{ __('Account #') }}</span><span class="font-mono text-xs text-gray-700">{{ $payroll->staff?->bank_account_number ?? '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">{{ __('IBAN') }}</span><span class="font-mono text-xs text-gray-700">{{ $payroll->staff?->iban ?? '—' }}</span></div>
                </div>
            </div>

            {{-- Audit --}}
            <div class="card p-5">
                <h3 class="font-semibold text-gray-900 text-sm mb-3">{{ __('Audit Trail') }}</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">{{ __('Prepared by') }}</span><span class="text-gray-900">{{ $payroll->preparedBy?->name ?? '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">{{ __('Approved by') }}</span><span class="text-gray-900">{{ $payroll->approvedBy?->name ?? '—' }}</span></div>
                    @if($payroll->payment_reference)
                    <div class="flex justify-between"><span class="text-gray-500">{{ __('Reference') }}</span><span class="font-mono text-xs text-gray-700">{{ $payroll->payment_reference }}</span></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Mark Paid Modal --}}
    @if($payroll->status === 'approved')
    @push('modals')
    <div x-data="modal(false)" @open-pay-modal.window="show()" x-cloak>
        <div x-show="open" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/50 px-4 pb-4 sm:pb-0"
            x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6" @click.stop>
                <h3 class="font-semibold text-gray-900 mb-1">{{ __('Confirm Payment') }}</h3>
                <p class="text-sm text-gray-500 mb-5">{{ $payroll->staff?->user?->name }} · {{ number_format($payroll->net_salary, 3) }} OMR</p>
                <form method="POST" action="{{ route('payroll.mark-paid', $payroll) }}" class="space-y-4">
                    @csrf @method('PATCH')
                    <div><label class="label">{{ __('Paid On') }}</label><input type="date" name="paid_on" required value="{{ date('Y-m-d') }}" class="input"></div>
                    <div><label class="label">{{ __('Payment Reference') }} <span class="text-gray-400">({{ __('optional') }})</span></label><input type="text" name="payment_reference" class="input" placeholder="{{ __('e.g. transaction ID') }}"></div>
                    <div class="flex gap-3">
                        <button type="submit" class="btn-primary flex-1">{{ __('Confirm Payment') }}</button>
                        <button type="button" @click="hide()" class="btn-secondary flex-1">{{ __('Cancel') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endpush
    @endif

</x-layouts.app>
