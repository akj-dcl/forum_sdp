<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JenisAgama;
use Inertia\Inertia;

class JenisAgamaController extends Controller
{
    public function index(Request $request)
    {
        $agamas = JenisAgama::when($request->search, function ($query, $search) {
                $query->where('nama_agama', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        // Pastikan folder di Vue-nya nanti bernama "JenisAgama"
        return Inertia::render('admin/datamaster/jenisagama/Index', [
            'agamas' => $agamas,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/datamaster/jenisagama/Create');
    }

    public function store(Request $request)
    {
        JenisAgama::create($request->validate([
            'nama_agama' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-agamas.index')->with('success', 'Jenis Agama berhasil ditambahkan.');
    }

    public function edit(JenisAgama $jenis_agama) // Variabel otomatis dari Laravel
    {
        return Inertia::render('admin/datamaster/jenisagama/Edit', ['agama' => $jenis_agama]);
    }

    public function update(Request $request, JenisAgama $jenis_agama)
    {
        $jenis_agama->update($request->validate([
            'nama_agama' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-agamas.index')->with('success', 'Jenis Agama berhasil diperbarui.');
    }

    public function destroy(JenisAgama $jenis_agama)
    {
        $jenis_agama->delete();
        return redirect()->route('jenis-agamas.index')->with('success', 'Jenis Agama berhasil dihapus.');
    }
}
