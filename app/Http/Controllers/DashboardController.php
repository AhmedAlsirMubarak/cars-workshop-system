<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\JobOrder;
use App\Models\Invoice;
use App\Models\Part;
use App\Models\Appointment;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard/Index', [
            'stats' => [
                'open_jobs' => JobOrder::whereIn('status', ['pending', 'in_progress'])->count(),

                'jobs_today' => JobOrder::whereDate('created_at', today())->count(),

                'revenue_month' => Invoice::where('status', 'paid')
                    ->whereMonth('issued_at', now()->month)
                    ->sum('total'),

                'low_stock_parts' => Part::whereColumn('quantity_in_stock', '<=', 'reorder_level')->count(),

                'appointments_today' => Appointment::whereDate('scheduled_at', today())
                    ->whereIn('status', ['scheduled', 'confirmed'])
                    ->count(),
            ],

            'recent_jobs' => JobOrder::with(['customer', 'vehicle', 'staff.user'])
                ->latest()
                ->limit(8)
                ->get(),

            'upcoming_appointments' => Appointment::with(['customer', 'vehicle', 'staff.user'])
                ->where('scheduled_at', '>=', now())
                ->orderBy('scheduled_at')
                ->limit(5)
                ->get(),
        ]);
    }
}
