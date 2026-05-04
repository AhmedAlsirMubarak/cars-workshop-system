<x-layouts.app title="{{ __('Appointments') }}">

    {{-- Filters + New button --}}
    <form method="GET" action="{{ route('appointments.index') }}" class="flex flex-wrap items-center gap-3 mb-6">
        <input type="date" name="date" value="{{ request('date') }}"
            onchange="this.form.submit()" class="input w-44">

        <select name="status" onchange="this.form.submit()" class="input w-40">
            <option value="">{{ __('All statuses') }}</option>
            @foreach(['scheduled' => __('Scheduled'), 'confirmed' => __('Confirmed'), 'in_progress' => __('In Progress'), 'completed' => __('Completed'), 'cancelled' => __('Cancelled'), 'no_show' => __('No Show')] as $val => $lbl)
            <option value="{{ $val }}" @selected(request('status') === $val)>{{ $lbl }}</option>
            @endforeach
        </select>

        <select name="staff_id" onchange="this.form.submit()" class="input w-44">
            <option value="">{{ __('All staff') }}</option>
            @foreach($staff as $s)
            <option value="{{ $s->id }}" @selected(request('staff_id') == $s->id)>{{ $s->display_name }}</option>
            @endforeach
        </select>

        <button type="submit" class="btn-secondary">{{ __('Filter') }}</button>
        <a href="{{ route('appointments.index') }}" class="text-xs text-gray-400 hover:text-gray-600">{{ __('Clear') }}</a>

        {{-- New Appointment button opens modal --}}
        <button type="button" class="ms-auto btn-primary"
            x-data @click="$dispatch('open-modal', 'new-appointment')">
            + {{ __('New Appointment') }}
        </button>
    </form>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="hidden sm:block overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Date / Time') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Vehicle') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Staff') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $apt)
                    <tr>
                        <td>
                            <p class="font-medium text-gray-900">{{ $apt->scheduled_at->format('d M Y') }}</p>
                            <p class="text-xs text-orange-500 font-semibold">{{ $apt->scheduled_at->format('H:i') }}</p>
                        </td>
                        <td class="font-medium text-gray-900">{{ $apt->customer?->name }}</td>
                        <td class="text-xs text-gray-500">
                            {{ $apt->vehicle?->make }} {{ $apt->vehicle?->model }}<br>
                            <span class="font-mono">{{ $apt->vehicle?->plate_number }}</span>
                        </td>
                        <td class="capitalize text-gray-600 text-sm">{{ $apt->type ?? '—' }}</td>
                        <td class="text-gray-500 text-xs">{{ $apt->staff?->user?->name ?? '—' }}</td>
                        <td>@include('components.status-badge', ['status' => $apt->status])</td>
                        <td>
                            @if(!$apt->jobOrder)
                            <form method="POST" action="{{ route('appointments.convert-to-job', $apt) }}" class="inline">
                                @csrf
                                <button type="submit" class="btn-secondary btn-sm">→ {{ __('Job') }}</button>
                            </form>
                            @else
                            <a href="{{ route('jobs.show', $apt->jobOrder) }}" class="text-xs text-orange-500 hover:underline">{{ __('View Job') }}</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-sm text-gray-400">{{ __('No appointments found.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="sm:hidden divide-y divide-gray-50">
            @forelse($appointments as $apt)
            <div class="p-4">
                <div class="flex items-start justify-between gap-3 mb-2">
                    <div>
                        <p class="font-medium text-gray-900">{{ $apt->customer?->name }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $apt->vehicle?->make }} {{ $apt->vehicle?->model }} · {{ $apt->vehicle?->plate_number }}</p>
                    </div>
                    <div class="text-end shrink-0">
                        <p class="text-sm font-bold text-orange-500">{{ $apt->scheduled_at->format('H:i') }}</p>
                        <p class="text-xs text-gray-400">{{ $apt->scheduled_at->format('d M') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    @include('components.status-badge', ['status' => $apt->status])
                    <span class="text-xs text-gray-400 capitalize">{{ $apt->type }}</span>
                    @if(!$apt->jobOrder)
                    <form method="POST" action="{{ route('appointments.convert-to-job', $apt) }}" class="inline ms-auto">
                        @csrf
                        <button type="submit" class="btn-secondary btn-sm">{{ __('Convert to Job') }}</button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <p class="py-12 text-center text-sm text-gray-400">{{ __('No appointments found.') }}</p>
            @endforelse
        </div>
    </div>

    @include('components.pagination', ['paginator' => $appointments])

    {{-- New Appointment Modal --}}
    @push('modals')
    <div x-data="{ open: false }"
        @open-modal.window="if ($event.detail === 'new-appointment') open = true"
        x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        @keydown.escape.window="open = false">

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 overflow-y-auto max-h-[90vh]"
            @click.stop>
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-semibold text-gray-900">{{ __('New Appointment') }}</h3>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('appointments.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Customer') }} <span class="text-red-500">*</span></label>
                    <select name="customer_id" required class="input w-full">
                        <option value="">{{ __('Select customer…') }}</option>
                        @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Vehicle') }} <span class="text-red-500">*</span></label>
                    <select name="vehicle_id" required class="input w-full">
                        <option value="">{{ __('Select vehicle…') }}</option>
                        @foreach($vehicles as $v)
                        <option value="{{ $v->id }}">{{ $v->make }} {{ $v->model }} — {{ $v->plate_number }} ({{ $v->customer?->name }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Assigned Staff') }}</label>
                    <select name="staff_id" class="input w-full">
                        <option value="">{{ __('Unassigned') }}</option>
                        @foreach($staff as $s)
                        <option value="{{ $s->id }}">{{ $s->display_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Date & Time') }} <span class="text-red-500">*</span></label>
                        <input type="datetime-local" name="scheduled_at" required
                            class="input w-full" min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Duration (min)') }}</label>
                        <input type="number" name="duration_minutes" value="60" min="15" step="15" class="input w-full">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Type') }}</label>
                    <select name="type" class="input w-full">
                        <option value="inspection">{{ __('Inspection') }}</option>
                        <option value="repair">{{ __('Repair') }}</option>
                        <option value="maintenance">{{ __('Maintenance') }}</option>
                        <option value="diagnostic">{{ __('Diagnostic') }}</option>
                        <option value="other">{{ __('Other') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Description') }}</label>
                    <textarea name="description" rows="3" class="input w-full"
                        placeholder="{{ __('Brief description of the appointment…') }}"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary flex-1">{{ __('Book Appointment') }}</button>
                    <button type="button" @click="open = false" class="btn-secondary flex-1">{{ __('Cancel') }}</button>
                </div>
            </form>
        </div>
    </div>
    @endpush

</x-layouts.app>
