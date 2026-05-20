<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RegistrasiPengunjung;
use Inertia\Inertia;

class RegistrasiPengunjungController extends Controller
{
    public function index(Request $request)
    {
        $registrasipengunjungs = RegistrasiPengunjung::when($request->search, function ($query, $search) {
                $query->where('nama_registrasipengunjung', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        // Pastikan folder di Vue-nya nanti bernama "RegistrasiPengunjung"
        return Inertia::render('admin/datamaster/registrasipengunjung/Index', [
            'registrasipengunjungs' => $registrasipengunjungs,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/datamaster/registrasipengunjung/Create');
    }

    public function store(Request $request)
    {
        RegistrasiPengunjung::create($request->validate([
            'nama_registrasipengunjung' => 'required|string|max:255',
        ]));

        return redirect()->route('registrasi-pengunjungs.index')->with('success', 'Jenis RegistrasiPengunjung berhasil ditambahkan.');
    }

    public function edit(RegistrasiPengunjung $registrasi_pengunjung) // Variabel otomatis dari Laravel
    {
        return Inertia::render('admin/datamaster/registrasipengunjung/Edit', ['registrasipengunjung' => $registrasi_pengunjung]);
    }

    public function update(Request $request, RegistrasiPengunjung $registrasi_pengunjung)
    {
        $registrasi_pengunjung->update($request->validate([
            'nama_registrasipengunjung' => 'required|string|max:255',
        ]));

        return redirect()->route('registrasi-pengunjungs.index')->with('success', 'Jenis RegistrasiPengunjung berhasil diperbarui.');
    }

    public function destroy(RegistrasiPengunjung $registrasi_pengunjung)
    {
        $registrasi_pengunjung->delete();
        return redirect()->route('registrasi-pengunjungs.index')->with('success', 'Jenis RegistrasiPengunjung berhasil dihapus.');
    }
}
