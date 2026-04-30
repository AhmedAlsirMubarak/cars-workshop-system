<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\Appointment;
use App\Models\JobOrder;
use App\Models\Staff;

class AppointmentController extends Controller
{
    public function index(Request $request): Response
    {
        $appointments = Appointment::with(['customer', 'vehicle', 'staff.user'])
            ->when($request->date,     fn ($q, $d)  => $q->whereDate('scheduled_at', $d))
            ->when($request->status,   fn ($q, $s)  => $q->where('status', $s))
            ->when($request->staff_id, fn ($q, $id) => $q->where('staff_id', $id))
            ->orderBy('scheduled_at')
            ->paginate(20)
            ->withQueryString();

        $staff = Staff::with('user')->where('status', 'active')->get();

        return Inertia::render('Appointments/Index', compact('appointments', 'staff'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id'      => 'required|exists:customers,id',
            'vehicle_id'       => 'required|exists:vehicles,id',
            'staff_id'         => 'nullable|exists:staff,id',
            'scheduled_at'     => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:15',
            'type'             => 'in:inspection,repair,maintenance,diagnostic,other',
            'description'      => 'nullable|string',
            'notes'            => 'nullable|string',
        ]);

        Appointment::create($data);

        return back()->with('success', __('app.appointments.created'));
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $data = $request->validate([
            'status'       => 'in:scheduled,confirmed,in_progress,completed,cancelled,no_show',
            'staff_id'     => 'nullable|exists:staff,id',
            'scheduled_at' => 'sometimes|date',
            'notes'        => 'nullable|string',
        ]);

        $appointment->update($data);

        return back()->with('success', __('app.appointments.updated'));
    }

    public function convertToJob(Appointment $appointment): RedirectResponse
    {
        if ($appointment->jobOrder) {
            return redirect()->route('jobs.show', $appointment->jobOrder);
        }

        $job = JobOrder::create([
            'customer_id'    => $appointment->customer_id,
            'vehicle_id'     => $appointment->vehicle_id,
            'staff_id'       => $appointment->staff_id,
            'appointment_id' => $appointment->id,
            'priority'       => 'normal',
            'complaint'      => $appointment->description,
        ]);

        $appointment->update(['status' => 'in_progress']);

        return redirect()
            ->route('jobs.show', $job)
            ->with('success', __('app.appointments.converted'));
    }
}
