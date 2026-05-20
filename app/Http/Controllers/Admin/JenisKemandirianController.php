<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JenisKemandirian;
use Inertia\Inertia;

class JenisKemandirianController extends Controller
{
    public function index(Request $request)
    {
        $kemandirians = JenisKemandirian::when($request->search, function ($query, $search) {
                $query->where('nama_kemandirian', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        // Pastikan folder di Vue-nya nanti bernama "JenisKemandirian"
        return Inertia::render('admin/datamaster/jeniskemandirian/Index', [
            'kemandirians' => $kemandirians,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/datamaster/jeniskemandirian/Create');
    }

    public function store(Request $request)
    {
        JenisKemandirian::create($request->validate([
            'nama_kemandirian' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-kemandirians.index')->with('success', 'Jenis Kemandirian berhasil ditambahkan.');
    }

    public function edit(JenisKemandirian $jenis_kemandirian) // Variabel otomatis dari Laravel
    {
        return Inertia::render('admin/datamaster/jeniskemandirian/Edit', ['kemandirian' => $jenis_kemandirian]);
    }

    public function update(Request $request, JenisKemandirian $jenis_kemandirian)
    {
        $jenis_kemandirian->update($request->validate([
            'nama_kemandirian' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-kemandirians.index')->with('success', 'Jenis Kemandirian berhasil diperbarui.');
    }

    public function destroy(JenisKemandirian $jenis_kemandirian)
    {
        $jenis_kemandirian->delete();
        return redirect()->route('jenis-kemandirians.index')->with('success', 'Jenis Kemandirian berhasil dihapus.');
    }
}
