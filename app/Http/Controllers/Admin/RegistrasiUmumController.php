<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RegistrasiUmum;
use Inertia\Inertia;

class RegistrasiUmumController extends Controller
{
    public function index(Request $request)
    {
        $registrasiumums = RegistrasiUmum::when($request->search, function ($query, $search) {
                $query->where('nama_registrasiumum', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        // Pastikan folder di Vue-nya nanti bernama "RegistrasiUmum"
        return Inertia::render('admin/datamaster/registrasiumum/Index', [
            'registrasiumums' => $registrasiumums,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/datamaster/registrasiumum/Create');
    }

    public function store(Request $request)
    {
        RegistrasiUmum::create($request->validate([
            'nama_registrasiumum' => 'required|string|max:255',
        ]));

        return redirect()->route('registrasi-umums.index')->with('success', 'Jenis RegistrasiUmum berhasil ditambahkan.');
    }

    public function edit(RegistrasiUmum $registrasi_umum) // Variabel otomatis dari Laravel
    {
        return Inertia::render('admin/datamaster/registrasiumum/Edit', ['registrasiumum' => $registrasi_umum]);
    }

    public function update(Request $request, RegistrasiUmum $registrasi_umum)
    {
        $registrasi_umum->update($request->validate([
            'nama_registrasiumum' => 'required|string|max:255',
        ]));

        return redirect()->route('registrasi-umums.index')->with('success', 'Jenis RegistrasiUmum berhasil diperbarui.');
    }

    public function destroy(RegistrasiUmum $registrasi_umum)
    {
        $registrasi_umum->delete();
        return redirect()->route('registrasi-umums.index')->with('success', 'Jenis RegistrasiUmum berhasil dihapus.');
    }
}
