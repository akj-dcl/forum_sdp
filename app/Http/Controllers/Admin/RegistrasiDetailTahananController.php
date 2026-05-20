<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RegistrasiDetailTahanan;
use Inertia\Inertia;

class RegistrasiDetailTahananController extends Controller
{
    public function index(Request $request)
    {
        $registrasidetailtahanans = RegistrasiDetailTahanan::when($request->search, function ($query, $search) {
                $query->where('nama_registrasidetailtahanan', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        // Pastikan folder di Vue-nya nanti bernama "RegistrasiDetailTahanan"
        return Inertia::render('admin/datamaster/registrasidetailtahanan/Index', [
            'registrasidetailtahanans' => $registrasidetailtahanans,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/datamaster/registrasidetailtahanan/Create');
    }

    public function store(Request $request)
    {
        RegistrasiDetailTahanan::create($request->validate([
            'nama_registrasidetailtahanan' => 'required|string|max:255',
        ]));

        return redirect()->route('registrasi-detail-tahanans.index')->with('success', 'Jenis RegistrasiDetailTahanan berhasil ditambahkan.');
    }

    public function edit(RegistrasiDetailTahanan $registrasi_detailtahanan) // Variabel otomatis dari Laravel
    {
        return Inertia::render('admin/datamaster/registrasidetailtahanan/Edit', ['registrasidetailtahanan' => $registrasi_detailtahanan]);
    }

    public function update(Request $request, RegistrasiDetailTahanan $registrasi_detailtahanan)
    {
        $registrasi_detailtahanan->update($request->validate([
            'nama_registrasidetailtahanan' => 'required|string|max:255',
        ]));

        return redirect()->route('registrasi-detail-tahanans.index')->with('success', 'Jenis RegistrasiDetailTahanan berhasil diperbarui.');
    }

    public function destroy(RegistrasiDetailTahanan $registrasi_detailtahanan)
    {
        $registrasi_detailtahanan->delete();
        return redirect()->route('registrasi-detail-tahanans.index')->with('success', 'Jenis RegistrasiDetailTahanan berhasil dihapus.');
    }
}
