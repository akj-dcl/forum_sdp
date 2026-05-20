<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RegistrasiPidum;
use Inertia\Inertia;

class RegistrasiPidumController extends Controller
{
    public function index(Request $request)
    {
        $registrasipidums = RegistrasiPidum::when($request->search, function ($query, $search) {
                $query->where('nama_registrasipidum', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        // Pastikan folder di Vue-nya nanti bernama "RegistrasiPidum"
        return Inertia::render('admin/datamaster/registrasipidum/Index', [
            'registrasipidums' => $registrasipidums,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/datamaster/registrasipidum/Create');
    }

    public function store(Request $request)
    {
        RegistrasiPidum::create($request->validate([
            'nama_registrasipidum' => 'required|string|max:255',
        ]));

        return redirect()->route('registrasi-pidums.index')->with('success', 'Jenis RegistrasiPidum berhasil ditambahkan.');
    }

    public function edit(RegistrasiPidum $registrasi_pidum) // Variabel otomatis dari Laravel
    {
        return Inertia::render('admin/datamaster/registrasipidum/Edit', ['registrasipidum' => $registrasi_pidum]);
    }

    public function update(Request $request, RegistrasiPidum $registrasi_pidum)
    {
        $registrasi_pidum->update($request->validate([
            'nama_registrasipidum' => 'required|string|max:255',
        ]));

        return redirect()->route('registrasi-pidums.index')->with('success', 'Jenis RegistrasiPidum berhasil diperbarui.');
    }

    public function destroy(RegistrasiPidum $registrasi_pidum)
    {
        $registrasi_pidum->delete();
        return redirect()->route('registrasi-pidums.index')->with('success', 'Jenis RegistrasiPidum berhasil dihapus.');
    }
}
