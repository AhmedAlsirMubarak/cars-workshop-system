<x-layouts.app title="Job Orders">

    {{-- Filters + New button --}}
    <form method="GET" action="{{ route('jobs.index') }}" class="flex flex-wrap items-center gap-3 mb-6">
        <div x-data="searchBox()">
            <input type="text" name="search" x-model="query" @input="debounce()"
                placeholder="Search job #, customer…" class="input w-60">
        </div>
        <select name="status" onchange="this.form.submit()" class="input w-40">
            <option value="">All statuses</option>
            @foreach(['pending' => 'Pending','in_progress' => 'In Progress','waiting_parts' => 'Waiting Parts','completed' => 'Completed','cancelled' => 'Cancelled'] as $val => $lbl)
            <option value="{{ $val }}" @selected(request('status') === $val)>{{ $lbl }}</option>
            @endforeach
        </select>
        <select name="priority" onchange="this.form.submit()" class="input w-36">
            <option value="">All priorities</option>
            @foreach(['urgent' => 'Urgent','high' => 'High','normal' => 'Normal','low' => 'Low'] as $val => $lbl)
            <option value="{{ $val }}" @selected(request('priority') === $val)>{{ $lbl }}</option>
            @endforeach
        </select>
        <select name="staff_id" onchange="this.form.submit()" class="input w-44">
            <option value="">All technicians</option>
            @foreach($staff as $s)
            <option value="{{ $s->id }}" @selected(request('staff_id') == $s->id)>{{ $s->user->name }}</option>
            @endforeach
        </select>
        <a href="{{ route('jobs.create') }}" class="ms-auto btn-primary">+ New Job Order</a>
    </form>

    <div class="card overflow-hidden">
        {{-- Desktop --}}
        <div class="hidden sm:block overflow-x-auto">
            <table class="table">
                <thead><tr>
                    <th>Job #</th><th>Customer / Vehicle</th><th>Status</th><th>Priority</th><th>Technician</th><th>Promised</th><th class="text-end">Total</th><th></th>
                </tr></thead>
                <tbody>
                    @forelse($jobs as $job)
                    <tr class="cursor-pointer" onclick="location='{{ route('jobs.show', $job) }}'">
                        <td class="font-mono text-xs font-semibold text-orange-500">{{ $job->job_number }}</td>
                        <td>
                            <p class="font-medium text-gray-900">{{ $job->customer?->name }}</p>
                            <p class="text-xs text-gray-400">{{ $job->vehicle?->make }} {{ $job->vehicle?->model }} · {{ $job->vehicle?->plate_number }}</p>
                        </td>
                        <td>@include('components.status-badge', ['status' => $job->status])</td>
                        <td>@include('components.status-badge', ['status' => $job->priority])</td>
                        <td class="text-gray-500 text-xs">{{ $job->staff?->user?->name ?? '—' }}</td>
                        <td class="text-gray-500 text-xs">{{ $job->promised_at?->format('d M Y') ?? '—' }}</td>
                        <td class="text-end font-semibold text-gray-900 text-xs">{{ number_format($job->total, 3) }} OMR</td>
                        <td><a href="{{ route('jobs.show', $job) }}" class="text-xs text-orange-500 hover:text-orange-600" onclick="event.stopPropagation()">View →</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="py-12 text-center text-sm text-gray-400">No job orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile --}}
        <div class="sm:hidden divide-y divide-gray-50">
            @forelse($jobs as $job)
            <a href="{{ route('jobs.show', $job) }}" class="flex items-start gap-3 p-4 hover:bg-gray-50 transition">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-mono text-xs font-semibold text-orange-500">{{ $job->job_number }}</span>
                        @include('components.status-badge', ['status' => $job->status])
                    </div>
                    <p class="text-sm font-medium text-gray-900 truncate">{{ $job->customer?->name }}</p>
                    <p class="text-xs text-gray-400">{{ $job->vehicle?->make }} {{ $job->vehicle?->model }} · {{ $job->vehicle?->plate_number }}</p>
                </div>
                <div class="text-end shrink-0">
                    <p class="font-bold text-gray-900">{{ number_format($job->total, 3) }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $job->staff?->user?->name ?? 'Unassigned' }}</p>
                </div>
            </a>
            @empty
            <p class="py-12 text-center text-sm text-gray-400">No job orders found.</p>
            @endforelse
        </div>
    </div>

    @include('components.pagination', ['paginator' => $jobs])

</x-layouts.app>
