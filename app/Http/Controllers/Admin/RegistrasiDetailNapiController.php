<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RegistrasiDetailNapi;
use Inertia\Inertia;

class RegistrasiDetailNapiController extends Controller
{
    public function index(Request $request)
    {
        $registrasidetailnapis = RegistrasiDetailNapi::when($request->search, function ($query, $search) {
                $query->where('nama_registrasidetailnapi', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        // Pastikan folder di Vue-nya nanti bernama "RegistrasiDetailNapi"
        return Inertia::render('admin/datamaster/registrasidetailnapi/Index', [
            'registrasidetailnapis' => $registrasidetailnapis,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/datamaster/registrasidetailnapi/Create');
    }

    public function store(Request $request)
    {
        RegistrasiDetailNapi::create($request->validate([
            'nama_registrasidetailnapi' => 'required|string|max:255',
        ]));

        return redirect()->route('registrasi-detail-napis.index')->with('success', 'Jenis RegistrasiDetailNapi berhasil ditambahkan.');
    }

    public function edit(RegistrasiDetailNapi $registrasi_detailnapi) // Variabel otomatis dari Laravel
    {
        return Inertia::render('admin/datamaster/registrasidetailnapi/Edit', ['registrasidetailnapi' => $registrasi_detailnapi]);
    }

    public function update(Request $request, RegistrasiDetailNapi $registrasi_detailnapi)
    {
        $registrasi_detailnapi->update($request->validate([
            'nama_registrasidetailnapi' => 'required|string|max:255',
        ]));

        return redirect()->route('registrasi-detail-napis.index')->with('success', 'Jenis RegistrasiDetailNapi berhasil diperbarui.');
    }

    public function destroy(RegistrasiDetailNapi $registrasi_detailnapi)
    {
        $registrasi_detailnapi->delete();
        return redirect()->route('registrasi-detail-napis.index')->with('success', 'Jenis RegistrasiDetailNapi berhasil dihapus.');
    }
}
