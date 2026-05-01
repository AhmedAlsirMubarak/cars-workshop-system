<x-layouts.app title="Staff">

    {{-- Filters + Add Staff --}}
    <form method="GET" action="{{ route('staff.index') }}" class="flex flex-wrap items-center gap-3 mb-6">
        <div x-data="searchBox()">
            <input type="text" name="search" x-model="query" @input="debounce()"
                placeholder="Search by name…" class="input w-56"
                value="{{ request('search') }}">
        </div>
        <select name="status" onchange="this.form.submit()" class="input w-36">
            <option value="">All statuses</option>
            <option value="active"   @selected(request('status') === 'active')>Active</option>
            <option value="on_leave" @selected(request('status') === 'on_leave')>On Leave</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
        </select>
        <button type="button" class="ms-auto btn-primary"
            x-data @click="$dispatch('open-modal', 'add-staff')">
            + Add Staff
        </button>
    </form>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="hidden sm:block overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Specialization</th>
                        <th class="text-end">Hourly Rate</th>
                        <th>Status</th>
                        <th class="text-center">Total Jobs</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staff as $s)
                    <tr class="cursor-pointer" onclick="location='{{ route('staff.show', $s) }}'">
                        <td class="font-mono text-xs font-semibold text-gray-600">{{ $s->employee_id }}</td>
                        <td>
                            <p class="font-medium text-gray-900">{{ $s->user->name }}</p>
                            <p class="text-xs text-gray-400">{{ $s->user->email }}</p>
                        </td>
                        <td class="capitalize text-gray-600 text-sm">{{ $s->user->roles->first()?->name ?? '—' }}</td>
                        <td class="text-gray-500 text-sm">{{ $s->specialization ?? '—' }}</td>
                        <td class="text-end text-sm text-gray-700">{{ number_format($s->hourly_rate, 3) }} OMR</td>
                        <td>@include('components.status-badge', ['status' => $s->status])</td>
                        <td class="text-center font-bold text-gray-900">{{ $s->total_jobs }}</td>
                        <td>
                            <a href="{{ route('staff.show', $s) }}" class="text-xs text-orange-500 hover:underline" onclick="event.stopPropagation()">View →</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-sm text-gray-400">No staff members found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile --}}
        <div class="sm:hidden divide-y divide-gray-50">
            @forelse($staff as $s)
            <a href="{{ route('staff.show', $s) }}" class="flex items-start justify-between gap-3 p-4 hover:bg-gray-50 transition">
                <div>
                    <p class="font-medium text-gray-900">{{ $s->user->name }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $s->employee_id }} · {{ $s->specialization ?? $s->user->roles->first()?->name }}</p>
                </div>
                <div class="text-end shrink-0">
                    @include('components.status-badge', ['status' => $s->status])
                    <p class="text-xs text-gray-400 mt-1">{{ $s->total_jobs }} jobs</p>
                </div>
            </a>
            @empty
            <p class="py-12 text-center text-sm text-gray-400">No staff found.</p>
            @endforelse
        </div>
    </div>

    {{-- Add Staff Modal --}}
    @push('modals')
    <div x-data="{ open: false }"
        @open-modal.window="if ($event.detail === 'add-staff') open = true"
        x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        @keydown.escape.window="open = false">

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 overflow-y-auto max-h-[90vh]" @click.stop>
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-semibold text-gray-900">Add Staff Member</h3>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('staff.store') }}" class="space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required class="input w-full text-sm" placeholder="Full name">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" required class="input w-full text-sm" placeholder="work@email.com">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Phone <span class="text-red-500">*</span></label>
                        <input type="text" name="phone" required class="input w-full text-sm" placeholder="+968 …">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required minlength="8" class="input w-full text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Confirm Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" required class="input w-full text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                        <select name="role" required class="input w-full text-sm">
                            <option value="technician">Technician</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Employee ID <span class="text-red-500">*</span></label>
                        <input type="text" name="employee_id" required class="input w-full text-sm" placeholder="EMP-001">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Specialization</label>
                        <input type="text" name="specialization" class="input w-full text-sm" placeholder="e.g. Engine, Electrical">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Hourly Rate (OMR)</label>
                        <input type="number" name="hourly_rate" step="0.001" min="0" value="0" class="input w-full text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Hired Date</label>
                        <input type="date" name="hired_at" class="input w-full text-sm">
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary flex-1">Add Staff</button>
                    <button type="button" @click="open = false" class="btn-secondary flex-1">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    @endpush

</x-layouts.app>
