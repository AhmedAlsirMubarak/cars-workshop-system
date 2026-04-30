<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\Vehicle;

class VehicleController extends Controller
{
    public function index(Request $request): Response
    {
        $vehicles = Vehicle::with('customer')
            ->when($request->search, fn ($q, $s) => $q
                ->where('plate_number', 'like', "%{$s}%")
                ->orWhere('make', 'like', "%{$s}%")
                ->orWhere('model', 'like', "%{$s}%")
                ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$s}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Vehicles/Index', compact('vehicles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id'  => 'required|exists:customers,id',
            'make'         => 'required|string|max:100',
            'model'        => 'required|string|max:100',
            'year'         => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'color'        => 'nullable|string|max:50',
            'plate_number' => 'required|string|max:20|unique:vehicles',
            'vin'          => 'nullable|string|max:17|unique:vehicles',
            'engine_type'  => 'nullable|string|max:100',
            'mileage'      => 'nullable|integer|min:0',
            'notes'        => 'nullable|string',
        ]);

        $vehicle = Vehicle::create($data);

        return redirect()
            ->route('vehicles.show', $vehicle)
            ->with('success', __('app.vehicles.created'));
    }

    public function show(Vehicle $vehicle): Response
    {
        $vehicle->load(['customer', 'jobOrders.staff.user', 'appointments.staff.user']);

        return Inertia::render('Vehicles/Show', compact('vehicle'));
    }

    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $data = $request->validate([
            'make'         => 'required|string|max:100',
            'model'        => 'required|string|max:100',
            'year'         => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'color'        => 'nullable|string|max:50',
            'plate_number' => 'required|string|max:20|unique:vehicles,plate_number,' . $vehicle->id,
            'vin'          => 'nullable|string|max:17|unique:vehicles,vin,' . $vehicle->id,
            'mileage'      => 'nullable|integer|min:0',
        ]);

        $vehicle->update($data);

        return back()->with('success', __('app.vehicles.updated'));
    }
}
