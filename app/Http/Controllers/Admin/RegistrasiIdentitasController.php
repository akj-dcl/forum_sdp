<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RegistrasiIdentitas;
use Inertia\Inertia;

class RegistrasiIdentitasController extends Controller
{
    public function index(Request $request)
    {
        $registrasiidentitases = RegistrasiIdentitas::when($request->search, function ($query, $search) {
                $query->where('nama_registrasiidentitas', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        // Pastikan folder di Vue-nya nanti bernama "RegistrasiIdentitas"
        return Inertia::render('admin/datamaster/registrasiidentitas/Index', [
            'registrasiidentitases' => $registrasiidentitases,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/datamaster/registrasiidentitas/Create');
    }

    public function store(Request $request)
    {
        RegistrasiIdentitas::create($request->validate([
            'nama_registrasiidentitas' => 'required|string|max:255',
        ]));

        return redirect()->route('registrasi-identitases.index')->with('success', 'Registrasiidentitas berhasil ditambahkan.');
    }

    public function edit(RegistrasiIdentitas $registrasi_identitas) // Variabel otomatis dari Laravel
    {
        return Inertia::render('admin/datamaster/registrasiidentitas/Edit', ['registrasiidentitas' => $registrasi_identitas]);
    }

    public function update(Request $request, RegistrasiIdentitas $registrasi_identitas)
    {
        $registrasi_identitas->update($request->validate([
            'nama_registrasiidentitas' => 'required|string|max:255',
        ]));

        return redirect()->route('registrasi-identitases.index')->with('success', 'Registrasiidentitas berhasil diperbarui.');
    }

    public function destroy(RegistrasiIdentitas $registrasi_identitas)
    {
        $registrasi_identitas->delete();
        return redirect()->route('registrasi-identitases.index')->with('success', 'Registrasiidentitas berhasil dihapus.');
    }
}
