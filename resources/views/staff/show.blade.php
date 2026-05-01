<x-layouts.app title="{{ $staff->user->name }}">

    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('staff.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">{{ $staff->user->name }}</h2>
                    @include('components.status-badge', ['status' => $staff->status])
                </div>
                <p class="text-sm text-gray-400 mt-0.5">{{ $staff->employee_id }} · Hired {{ $staff->hired_at?->format('d M Y') ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-6">

        {{-- Left: profile + update form --}}
        <div class="space-y-4">

            {{-- Profile --}}
            <div class="card p-5 space-y-3">
                <h3 class="font-semibold text-gray-900 text-sm">Profile</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex gap-2">
                        <span class="text-gray-400 w-24 shrink-0">Email</span>
                        <span class="text-gray-700 truncate">{{ $staff->user->email }}</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="text-gray-400 w-24 shrink-0">Phone</span>
                        <span class="text-gray-700">{{ $staff->user->phone ?? '—' }}</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="text-gray-400 w-24 shrink-0">Role</span>
                        <span class="text-gray-700 capitalize">{{ $staff->user->roles->first()?->name ?? '—' }}</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="text-gray-400 w-24 shrink-0">Specialization</span>
                        <span class="text-gray-700">{{ $staff->specialization ?? '—' }}</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="text-gray-400 w-24 shrink-0">Hourly Rate</span>
                        <span class="text-gray-700">{{ number_format($staff->hourly_rate, 3) }} OMR</span>
                    </div>
                </div>
            </div>

            {{-- Update form --}}
            <div class="card p-5">
                <h3 class="font-semibold text-gray-900 text-sm mb-4">Update Info</h3>
                <form method="POST" action="{{ route('staff.update', $staff) }}" class="space-y-3">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Specialization</label>
                        <input type="text" name="specialization" value="{{ old('specialization', $staff->specialization) }}"
                            class="input w-full text-sm" placeholder="e.g. Engine, Electrical">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Hourly Rate (OMR)</label>
                        <input type="number" name="hourly_rate" value="{{ old('hourly_rate', $staff->hourly_rate) }}"
                            step="0.001" min="0" class="input w-full text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                        <select name="status" class="input w-full text-sm">
                            <option value="active"   @selected(old('status', $staff->status) === 'active')>Active</option>
                            <option value="on_leave" @selected(old('status', $staff->status) === 'on_leave')>On Leave</option>
                            <option value="inactive" @selected(old('status', $staff->status) === 'inactive')>Inactive</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Notes</label>
                        <textarea name="notes" rows="2" class="input w-full text-sm"
                            placeholder="Internal notes…">{{ old('notes', $staff->notes ?? '') }}</textarea>
                    </div>

                    <button type="submit" class="btn-primary w-full">Save Changes</button>
                </form>
            </div>
        </div>

        {{-- Right: metrics + jobs --}}
        <div class="xl:col-span-2 space-y-4 sm:space-y-6">

            {{-- Metrics --}}
            <div class="grid grid-cols-3 gap-3">
                <div class="card p-4 text-center">
                    <p class="text-2xl font-bold text-green-600">{{ $metrics['jobs_completed'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">Completed</p>
                </div>
                <div class="card p-4 text-center">
                    <p class="text-2xl font-bold text-blue-600">{{ $metrics['jobs_in_progress'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">In Progress</p>
                </div>
                <div class="card p-4 text-center">
                    <p class="text-xl font-bold text-gray-900">{{ number_format($metrics['revenue_generated'], 3) }}</p>
                    <p class="text-xs text-gray-400 mt-1">Revenue (OMR)</p>
                </div>
            </div>

            {{-- Recent Jobs --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-gray-900">Recent Job Orders</h3>
                    <a href="{{ route('jobs.index', ['staff_id' => $staff->id]) }}" class="text-xs text-orange-500 hover:underline">View all</a>
                </div>
                @if($staff->jobOrders->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-gray-400">No job orders assigned yet.</p>
                @else
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Job #</th><th>Customer</th><th>Vehicle</th><th>Status</th><th class="text-end">Total</th></tr></thead>
                        <tbody>
                            @foreach($staff->jobOrders->take(15) as $j)
                            <tr class="cursor-pointer" onclick="location='{{ route('jobs.show', $j) }}'">
                                <td class="font-mono text-xs font-semibold text-orange-500">{{ $j->job_number }}</td>
                                <td class="font-medium text-gray-900">{{ $j->customer?->name }}</td>
                                <td class="text-xs text-gray-500">{{ $j->vehicle?->make }} {{ $j->vehicle?->model }}<br><span class="font-mono">{{ $j->vehicle?->plate_number }}</span></td>
                                <td>@include('components.status-badge', ['status' => $j->status])</td>
                                <td class="text-end font-semibold text-gray-900 text-xs">{{ number_format($j->total, 3) }} OMR</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

        </div>
    </div>

</x-layouts.app>
