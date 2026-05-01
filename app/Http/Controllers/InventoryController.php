<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Part;

class InventoryController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
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

        return view('inventory.index', compact('parts', 'categories'));
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

    public function export(Request $request): StreamedResponse
    {
        $parts = Part::query()
            ->when($request->search,   fn ($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('sku', 'like', "%{$s}%"))
            ->when($request->category, fn ($q, $c) => $q->where('category', $c))
            ->when($request->low_stock, fn ($q)    => $q->whereColumn('quantity_in_stock', '<=', 'reorder_level'))
            ->orderBy('name')
            ->get();

        $filename = 'inventory-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($parts) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['SKU', 'Name', 'Brand', 'Category', 'Cost (OMR)', 'Selling (OMR)', 'Stock Qty', 'Reorder Level', 'Status', 'Location', 'Supplier']);

            foreach ($parts as $part) {
                fputcsv($handle, [
                    $part->sku,
                    $part->name,
                    $part->brand ?? '',
                    $part->category ?? '',
                    number_format($part->cost_price, 3),
                    number_format($part->selling_price, 3),
                    $part->quantity_in_stock,
                    $part->reorder_level,
                    $part->quantity_in_stock <= $part->reorder_level ? 'Low Stock' : 'OK',
                    $part->location ?? '',
                    $part->supplier ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function destroy(Part $part): RedirectResponse
    {
        $part->delete();

        return back()->with('success', 'Part deleted successfully.');
    }
}
