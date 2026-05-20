<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RegistrasiPidsus;
use Inertia\Inertia;

class RegistrasiPidsusController extends Controller
{
    public function index(Request $request)
    {
        $registrasipidsuses = RegistrasiPidsus::when($request->search, function ($query, $search) {
                $query->where('nama_registrasipidsus', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        // Pastikan folder di Vue-nya nanti bernama "RegistrasiPidsus"
        return Inertia::render('admin/datamaster/registrasipidsus/Index', [
            'registrasipidsuses' => $registrasipidsuses,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/datamaster/registrasipidsus/Create');
    }

    public function store(Request $request)
    {
        RegistrasiPidsus::create($request->validate([
            'nama_registrasipidsus' => 'required|string|max:255',
        ]));

        return redirect()->route('registrasi-pidsuses.index')->with('success', 'Jenis RegistrasiPidsus berhasil ditambahkan.');
    }

    public function edit(RegistrasiPidsus $registrasi_pidsus) // Variabel otomatis dari Laravel
    {
        return Inertia::render('admin/datamaster/registrasipidsus/Edit', ['registrasipidsus' => $registrasi_pidsus]);
    }

    public function update(Request $request, RegistrasiPidsus $registrasi_pidsus)
    {
        $registrasi_pidsus->update($request->validate([
            'nama_registrasipidsus' => 'required|string|max:255',
        ]));

        return redirect()->route('registrasi-pidsuses.index')->with('success', 'Jenis RegistrasiPidsus berhasil diperbarui.');
    }

    public function destroy(RegistrasiPidsus $registrasi_pidsus)
    {
        $registrasi_pidsus->delete();
        return redirect()->route('registrasi-pidsuses.index')->with('success', 'Jenis RegistrasiPidsus berhasil dihapus.');
    }
}
