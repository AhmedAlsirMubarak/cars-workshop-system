<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use App\Models\JobOrder;
use App\Models\Customer;
use App\Models\Staff;
use App\Models\Part;

class JobOrderController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
    {
        $jobs = JobOrder::with(['customer', 'vehicle', 'staff.user'])
            ->when($request->status,   fn ($q, $s)  => $q->where('status', $s))
            ->when($request->priority, fn ($q, $p)  => $q->where('priority', $p))
            ->when($request->staff_id, fn ($q, $id) => $q->where('staff_id', $id))
            ->when($request->search,   fn ($q, $s)  => $q
                ->where('job_number', 'like', "%{$s}%")
                ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$s}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $staff = Staff::with('user')->where('status', 'active')->get();

        return view('jobs.index', compact('jobs', 'staff'));
    }

    public function create(Request $request): \Illuminate\View\View
    {
        return view('jobs.create', [
            'customers'           => Customer::with('vehicles')->select('id', 'name', 'phone')->get(),
            'staff'               => Staff::with('user')->where('status', 'active')->get(),
            'selected_vehicle_id' => $request->vehicle_id,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id'         => 'required|exists:customers,id',
            'vehicle_id'          => 'required|exists:vehicles,id',
            'staff_id'            => 'nullable|exists:staff,id',
            'appointment_id'      => 'nullable|exists:appointments,id',
            'priority'            => 'in:low,normal,high,urgent',
            'complaint'           => 'nullable|string',
            'diagnosis'           => 'nullable|string',
            'promised_at'         => 'nullable|date',
            'mileage_in'          => 'nullable|integer',
            'labour_cost'         => 'nullable|numeric|min:0',
            'items'               => 'array',
            'items.*.description' => 'required_with:items|string',
            'items.*.type'        => 'in:labour,part,service,other',
            'items.*.quantity'    => 'required_with:items|numeric|min:0',
            'items.*.unit_price'  => 'required_with:items|numeric|min:0',
        ]);

        $job = JobOrder::create($data);

        if (!empty($data['items'])) {
            $job->items()->createMany($data['items']);
        }

        return redirect()
            ->route('jobs.show', $job)
            ->with('success', __('app.jobs.created', ['number' => $job->job_number]));
    }

    public function show(JobOrder $job): \Illuminate\View\View
    {
        $job->load([
            'customer',
            'vehicle',
            'staff.user',
            'items',
            'parts.part',
            'invoice.payments',
        ]);

        $parts = Part::where('is_active', true)
            ->get(['id', 'sku', 'name', 'selling_price', 'quantity_in_stock']);

        return view('jobs.show', compact('job', 'parts'));
    }

    public function update(Request $request, JobOrder $job): RedirectResponse
    {
        $data = $request->validate([
            'status'          => 'in:pending,in_progress,waiting_parts,completed,cancelled',
            'priority'        => 'in:low,normal,high,urgent',
            'staff_id'        => 'nullable|exists:staff,id',
            'complaint'       => 'nullable|string',
            'diagnosis'       => 'nullable|string',
            'work_performed'  => 'nullable|string',
            'recommendations' => 'nullable|string',
            'labour_cost'     => 'nullable|numeric|min:0',
            'discount'        => 'nullable|numeric|min:0',
            'mileage_out'     => 'nullable|integer',
        ]);

        if (isset($data['status'])) {
            if ($data['status'] === 'in_progress' && !$job->started_at) {
                $data['started_at'] = now();
            }
            if ($data['status'] === 'completed' && !$job->completed_at) {
                $data['completed_at'] = now();
            }
        }

        $job->update($data);

        return back()->with('success', __('app.jobs.updated'));
    }

    public function addPart(Request $request, JobOrder $job): RedirectResponse
    {
        $data = $request->validate([
            'part_id'    => 'required|exists:parts,id',
            'quantity'   => 'required|numeric|min:0.01',
            'unit_price' => 'required|numeric|min:0',
        ]);

        $part = Part::findOrFail($data['part_id']);

        if ($part->quantity_in_stock < $data['quantity']) {
            return back()->withErrors(['quantity' => __('app.jobs.insufficient')]);
        }

        $job->parts()->create($data);

        $part->decrement('quantity_in_stock', $data['quantity']);

        $job->update([
            'parts_cost' => $job->parts()->sum(DB::raw('quantity * unit_price')),
        ]);

        return back()->with('success', __('app.jobs.part_added'));
    }
}
