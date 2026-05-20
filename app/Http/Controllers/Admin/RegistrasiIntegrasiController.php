<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RegistrasiIntegrasi;
use Inertia\Inertia;

class RegistrasiIntegrasiController extends Controller
{
    public function index(Request $request)
    {
        $registrasiintegrasis = RegistrasiIntegrasi::when($request->search, function ($query, $search) {
                $query->where('nama_registrasiintegrasi', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        // Pastikan folder di Vue-nya nanti bernama "RegistrasiIntegrasi"
        return Inertia::render('admin/datamaster/registrasiintegrasi/Index', [
            'registrasiintegrasis' => $registrasiintegrasis,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/datamaster/registrasiintegrasi/Create');
    }

    public function store(Request $request)
    {
        RegistrasiIntegrasi::create($request->validate([
            'nama_registrasiintegrasi' => 'required|string|max:255',
        ]));

        return redirect()->route('registrasi-integrasis.index')->with('success', 'Jenis RegistrasiIntegrasi berhasil ditambahkan.');
    }

    public function edit(RegistrasiIntegrasi $registrasi_integrasi) // Variabel otomatis dari Laravel
    {
        return Inertia::render('admin/datamaster/registrasiintegrasi/Edit', ['registrasiintegrasi' => $registrasi_integrasi]);
    }

    public function update(Request $request, RegistrasiIntegrasi $registrasi_integrasi)
    {
        $registrasi_integrasi->update($request->validate([
            'nama_registrasiintegrasi' => 'required|string|max:255',
        ]));

        return redirect()->route('registrasi-integrasis.index')->with('success', 'Jenis RegistrasiIntegrasi berhasil diperbarui.');
    }

    public function destroy(RegistrasiIntegrasi $registrasi_integrasi)
    {
        $registrasi_integrasi->delete();
        return redirect()->route('registrasi-integrasis.index')->with('success', 'Jenis RegistrasiIntegrasi berhasil dihapus.');
    }
}
