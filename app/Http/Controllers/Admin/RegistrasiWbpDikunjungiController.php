<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RegistrasiWbpDikunjungi;
use Inertia\Inertia;

class RegistrasiWbpDikunjungiController extends Controller
{
    public function index(Request $request)
    {
        $registrasiwbpdikunjungis = RegistrasiWbpDikunjungi::when($request->search, function ($query, $search) {
                $query->where('nama_registrasiwbpdikunjungi', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        // Pastikan folder di Vue-nya nanti bernama "RegistrasiWbpDikunjungi"
        return Inertia::render('admin/datamaster/registrasiwbpdikunjungi/Index', [
            'registrasiwbpdikunjungis' => $registrasiwbpdikunjungis,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/datamaster/registrasiwbpdikunjungi/Create');
    }

    public function store(Request $request)
    {
        RegistrasiWbpDikunjungi::create($request->validate([
            'nama_registrasiwbpdikunjungi' => 'required|string|max:255',
        ]));

        return redirect()->route('registrasi-wbp-dikunjungis.index')->with('success', 'Jenis RegistrasiWbpDikunjungi berhasil ditambahkan.');
    }

    public function edit(RegistrasiWbpDikunjungi $registrasi_wbpdikunjungi) // Variabel otomatis dari Laravel
    {
        return Inertia::render('admin/datamaster/registrasiwbpdikunjungi/Edit', ['registrasiwbpdikunjungi' => $registrasi_wbpdikunjungi]);
    }

    public function update(Request $request, RegistrasiWbpDikunjungi $registrasi_wbpdikunjungi)
    {
        $registrasi_wbpdikunjungi->update($request->validate([
            'nama_registrasiwbpdikunjungi' => 'required|string|max:255',
        ]));

        return redirect()->route('registrasi-wbp-dikunjungis.index')->with('success', 'Jenis RegistrasiWbpDikunjungi berhasil diperbarui.');
    }

    public function destroy(RegistrasiWbpDikunjungi $registrasi_wbpdikunjungi)
    {
        $registrasi_wbpdikunjungi->delete();
        return redirect()->route('registrasi-wbp-dikunjungis.index')->with('success', 'Jenis RegistrasiWbpDikunjungi berhasil dihapus.');
    }
}
