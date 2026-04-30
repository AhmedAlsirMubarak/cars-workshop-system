<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\Staff;
use App\Models\User;

class StaffController extends Controller
{
    public function index(Request $request): Response
    {
        $staff = Staff::with('user')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->search, fn ($q, $s) => $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$s}%")))
            ->withCount(['jobOrders as total_jobs'])
            ->get();

        return Inertia::render('Staff/Index', compact('staff'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users',
            'phone'          => 'required|string|max:20',
            'password'       => 'required|string|min:8|confirmed',
            'role'           => 'required|in:admin,manager,technician',
            'employee_id'    => 'required|string|unique:staff',
            'specialization' => 'nullable|string|max:100',
            'hourly_rate'    => 'nullable|numeric|min:0',
            'hired_at'       => 'nullable|date',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'phone'    => $data['phone'],
            'password' => bcrypt($data['password']),
        ]);

        $user->assignRole($data['role']);

        Staff::create([
            'user_id'        => $user->id,
            'employee_id'    => $data['employee_id'],
            'specialization' => $data['specialization'] ?? null,
            'hourly_rate'    => $data['hourly_rate'] ?? 0,
            'hired_at'       => $data['hired_at'] ?? null,
        ]);

        return back()->with('success', __('app.staff.created'));
    }

    public function show(Staff $staff): Response
    {
        $staff->load(['user', 'jobOrders.customer', 'jobOrders.vehicle']);

        $metrics = [
            'jobs_completed'    => $staff->jobOrders()->where('status', 'completed')->count(),
            'jobs_in_progress'  => $staff->jobOrders()->where('status', 'in_progress')->count(),
            'revenue_generated' => $staff->jobOrders()
                ->join('invoices', 'job_orders.id', '=', 'invoices.job_order_id')
                ->where('invoices.status', 'paid')
                ->sum('invoices.total'),
        ];

        return Inertia::render('Staff/Show', compact('staff', 'metrics'));
    }

    public function update(Request $request, Staff $staff): RedirectResponse
    {
        $data = $request->validate([
            'specialization' => 'nullable|string|max:100',
            'hourly_rate'    => 'nullable|numeric|min:0',
            'status'         => 'in:active,on_leave,inactive',
            'notes'          => 'nullable|string',
        ]);

        $staff->update($data);

        return back()->with('success', __('app.staff.updated'));
    }
}
