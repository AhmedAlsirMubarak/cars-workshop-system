<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use App\Models\JobOrder;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Staff;
use App\Models\Part;

class JobOrderController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
    {
        $jobs = JobOrder::with(['customer', 'vehicle', 'assignedStaff'])
            ->when($request->status,   fn ($q, $s)  => $q->where('status', $s))
            ->when($request->priority, fn ($q, $p)  => $q->where('priority', $p))
            ->when($request->staff_id, fn ($q, $id) => $q->whereHas('assignedStaff', fn ($s) => $s->where('staff.id', $id)))
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
        $isWalkIn = $request->boolean('walk_in');

        $rules = [
            'staff_ids'           => 'nullable|array',
            'staff_ids.*'         => 'exists:staff,id',
            'appointment_id'      => 'nullable|exists:appointments,id',
            'priority'            => 'in:low,normal,high,urgent',
            'complaint'           => 'nullable|string',
            'diagnosis'           => 'nullable|string',
            'promised_at'         => 'nullable|date',
            'mileage_in'          => 'nullable|integer',
            'labour_cost'         => 'nullable|numeric|min:0',
            'tax_rate'            => 'nullable|numeric|min:0|max:100',
            'items'               => 'array',
            'items.*.description' => 'required_with:items|string',
            'items.*.type'        => 'in:labour,part,service,other',
            'items.*.quantity'    => 'required_with:items|numeric|min:0',
            'items.*.unit_price'  => 'required_with:items|numeric|min:0',
        ];

        if ($isWalkIn) {
            $rules += [
                'guest_name'  => 'required|string|max:255',
                'guest_phone' => 'required|string|max:20',
                'guest_make'  => 'required|string|max:100',
                'guest_model' => 'required|string|max:100',
                'guest_year'  => 'nullable|integer|min:1900|max:' . (date('Y') + 2),
                'guest_plate' => 'required|string|max:20',
            ];
        } else {
            $rules += [
                'customer_id' => 'required|exists:customers,id',
                'vehicle_id'  => 'required|exists:vehicles,id',
            ];
        }

        $data = $request->validate($rules);

        if ($isWalkIn) {
            $customer = Customer::firstOrCreate(
                ['phone' => $data['guest_phone']],
                ['name'  => $data['guest_name']]
            );

            $vehicle = Vehicle::firstOrCreate(
                ['plate_number' => strtoupper($data['guest_plate'])],
                [
                    'customer_id' => $customer->id,
                    'make'        => $data['guest_make'],
                    'model'       => $data['guest_model'],
                    'year'        => $data['guest_year'] ?? (int) date('Y'),
                ]
            );

            $data['customer_id'] = $customer->id;
            $data['vehicle_id']  = $vehicle->id;
        }

        $staffIds = $data['staff_ids'] ?? [];
        unset($data['staff_ids']);

        $job = JobOrder::create($data);

        if (!empty($staffIds)) {
            $job->assignedStaff()->sync($staffIds);
        }

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
            'assignedStaff',
            'items',
            'parts.part',
            'invoice.payments',
        ]);

        $parts = Part::where('is_active', true)
            ->get(['id', 'sku', 'name', 'selling_price', 'quantity_in_stock']);

        return view('jobs.show', compact('job', 'parts'));
    }

    public function edit(JobOrder $job): \Illuminate\View\View
    {
        $job->load(['customer.vehicles', 'assignedStaff', 'items']);

        return view('jobs.edit', [
            'job'       => $job,
            'customers' => Customer::with('vehicles')->select('id', 'name', 'phone')->get(),
            'staff'     => Staff::with('user')->where('status', 'active')->get(),
        ]);
    }

    public function update(Request $request, JobOrder $job): RedirectResponse
    {
        $data = $request->validate([
            'customer_id'         => 'sometimes|exists:customers,id',
            'vehicle_id'          => 'sometimes|exists:vehicles,id',
            'status'              => 'in:pending,in_progress,waiting_parts,completed,cancelled',
            'priority'            => 'in:low,normal,high,urgent',
            'staff_ids'           => 'nullable|array',
            'staff_ids.*'         => 'exists:staff,id',
            'complaint'           => 'nullable|string',
            'diagnosis'           => 'nullable|string',
            'work_performed'      => 'nullable|string',
            'recommendations'     => 'nullable|string',
            'labour_cost'         => 'nullable|numeric|min:0',
            'discount'            => 'nullable|numeric|min:0',
            'tax_rate'            => 'nullable|numeric|min:0|max:100',
            'promised_at'         => 'nullable|date',
            'mileage_in'          => 'nullable|integer',
            'mileage_out'         => 'nullable|integer',
            'items'               => 'array',
            'items.*.description' => 'required_with:items|string',
            'items.*.type'        => 'in:labour,part,service,other',
            'items.*.quantity'    => 'required_with:items|numeric|min:0',
            'items.*.unit_price'  => 'required_with:items|numeric|min:0',
        ]);

        if (isset($data['status'])) {
            if ($data['status'] === 'in_progress' && !$job->started_at) {
                $data['started_at'] = now();
            }
            if ($data['status'] === 'completed' && !$job->completed_at) {
                $data['completed_at'] = now();
            }
        }

        $staffIds = $data['staff_ids'] ?? null;
        unset($data['staff_ids']);

        if ($request->has('items')) {
            $job->items()->delete();
            if (!empty($data['items'])) {
                $job->items()->createMany($data['items']);
            }
            unset($data['items']);
        }

        $job->update($data);

        if ($request->has('staff_ids')) {
            $job->assignedStaff()->sync($staffIds ?? []);
        }

        return $request->boolean('from_edit')
            ? redirect()->route('jobs.show', $job)->with('success', __('app.jobs.updated'))
            : back()->with('success', __('app.jobs.updated'));
    }

    public function destroy(JobOrder $job): RedirectResponse
    {
        $job->delete();

        return redirect()->route('jobs.index')->with('success', 'Job order deleted.');
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
