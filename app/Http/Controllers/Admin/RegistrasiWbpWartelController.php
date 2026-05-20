<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RegistrasiWbpWartel;
use Inertia\Inertia;

class RegistrasiWbpWartelController extends Controller
{
    public function index(Request $request)
    {
        $registrasiwbpwartels = RegistrasiWbpWartel::when($request->search, function ($query, $search) {
                $query->where('nama_registrasiwbpwartel', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        // Pastikan folder di Vue-nya nanti bernama "RegistrasiWbpWartel"
        return Inertia::render('admin/datamaster/registrasiwbpwartel/Index', [
            'registrasiwbpwartels' => $registrasiwbpwartels,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/datamaster/registrasiwbpwartel/Create');
    }

    public function store(Request $request)
    {
        RegistrasiWbpWartel::create($request->validate([
            'nama_registrasiwbpwartel' => 'required|string|max:255',
        ]));

        return redirect()->route('registrasi-wbp-wartels.index')->with('success', 'Jenis RegistrasiWbpWartel berhasil ditambahkan.');
    }

    public function edit(RegistrasiWbpWartel $registrasi_wbpwartel) // Variabel otomatis dari Laravel
    {
        return Inertia::render('admin/datamaster/registrasiwbpwartel/Edit', ['registrasiwbpwartel' => $registrasi_wbpwartel]);
    }

    public function update(Request $request, RegistrasiWbpWartel $registrasi_wbpwartel)
    {
        $registrasi_wbpwartel->update($request->validate([
            'nama_registrasiwbpwartel' => 'required|string|max:255',
        ]));

        return redirect()->route('registrasi-wbp-wartels.index')->with('success', 'Jenis RegistrasiWbpWartel berhasil diperbarui.');
    }

    public function destroy(RegistrasiWbpWartel $registrasi_wbpwartel)
    {
        $registrasi_wbpwartel->delete();
        return redirect()->route('registrasi-wbp-wartels.index')->with('success', 'Jenis RegistrasiWbpWartel berhasil dihapus.');
    }
}
