<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\Staff;
use App\Models\User;

class StaffController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
    {
        $staff = Staff::with('user')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->search, function ($q, $s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$s}%"));
            })
            ->withCount(['assignedJobs as total_jobs'])
            ->get();

        return view('staff.index', compact('staff'));
    }

    public function store(Request $request): RedirectResponse
    {
        $needsLogin = in_array($request->role, ['admin', 'manager']);

        $rules = [
            'name'           => 'required|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'role'           => 'required|in:admin,manager,technician,receptionist',
            'employee_id'    => 'required|string|unique:staff',
            'specialization' => 'nullable|string|max:100',
            'basic_salary'   => 'nullable|numeric|min:0',
            'hired_at'       => 'nullable|date',
        ];

        if ($needsLogin) {
            $rules['email']    = 'required|email|unique:users';
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $data = $request->validate($rules);

        $userId = null;

        if ($needsLogin) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'phone'    => $data['phone'] ?? null,
                'password' => bcrypt($data['password']),
            ]);
            $user->assignRole($data['role']);
            $userId = $user->id;
        }

        Staff::create([
            'user_id'        => $userId,
            'name'           => $data['name'],
            'phone'          => $data['phone'] ?? null,
            'role'           => $data['role'],
            'employee_id'    => $data['employee_id'],
            'specialization' => $data['specialization'] ?? null,
            'basic_salary'   => $data['basic_salary'] ?? 0,
            'hired_at'       => $data['hired_at'] ?? null,
        ]);

        return back()->with('success', __('Staff member added successfully.'));
    }

    public function show(Staff $staff): \Illuminate\View\View
    {
        $staff->load(['user', 'assignedJobs.customer', 'assignedJobs.vehicle']);

        $metrics = [
            'jobs_completed'    => $staff->assignedJobs()->where('status', 'completed')->count(),
            'jobs_in_progress'  => $staff->assignedJobs()->where('status', 'in_progress')->count(),
            'revenue_generated' => $staff->assignedJobs()
                ->join('invoices', 'job_orders.id', '=', 'invoices.job_order_id')
                ->where('invoices.status', 'paid')
                ->sum('invoices.total'),
        ];

        return view('staff.show', compact('staff', 'metrics'));
    }

    public function update(Request $request, Staff $staff): RedirectResponse
    {
        $data = $request->validate([
            'specialization' => 'nullable|string|max:100',
            'basic_salary'   => 'nullable|numeric|min:0',
            'status'         => 'in:active,on_leave,inactive',
            'notes'          => 'nullable|string',
        ]);

        $staff->update($data);

        return back()->with('success', __('Staff updated successfully.'));
    }
}
