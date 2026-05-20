<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RegistrasiBarangTitipan;
use Inertia\Inertia;

class RegistrasiBarangTitipanController extends Controller
{
    public function index(Request $request)
    {
        $registrasibarangtitipans = RegistrasiBarangTitipan::when($request->search, function ($query, $search) {
                $query->where('nama_registrasibarangtitipan', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        // Pastikan folder di Vue-nya nanti bernama "RegistrasiBarangTitipan"
        return Inertia::render('admin/datamaster/registrasibarangtitipan/Index', [
            'registrasibarangtitipans' => $registrasibarangtitipans,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/datamaster/registrasibarangtitipan/Create');
    }

    public function store(Request $request)
    {
        RegistrasiBarangTitipan::create($request->validate([
            'nama_registrasibarangtitipan' => 'required|string|max:255',
        ]));

        return redirect()->route('registrasi-barang-titipans.index')->with('success', 'Jenis RegistrasiBarangTitipan berhasil ditambahkan.');
    }

    public function edit(RegistrasiBarangTitipan $registrasi_barangtitipan) // Variabel otomatis dari Laravel
    {
        return Inertia::render('admin/datamaster/registrasibarangtitipan/Edit', ['registrasibarangtitipan' => $registrasi_barangtitipan]);
    }

    public function update(Request $request, RegistrasiBarangTitipan $registrasi_barangtitipan)
    {
        $registrasi_barangtitipan->update($request->validate([
            'nama_registrasibarangtitipan' => 'required|string|max:255',
        ]));

        return redirect()->route('registrasi-barang-titipans.index')->with('success', 'Jenis RegistrasiBarangTitipan berhasil diperbarui.');
    }

    public function destroy(RegistrasiBarangTitipan $registrasi_barangtitipan)
    {
        $registrasi_barangtitipan->delete();
        return redirect()->route('registrasi-barang-titipans.index')->with('success', 'Jenis RegistrasiBarangTitipan berhasil dihapus.');
    }
}
