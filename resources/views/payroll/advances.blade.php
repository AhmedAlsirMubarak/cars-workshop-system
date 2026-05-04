<x-layouts.app title="{{ __('Salary Advances') }}">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">{{ __('Salary Advances') }}</h2>
            <p class="text-sm text-gray-500 mt-0.5">{{ __('Manage advance requests and deductions') }}</p>
        </div>
        <div class="sm:ms-auto flex flex-wrap items-center gap-2">
            <a href="{{ route('payroll.index') }}" class="btn-secondary">{{ __('Payroll') }}</a>
            <a href="{{ route('payroll.attendance.index') }}" class="btn-secondary">{{ __('Attendance') }}</a>
            <button x-data @click="$dispatch('open-advance-modal')" class="btn-primary">+ {{ __('New Advance') }}</button>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mb-6">
        <div class="card p-4 sm:p-5">
            <p class="text-xs text-gray-400 mb-1">{{ __('Pending Requests') }}</p>
            <p class="text-2xl font-bold text-amber-600">{{ $summary['pending_count'] }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ number_format($summary['pending_amount'], 3) }} OMR</p>
        </div>
        <div class="card p-4 sm:p-5">
            <p class="text-xs text-gray-400 mb-1">{{ __('Approved This Month') }}</p>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($summary['this_month'], 3) }} <span class="text-sm font-normal text-gray-500">OMR</span></p>
        </div>
        <div class="card p-4 sm:p-5">
            <p class="text-xs text-gray-400 mb-1">{{ __('Active Staff') }}</p>
            <p class="text-2xl font-bold text-gray-900">{{ $staff->count() }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('payroll.advances.index') }}" class="flex flex-wrap gap-2 mb-4">
        <select name="status" onchange="this.form.submit()" class="input w-40">
            <option value="">{{ __('All statuses') }}</option>
            <option value="pending"  @selected(request('status')==='pending')>{{ __('Pending') }}</option>
            <option value="approved" @selected(request('status')==='approved')>{{ __('Approved') }}</option>
            <option value="rejected" @selected(request('status')==='rejected')>{{ __('Rejected') }}</option>
            <option value="deducted" @selected(request('status')==='deducted')>{{ __('Deducted') }}</option>
        </select>
        <select name="staff_id" onchange="this.form.submit()" class="input w-48">
            <option value="">{{ __('All staff') }}</option>
            @foreach($staff as $s)
            <option value="{{ $s->id }}" @selected(request('staff_id') == $s->id)>{{ $s->display_name }}</option>
            @endforeach
        </select>
    </form>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="hidden sm:block overflow-x-auto">
            <table class="table">
                <thead><tr>
                    <th>{{ __('Employee') }}</th>
                    <th class="text-end">{{ __('Amount') }}</th>
                    <th>{{ __('Requested') }}</th>
                    <th>{{ __('Deduct Period') }}</th>
                    <th>{{ __('Reason') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th></th>
                </tr></thead>
                <tbody>
                    @forelse($advances as $adv)
                    <tr>
                        <td>
                            <p class="font-medium text-gray-900">{{ $adv->staff?->user?->name }}</p>
                            <p class="text-xs text-gray-400">{{ $adv->staff?->employee_id }}</p>
                        </td>
                        <td class="text-end font-bold text-gray-900">{{ number_format($adv->amount, 3) }} OMR</td>
                        <td class="text-sm text-gray-600">{{ $adv->requested_on->format('d M Y') }}</td>
                        <td class="text-sm text-gray-600 font-mono">{{ $adv->deduct_period }}</td>
                        <td class="text-sm text-gray-500 max-w-[180px] truncate">{{ $adv->reason }}</td>
                        <td>
                            @php
                                $colors = ['pending'=>'badge-yellow','approved'=>'badge-blue','rejected'=>'badge-red','deducted'=>'badge-teal'];
                                $labels = ['pending'=>__('Pending'),'approved'=>__('Approved'),'rejected'=>__('Rejected'),'deducted'=>__('Deducted')];
                            @endphp
                            <span class="{{ $colors[$adv->status] ?? 'badge-gray' }}">{{ $labels[$adv->status] ?? $adv->status }}</span>
                        </td>
                        <td class="text-end">
                            @if($adv->status === 'pending')
                            <div class="flex items-center justify-end gap-2">
                                <form method="POST" action="{{ route('payroll.advances.approve', $adv) }}">
                                    @csrf @method('PATCH')
                                    <button class="text-xs bg-blue-50 hover:bg-blue-100 text-blue-700 px-2.5 py-1 rounded-lg transition">✓ {{ __('Approve') }}</button>
                                </form>
                                <button x-data
                                    @click="$dispatch('open-reject-modal', { id: {{ $adv->id }} })"
                                    class="text-xs bg-red-50 hover:bg-red-100 text-red-700 px-2.5 py-1 rounded-lg transition">
                                    ✕ {{ __('Reject') }}
                                </button>
                                <button
                                    @click="$dispatch('open-confirm-delete', { url: '{{ route('payroll.advances.destroy', $adv) }}' })"
                                    class="text-xs text-gray-400 hover:text-red-600 transition px-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                            @else
                            <span class="text-xs text-gray-400">
                                @if($adv->approved_on){{ $adv->approved_on->format('d M Y') }}@endif
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="py-12 text-center text-sm text-gray-400">{{ __('No advance requests found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile --}}
        <div class="sm:hidden divide-y divide-gray-50">
            @forelse($advances as $adv)
            <div class="p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-medium text-gray-900">{{ $adv->staff?->user?->name }}</p>
                        <p class="text-xs text-gray-400">{{ $adv->requested_on->format('d M Y') }} · {{ $adv->deduct_period }}</p>
                        <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $adv->reason }}</p>
                    </div>
                    <div class="text-end shrink-0">
                        <p class="font-bold text-gray-900">{{ number_format($adv->amount, 3) }}</p>
                        <p class="text-xs text-gray-400">OMR</p>
                        @php $colors = ['pending'=>'badge-yellow','approved'=>'badge-blue','rejected'=>'badge-red','deducted'=>'badge-teal']; $labels = ['pending'=>__('Pending'),'approved'=>__('Approved'),'rejected'=>__('Rejected'),'deducted'=>__('Deducted')]; @endphp
                        <span class="{{ $colors[$adv->status] ?? 'badge-gray' }} mt-1 inline-block">{{ $labels[$adv->status] ?? $adv->status }}</span>
                    </div>
                </div>
                @if($adv->status === 'pending')
                <div class="flex gap-2 mt-3">
                    <form method="POST" action="{{ route('payroll.advances.approve', $adv) }}" class="flex-1">
                        @csrf @method('PATCH')
                        <button class="w-full text-sm bg-blue-50 hover:bg-blue-100 text-blue-700 px-3 py-1.5 rounded-lg transition">✓ {{ __('Approve') }}</button>
                    </form>
                    <button x-data @click="$dispatch('open-reject-modal', { id: {{ $adv->id }} })"
                        class="flex-1 text-sm bg-red-50 hover:bg-red-100 text-red-700 px-3 py-1.5 rounded-lg transition">
                        ✕ {{ __('Reject') }}
                    </button>
                </div>
                @endif
            </div>
            @empty
            <p class="py-10 text-center text-sm text-gray-400">{{ __('No advance requests found.') }}</p>
            @endforelse
        </div>
    </div>

    @include('components.pagination', ['paginator' => $advances])

    {{-- New Advance Modal --}}
    @push('modals')
    <div x-data="{ open: false }" @open-advance-modal.window="open = true" x-cloak>
        <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6" @click.stop>
                <h3 class="font-semibold text-gray-900 mb-4">{{ __('Request Salary Advance') }}</h3>
                <form method="POST" action="{{ route('payroll.advances.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Employee') }}</label>
                        <select name="staff_id" required class="input w-full">
                            <option value="">{{ __('Select employee…') }}</option>
                            @foreach($staff as $s)
                            <option value="{{ $s->id }}">{{ $s->display_name }} ({{ $s->employee_id }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Amount (OMR)') }}</label>
                            <input type="number" name="amount" step="0.001" min="1" required class="input w-full" placeholder="0.000">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Requested On') }}</label>
                            <input type="date" name="requested_on" value="{{ today()->toDateString() }}" max="{{ today()->toDateString() }}" required class="input w-full">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Deduct Month') }}</label>
                            <select name="deduct_month" required class="input w-full">
                                @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" @selected($m == now()->month)>{{ \Carbon\Carbon::createFromDate(null, $m, 1)->format('F') }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Deduct Year') }}</label>
                            <select name="deduct_year" required class="input w-full">
                                @for($y = now()->year; $y <= now()->year + 1; $y++)
                                <option value="{{ $y }}" @selected($y == now()->year)>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Reason') }}</label>
                        <textarea name="reason" rows="3" required class="input w-full" placeholder="{{ __('Reason for advance request…') }}"></textarea>
                    </div>
                    <div class="flex gap-3 pt-1">
                        <button type="submit" class="btn-primary flex-1">{{ __('Submit Request') }}</button>
                        <button type="button" @click="open = false" class="btn-secondary flex-1">{{ __('Cancel') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div x-data="{ open: false, advanceId: null }" @open-reject-modal.window="open = true; advanceId = $event.detail.id" x-cloak>
        <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6" @click.stop>
                <h3 class="font-semibold text-gray-900 mb-4">{{ __('Reject Advance') }}</h3>
                <template x-for="adv in [advanceId]" :key="adv">
                    <form :action="'/payroll/advances/' + adv + '/reject'" method="POST">
                        @csrf
                        <input type="hidden" name="_method" value="PATCH">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Rejection Reason') }}</label>
                            <textarea name="rejection_reason" rows="3" required class="input w-full"
                                placeholder="{{ __('Explain why the request is being rejected…') }}"></textarea>
                        </div>
                        <div class="flex gap-3">
                            <button type="submit" class="btn-danger flex-1">{{ __('Reject') }}</button>
                            <button type="button" @click="open = false" class="btn-secondary flex-1">{{ __('Cancel') }}</button>
                        </div>
                    </form>
                </template>
            </div>
        </div>
    </div>
    @endpush

</x-layouts.app>
