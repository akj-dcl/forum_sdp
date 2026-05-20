<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JenisIntegrasi;
use Inertia\Inertia;

class JenisIntegrasiController extends Controller
{
    public function index(Request $request)
    {
        $integrasis = JenisIntegrasi::when($request->search, function ($query, $search) {
                $query->where('nama_integrasi', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        // Pastikan folder di Vue-nya nanti bernama "JenisIntegrasi"
        return Inertia::render('admin/datamaster/jenisintegrasi/Index', [
            'integrasis' => $integrasis,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/datamaster/jenisintegrasi/Create');
    }

    public function store(Request $request)
    {
        JenisIntegrasi::create($request->validate([
            'nama_integrasi' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-integrasis.index')->with('success', 'Jenis Integrasi berhasil ditambahkan.');
    }

    public function edit(JenisIntegrasi $jenis_integrasi) // Variabel otomatis dari Laravel
    {
        return Inertia::render('admin/datamaster/jenisintegrasi/Edit', ['integrasi' => $jenis_integrasi]);
    }

    public function update(Request $request, JenisIntegrasi $jenis_integrasi)
    {
        $jenis_integrasi->update($request->validate([
            'nama_integrasi' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-integrasis.index')->with('success', 'Jenis Integrasi berhasil diperbarui.');
    }

    public function destroy(JenisIntegrasi $jenis_integrasi)
    {
        $jenis_integrasi->delete();
        return redirect()->route('jenis-integrasis.index')->with('success', 'Jenis Integrasi berhasil dihapus.');
    }
}
