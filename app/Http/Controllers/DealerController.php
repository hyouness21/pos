<?php

namespace App\Http\Controllers;

use App\Models\Dealer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DealerController extends Controller
{
    public function index(Request $request): View
    {
        $dealers = Dealer::withCount('purchases')
            ->withSum('purchases', 'total_amount')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->input('search') . '%'))
            ->latest()
            ->paginate(20)->withQueryString();

        return view('dealers.index', compact('dealers'));
    }

    public function create(): View
    {
        return view('dealers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'notes'   => 'nullable|string',
        ]);

        $dealer = Dealer::create($data);

        return redirect()->route('dealers.show', $dealer)->with('success', 'Dealer created.');
    }

    public function show(Dealer $dealer): View
    {
        $purchases = $dealer->purchases()->with('items.item')->latest()->paginate(15);
        return view('dealers.show', compact('dealer', 'purchases'));
    }

    public function edit(Dealer $dealer): View
    {
        return view('dealers.edit', compact('dealer'));
    }

    public function update(Request $request, Dealer $dealer): RedirectResponse
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'notes'   => 'nullable|string',
        ]);

        $dealer->update($data);

        return redirect()->route('dealers.show', $dealer)->with('success', 'Dealer updated.');
    }

    public function destroy(Dealer $dealer): RedirectResponse
    {
        $dealer->delete();
        return redirect()->route('dealers.index')->with('success', 'Dealer deleted.');
    }
}
