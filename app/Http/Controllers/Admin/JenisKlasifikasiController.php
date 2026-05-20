<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JenisKlasifikasi;
use Inertia\Inertia;

class JenisKlasifikasiController extends Controller
{
    public function index(Request $request)
    {
        $klasifikasis = JenisKlasifikasi::when($request->search, function ($query, $search) {
                $query->where('nama_klasifikasi', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        // Pastikan folder di Vue-nya nanti bernama "JenisKlasifikasi"
        return Inertia::render('admin/datamaster/jenisklasifikasi/Index', [
            'klasifikasis' => $klasifikasis,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/datamaster/jenisklasifikasi/Create');
    }

    public function store(Request $request)
    {
        JenisKlasifikasi::create($request->validate([
            'nama_klasifikasi' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-klasifikasis.index')->with('success', 'Jenis Klasifikasi berhasil ditambahkan.');
    }

    public function edit(JenisKlasifikasi $jenis_klasifikasi) // Variabel otomatis dari Laravel
    {
        return Inertia::render('admin/datamaster/jenisklasifikasi/Edit', ['klasifikasi' => $jenis_klasifikasi]);
    }

    public function update(Request $request, JenisKlasifikasi $jenis_klasifikasi)
    {
        $jenis_klasifikasi->update($request->validate([
            'nama_klasifikasi' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-klasifikasis.index')->with('success', 'Jenis Klasifikasi berhasil diperbarui.');
    }

    public function destroy(JenisKlasifikasi $jenis_klasifikasi)
    {
        $jenis_klasifikasi->delete();
        return redirect()->route('jenis-klasifikasis.index')->with('success', 'Jenis Klasifikasi berhasil dihapus.');
    }
}
