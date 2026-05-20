<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RegistrasiPetugas;
use Inertia\Inertia;

class RegistrasiPetugasController extends Controller
{
    public function index(Request $request)
    {
        $registrasipetugases = RegistrasiPetugas::when($request->search, function ($query, $search) {
                $query->where('nama_registrasipetugas', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        // Pastikan folder di Vue-nya nanti bernama "RegistrasiPetugas"
        return Inertia::render('admin/datamaster/registrasipetugas/Index', [
            'registrasipetugases' => $registrasipetugases,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/datamaster/registrasipetugas/Create');
    }

    public function store(Request $request)
    {
        RegistrasiPetugas::create($request->validate([
            'nama_registrasipetugas' => 'required|string|max:255',
        ]));

        return redirect()->route('registrasi-petugases.index')->with('success', 'Jenis RegistrasiPetugas berhasil ditambahkan.');
    }

    public function edit(RegistrasiPetugas $registrasi_petugas) // Variabel otomatis dari Laravel
    {
        return Inertia::render('admin/datamaster/registrasipetugas/Edit', ['registrasipetugas' => $registrasi_petugas]);
    }

    public function update(Request $request, RegistrasiPetugas $registrasi_petugas)
    {
        $registrasi_petugas->update($request->validate([
            'nama_registrasipetugas' => 'required|string|max:255',
        ]));

        return redirect()->route('registrasi-petugases.index')->with('success', 'Jenis RegistrasiPetugas berhasil diperbarui.');
    }

    public function destroy(RegistrasiPetugas $registrasi_petugas)
    {
        $registrasi_petugas->delete();
        return redirect()->route('registrasi-petugases.index')->with('success', 'Jenis RegistrasiPetugas berhasil dihapus.');
    }
}
