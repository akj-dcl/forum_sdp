<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CorporateHighlight;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CorporateHighlightController extends Controller
{
    public function index(Request $request)
    {
        $highlights = CorporateHighlight::when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/datamaster/corporatehighlight/Index', [
            'highlights' => $highlights,
            'filters' => $request->only(['search'])
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'link' => 'nullable|string|max:255',
        ]);

        CorporateHighlight::create($request->only(['title', 'description', 'link']));

        return redirect()->route('corporate-highlights.index')->with('success', 'Corporate Highlight berhasil dibuat.');
    }

    public function update(Request $request, CorporateHighlight $corporateHighlight)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'link' => 'nullable|string|max:255',
        ]);

        $corporateHighlight->update($request->only(['title', 'description', 'link']));

        return redirect()->route('corporate-highlights.index')->with('success', 'Corporate Highlight berhasil diperbarui.');
    }

    public function destroy(CorporateHighlight $corporateHighlight)
    {
        $corporateHighlight->delete();

        return redirect()->route('corporate-highlights.index')->with('success', 'Corporate Highlight berhasil dihapus.');
    }
}
