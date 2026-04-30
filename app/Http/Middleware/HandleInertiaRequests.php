<?php
// app/Http/Middleware/HandleInertiaRequests.php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Part;
use App\Models\JobOrder;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),

            'auth' => [
                'user'        => $request->user(),
                'permissions' => $request->user()?->getAllPermissions()->pluck('name'),
                'roles'       => $request->user()?->getRoleNames(),
            ],

            'flash' => [
                'success' => session('success'),
                'error'   => session('error'),
            ],

            // Shared global counts for sidebar badges
            'lowStockCount' => fn() => $request->user()
                ? Part::whereColumn('quantity_in_stock', '<=', 'reorder_level')->count()
                : 0,

            'openJobCount' => fn() => $request->user()
                ? JobOrder::whereIn('status', ['pending', 'in_progress', 'waiting_parts'])->count()
                : 0,
        ];
    }
}
