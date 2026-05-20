<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RegistrasiPendidikan;
use Inertia\Inertia;

class RegistrasiPendidikanController extends Controller
{
    public function index(Request $request)
    {
        $registrasipendidikans = RegistrasiPendidikan::when($request->search, function ($query, $search) {
                $query->where('nama_registrasipendidikan', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        // Pastikan folder di Vue-nya nanti bernama "RegistrasiPendidikan"
        return Inertia::render('admin/datamaster/registrasipendidikan/Index', [
            'registrasipendidikans' => $registrasipendidikans,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/datamaster/registrasipendidikan/Create');
    }

    public function store(Request $request)
    {
        RegistrasiPendidikan::create($request->validate([
            'nama_registrasipendidikan' => 'required|string|max:255',
        ]));

        return redirect()->route('registrasi-pendidikans.index')->with('success', 'Jenis RegistrasiPendidikan berhasil ditambahkan.');
    }

    public function edit(RegistrasiPendidikan $registrasi_pendidikan) // Variabel otomatis dari Laravel
    {
        return Inertia::render('admin/datamaster/registrasipendidikan/Edit', ['registrasipendidikan' => $registrasi_pendidikan]);
    }

    public function update(Request $request, RegistrasiPendidikan $registrasi_pendidikan)
    {
        $registrasi_pendidikan->update($request->validate([
            'nama_registrasipendidikan' => 'required|string|max:255',
        ]));

        return redirect()->route('registrasi-pendidikans.index')->with('success', 'Jenis RegistrasiPendidikan berhasil diperbarui.');
    }

    public function destroy(RegistrasiPendidikan $registrasi_pendidikan)
    {
        $registrasi_pendidikan->delete();
        return redirect()->route('registrasi-pendidikans.index')->with('success', 'Jenis RegistrasiPendidikan berhasil dihapus.');
    }
}
