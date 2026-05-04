<x-layouts.app title="{{ __('Dashboard') }}">

    {{-- Greeting --}}
    <div class="mb-6">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-900">
            @php
                $hour = now()->hour;
                $greeting = $hour < 12 ? __('Good morning') : ($hour < 17 ? __('Good afternoon') : __('Good evening'));
            @endphp
            {{ $greeting }}, {{ explode(' ', auth()->user()->name)[0] }} 👋
        </h2>
        <p class="text-sm text-gray-500 mt-0.5">
            {{ now()->format('l, d F Y') }} · {{ __("Here's what's happening at the workshop today.") }}
        </p>
    </div>

    {{-- KPI cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-3 sm:gap-4 mb-6 sm:mb-8">
        @foreach([
            ['label' => __('Open Jobs'),        'value' => $stats['open_jobs'],          'color' => 'blue',   'href' => route('jobs.index', ['status' => 'in_progress'])],
            ['label' => __('Jobs Today'),        'value' => $stats['jobs_today'],         'color' => 'purple', 'href' => route('jobs.index')],
            ['label' => __('Revenue This Month'),'value' => number_format($stats['revenue_month'], 3).' OMR', 'color' => 'green',  'href' => route('invoices.index', ['status' => 'paid'])],
            ['label' => __('Appointments Today'),'value' => $stats['appointments_today'], 'color' => 'orange', 'href' => route('appointments.index')],
            ['label' => __('Low Stock Parts'),   'value' => $stats['low_stock_parts'],    'color' => $stats['low_stock_parts'] > 0 ? 'red' : 'gray', 'href' => route('inventory.index', ['low_stock' => 1])],
        ] as $kpi)
        @php
            $colors = [
                'blue'   => 'bg-blue-50 text-blue-500',
                'purple' => 'bg-purple-50 text-purple-500',
                'green'  => 'bg-green-50 text-green-500',
                'orange' => 'bg-orange-50 text-orange-500',
                'red'    => 'bg-red-50 text-red-500',
                'gray'   => 'bg-gray-50 text-gray-400',
            ];
        @endphp
        <a href="{{ $kpi['href'] }}"
            class="card p-4 sm:p-5 flex flex-col gap-3 hover:border-[#FEE103] hover:shadow-sm transition {{ $loop->index === 2 ? 'col-span-2 sm:col-span-1' : '' }}">
            <div class="flex items-start justify-between gap-2">
                <span class="text-xs text-gray-400 font-medium leading-tight">{{ $kpi['label'] }}</span>
                <div class="w-8 h-8 rounded-xl {{ $colors[$kpi['color']] }} flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
            <p class="text-xl sm:text-2xl font-bold text-gray-900 leading-none">{{ $kpi['value'] }}</p>
        </a>
        @endforeach
    </div>

    {{-- Main grid --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-6">

        {{-- Recent job orders --}}
        <div class="xl:col-span-2 card flex flex-col">
            <div class="card-header">
                <h2 class="font-semibold text-gray-900">{{ __('Recent Job Orders') }}</h2>
                <a href="{{ route('jobs.create') }}" class="btn-primary btn-sm">+ {{ __('New Job') }}</a>
            </div>

            {{-- Desktop table --}}
            <div class="hidden sm:block overflow-x-auto">
                <table class="table">
                    <thead><tr>
                        <th>{{ __('Job #') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Vehicle') }}</th><th>{{ __('Status') }}</th><th>{{ __('Technician') }}</th><th class="text-end">{{ __('Total') }}</th>
                    </tr></thead>
                    <tbody>
                        @forelse($recent_jobs as $job)
                        <tr class="cursor-pointer" onclick="location='{{ route('jobs.show', $job) }}'">
                            <td class="font-mono text-xs font-semibold text-[#FEE103]">{{ $job->job_number }}</td>
                            <td class="font-medium text-gray-900 max-w-[130px] truncate">{{ $job->customer?->name }}</td>
                            <td class="text-gray-500 text-xs">{{ $job->vehicle?->make }} {{ $job->vehicle?->model }}<br><span class="font-mono">{{ $job->vehicle?->plate_number }}</span></td>
                            <td>@include('components.status-badge', ['status' => $job->status])</td>
                            <td class="text-gray-500 text-xs">
                                @if($job->assignedStaff->isNotEmpty())
                                {{ $job->assignedStaff->first()->display_name }}{{ $job->assignedStaff->count() > 1 ? ' +'.($job->assignedStaff->count()-1) : '' }}
                                @else —
                                @endif
                            </td>
                            <td class="text-end font-bold text-gray-900 text-xs">{{ number_format($job->total, 3) }} OMR</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="py-12 text-center text-sm text-gray-400">
                            {{ __('No job orders yet.') }} <a href="{{ route('jobs.create') }}" class="text-[#FEE103] hover:underline">{{ __('Create one') }} →</a>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile cards --}}
            <div class="sm:hidden divide-y divide-gray-50">
                @forelse($recent_jobs as $job)
                <a href="{{ route('jobs.show', $job) }}" class="flex items-start gap-3 px-4 py-3.5 hover:bg-gray-50 transition">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-mono text-xs font-semibold text-[#FEE103]">{{ $job->job_number }}</span>
                            @include('components.status-badge', ['status' => $job->status])
                        </div>
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $job->customer?->name }}</p>
                        <p class="text-xs text-gray-400">{{ $job->vehicle?->make }} {{ $job->vehicle?->model }} · {{ $job->vehicle?->plate_number }}</p>
                    </div>
                    <div class="text-end shrink-0">
                        <p class="font-bold text-gray-900">{{ number_format($job->total, 3) }}</p>
                        <p class="text-xs text-gray-400">OMR</p>
                    </div>
                </a>
                @empty
                <p class="px-4 py-10 text-center text-sm text-gray-400">{{ __('No job orders yet.') }}</p>
                @endforelse
            </div>

            <div class="px-5 py-3 border-t border-gray-50">
                <a href="{{ route('jobs.index') }}" class="text-xs text-orange-500 hover:text-orange-600 font-medium">{{ __('View all job orders') }} →</a>
            </div>
        </div>

        {{-- Right column --}}
        <div class="space-y-4 sm:space-y-6">

            {{-- Upcoming appointments --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold text-gray-900 text-sm sm:text-base">{{ __('Upcoming Today') }}</h2>
                    <a href="{{ route('appointments.index') }}" class="text-xs text-orange-500 hover:underline">{{ __('View all') }}</a>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($upcoming_appointments as $apt)
                    <div class="px-5 py-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $apt->customer?->name }}</p>
                                <p class="text-xs text-gray-500 truncate mt-0.5">{{ $apt->vehicle?->make }} {{ $apt->vehicle?->model }} · <span class="capitalize">{{ $apt->type }}</span></p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $apt->staff?->user?->name ?? __('Unassigned') }}</p>
                            </div>
                            <div class="text-end shrink-0">
                                <p class="text-sm font-bold text-[#FEE103]">{{ $apt->scheduled_at->format('H:i') }}</p>
                                <span class="{{ $apt->status === 'confirmed' ? 'badge-teal' : 'badge-gray' }} text-xs mt-1 inline-block">{{ $apt->status }}</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="px-5 py-10 text-center text-sm text-gray-400">{{ __('No appointments today.') }}</p>
                    @endforelse
                </div>
                <div class="px-5 py-3 border-t border-gray-50">
                    <a href="{{ route('appointments.index') }}" class="text-xs text-[#FEE103] hover:text-[#FEE103]/80 font-medium">+ {{ __('Schedule appointment') }} →</a>
                </div>
            </div>

            {{-- Quick actions --}}
            <div class="card p-4 sm:p-6">
                <h2 class="font-semibold text-gray-900 text-sm sm:text-base mb-4">{{ __('Quick Actions') }}</h2>
                <div class="grid grid-cols-2 gap-2 sm:gap-3">
                    @foreach([
                        ['href' => route('jobs.create'),         'label' => __('New Job Order'),   'color' => 'orange'],
                        ['href' => route('customers.create'),    'label' => __('Add Customer'),    'color' => 'blue'],
                        ['href' => route('appointments.index'),  'label' => __('Appointments'),    'color' => 'teal'],
                        ['href' => route('inventory.index'),     'label' => __('Inventory'),       'color' => 'amber'],
                    ] as $action)
                    @php
                        $qColors = [
                            'orange' => 'bg-orange-50 border-orange-100 hover:border-orange-300 text-orange-700',
                            'blue'   => 'bg-blue-50 border-blue-100 hover:border-blue-300 text-blue-700',
                            'teal'   => 'bg-teal-50 border-teal-100 hover:border-teal-300 text-teal-700',
                            'amber'  => 'bg-amber-50 border-amber-100 hover:border-amber-300 text-amber-700',
                        ];
                    @endphp
                    <a href="{{ $action['href'] }}"
                        class="flex flex-col items-center justify-center gap-2 p-3 sm:p-4 rounded-xl border transition-all text-center hover:shadow-sm hover:-translate-y-0.5 {{ $qColors[$action['color']] }}">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span class="text-xs font-medium leading-tight">{{ $action['label'] }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

</x-layouts.app>
