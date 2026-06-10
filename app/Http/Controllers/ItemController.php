<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    public function storehouse(Request $request): View
    {
        $search = $request->input('search');
        $categories = Category::with(['items' => function ($q) use ($search) {
            $q->orderBy('name');
            if ($search) {
                $q->where('name', 'like', "%{$search}%");
            }
        }])->orderBy('name')->get();

        if ($search) {
            $categories = $categories->filter(fn ($c) => $c->items->isNotEmpty());
        }

        $stats = [
            'total_skus'       => Item::count(),
            'total_units'      => Item::sum('stock'),
            'total_value'      => Item::selectRaw('SUM(stock * price) as val')->value('val') ?? 0,
            'total_cost'       => Item::whereNotNull('cost_price')->selectRaw('SUM(stock * cost_price) as val')->value('val') ?? 0,
            'low_stock'        => Item::whereColumn('stock', '<=', 'low_stock_threshold')->count(),
            'out_of_stock'     => Item::where('stock', 0)->count(),
            'expiring_soon'    => Item::whereNotNull('expiry_date')
                                      ->where('expiry_date', '<=', now()->addDays(30)->toDateString())
                                      ->count(),
        ];

        $activeCategory = $request->filled('category') ? $request->category : null;

        return view('items.storehouse', compact('categories', 'stats', 'activeCategory'));
    }

    public function index(Request $request): View
    {
        $query = Item::with('category');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->boolean('low_stock')) {
            $query->whereColumn('stock', '<=', 'low_stock_threshold');
        }

        $items      = $query->latest()->paginate(20)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('items.index', compact('items', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        return view('items.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id'        => 'required|exists:categories,id',
            'name'               => 'required|string|max:255',
            'cost_price'         => 'nullable|numeric|min:0',
            'price'              => 'required|numeric|min:0',
            'image'              => 'nullable|image|max:2048',
            'stock'              => 'required|integer|min:0',
            'low_stock_threshold'=> 'required|integer|min:0',
            'expiry_date'        => 'nullable|date',
            'barcode'            => 'nullable|string|max:255|unique:items,barcode',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('items', 'public');
        }

        Item::create($data);

        return redirect()->route('items.index')->with('success', 'Item created.');
    }

    public function edit(Item $item): View
    {
        $categories = Category::orderBy('name')->get();
        return view('items.edit', compact('item', 'categories'));
    }

    public function update(Request $request, Item $item): RedirectResponse
    {
        $data = $request->validate([
            'category_id'        => 'required|exists:categories,id',
            'name'               => 'required|string|max:255',
            'cost_price'         => 'nullable|numeric|min:0',
            'price'              => 'required|numeric|min:0',
            'image'              => 'nullable|image|max:2048',
            'stock'              => 'required|integer|min:0',
            'low_stock_threshold'=> 'required|integer|min:0',
            'expiry_date'        => 'nullable|date',
            'barcode'            => 'nullable|string|max:255|unique:items,barcode,' . $item->id,
        ]);

        if ($request->hasFile('image')) {
            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }
            $data['image'] = $request->file('image')->store('items', 'public');
        }

        $item->update($data);

        return redirect()->route('items.index')->with('success', 'Item updated.');
    }

    public function destroy(Item $item): RedirectResponse
    {
        if ($item->image) {
            Storage::disk('public')->delete($item->image);
        }
        $item->delete();

        return redirect()->route('items.index')->with('success', 'Item deleted.');
    }

    public function updateStock(Request $request, Item $item): RedirectResponse
    {
        $data = $request->validate(['stock' => 'required|integer|min:0']);
        $item->update($data);

        return back()->with('success', 'Stock updated.');
    }
}
