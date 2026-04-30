<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\Part;

class InventoryController extends Controller
{
    public function index(Request $request): Response
    {
        $parts = Part::query()
            ->when($request->search, fn ($q, $s) => $q
                ->where('name', 'like', "%{$s}%")
                ->orWhere('sku', 'like', "%{$s}%"))
            ->when($request->category,  fn ($q, $c) => $q->where('category', $c))
            ->when($request->low_stock, fn ($q)     => $q->whereColumn('quantity_in_stock', '<=', 'reorder_level'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $categories = Part::distinct()->pluck('category')->filter()->values();

        return Inertia::render('Inventory/Index', compact('parts', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sku'               => 'required|string|unique:parts',
            'name'              => 'required|string|max:255',
            'description'       => 'nullable|string',
            'category'          => 'nullable|string|max:100',
            'brand'             => 'nullable|string|max:100',
            'cost_price'        => 'required|numeric|min:0',
            'selling_price'     => 'required|numeric|min:0',
            'quantity_in_stock' => 'required|integer|min:0',
            'reorder_level'     => 'required|integer|min:0',
            'location'          => 'nullable|string|max:100',
            'supplier'          => 'nullable|string|max:255',
        ]);

        Part::create($data);

        return back()->with('success', __('app.inventory.created'));
    }

    public function update(Request $request, Part $part): RedirectResponse
    {
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'cost_price'        => 'required|numeric|min:0',
            'selling_price'     => 'required|numeric|min:0',
            'quantity_in_stock' => 'required|integer|min:0',
            'reorder_level'     => 'required|integer|min:0',
            'category'          => 'nullable|string|max:100',
            'supplier'          => 'nullable|string|max:255',
            'location'          => 'nullable|string|max:100',
        ]);

        $part->update($data);

        return back()->with('success', __('app.inventory.updated'));
    }

    public function destroy(Part $part): RedirectResponse
    {
        $part->update(['is_active' => false]);

        return back()->with('success', __('app.inventory.deactivated'));
    }
}
