<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JenisPidana;
use Inertia\Inertia;

class JenisPidanaController extends Controller
{
    public function index(Request $request)
    {
        $pidanas = JenisPidana::when($request->search, function ($query, $search) {
                $query->where('nama_pidana', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        // Pastikan folder di Vue-nya nanti bernama "JenisKejahatan"
        return Inertia::render('admin/datamaster/jenispidana/Index', [
            'pidanas' => $pidanas,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/datamaster/jenispidana/Create');
    }

    public function store(Request $request)
    {
        JenisPidana::create($request->validate([
            'nama_pidana' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-pidanas.index')->with('success', 'Jenis Kejahatan berhasil ditambahkan.');
    }

    public function edit(JenisPidana $jenis_pidana) // Variabel otomatis dari Laravel
    {
        return Inertia::render('admin/datamaster/jenispidana/Edit', ['pidana' => $jenis_pidana]);
    }

    public function update(Request $request, JenisPidana $jenis_pidana)
    {
        $jenis_pidana->update($request->validate([
            'nama_pidana' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-pidanas.index')->with('success', 'Jenis Kejahatan berhasil diperbarui.');
    }

    public function destroy(JenisPidana $jenis_pidana)
    {
        $jenis_pidana->delete();
        return redirect()->route('jenis-pidanas.index')->with('success', 'Jenis Kejahatan berhasil dihapus.');
    }
}
