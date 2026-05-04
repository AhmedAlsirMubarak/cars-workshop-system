<x-layouts.app title="{{ __('Customers') }}">
    <form method="GET" action="{{ route('customers.index') }}" class="flex flex-wrap items-center gap-3 mb-6">
        <div x-data="searchBox()">
            <input type="text" name="search" x-model="query" @input="debounce()"
                placeholder="{{ __('Search by name, phone or email…') }}" class="input w-64">
        </div>
        <select name="status" onchange="this.form.submit()" class="input w-36">
            <option value="">{{ __('All statuses') }}</option>
            <option value="active" @selected(request('status')==='active')>{{ __('Active') }}</option>
            <option value="inactive" @selected(request('status')==='inactive')>{{ __('Inactive') }}</option>
        </select>
        <a href="{{ route('customers.create') }}" class="ms-auto btn-primary">+ {{ __('New Customer') }}</a>
    </form>

    <div class="card overflow-hidden">
        <div class="hidden sm:block overflow-x-auto">
            <table class="table">
                <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Phone') }}</th><th>{{ __('City') }}</th><th class="text-center">{{ __('Vehicles') }}</th><th class="text-center">{{ __('Jobs') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead>
                <tbody>
                    @forelse($customers as $c)
                    <tr class="cursor-pointer" onclick="location='{{ route('customers.show', $c) }}'">
                        <td>
                            <p class="font-medium text-gray-900">{{ $c->name }}</p>
                            <p class="text-xs text-gray-400">{{ $c->email ?? '—' }}</p>
                        </td>
                        <td class="text-gray-600">{{ $c->phone }}</td>
                        <td class="text-gray-500 text-xs">{{ $c->city ?? '—' }}</td>
                        <td class="text-center font-medium text-gray-700">{{ $c->vehicles_count }}</td>
                        <td class="text-center font-medium text-gray-700">{{ $c->job_orders_count }}</td>
                        <td>@include('components.status-badge', ['status' => $c->status])</td>
                        <td class="text-end">
                            <a href="{{ route('customers.show', $c) }}" class="text-xs text-orange-500 hover:underline" onclick="event.stopPropagation()">{{ __('View') }} →</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="py-12 text-center text-sm text-gray-400">{{ __('No customers found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="sm:hidden divide-y divide-gray-50">
            @forelse($customers as $c)
            <a href="{{ route('customers.show', $c) }}" class="flex items-start justify-between gap-3 p-4 hover:bg-gray-50 transition">
                <div>
                    <p class="font-medium text-gray-900">{{ $c->name }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $c->phone }}</p>
                </div>
                <div class="text-end shrink-0">
                    @include('components.status-badge', ['status' => $c->status])
                    <p class="text-xs text-gray-400 mt-1">{{ $c->vehicles_count }} {{ __('vehicles') }} · {{ $c->job_orders_count }} {{ __('jobs') }}</p>
                </div>
            </a>
            @empty
            <p class="py-12 text-center text-sm text-gray-400">{{ __('No customers found.') }}</p>
            @endforelse
        </div>
    </div>
    @include('components.pagination', ['paginator' => $customers])
</x-layouts.app>
