<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RegistrasiOverstaying;
use Inertia\Inertia;

class RegistrasiOverstayingController extends Controller
{
    public function index(Request $request)
    {
        $registrasioverstayings = RegistrasiOverstaying::when($request->search, function ($query, $search) {
                $query->where('nama_registrasioverstaying', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        // Pastikan folder di Vue-nya nanti bernama "RegistrasiOverstaying"
        return Inertia::render('admin/datamaster/registrasioverstaying/Index', [
            'registrasioverstayings' => $registrasioverstayings,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/datamaster/registrasioverstaying/Create');
    }

    public function store(Request $request)
    {
        RegistrasiOverstaying::create($request->validate([
            'nama_registrasioverstaying' => 'required|string|max:255',
        ]));

        return redirect()->route('registrasi-overstayings.index')->with('success', 'Jenis RegistrasiOverstaying berhasil ditambahkan.');
    }

    public function edit(RegistrasiOverstaying $registrasi_overstaying) // Variabel otomatis dari Laravel
    {
        return Inertia::render('admin/datamaster/registrasioverstaying/Edit', ['registrasioverstaying' => $registrasi_overstaying]);
    }

    public function update(Request $request, RegistrasiOverstaying $registrasi_overstaying)
    {
        $registrasi_overstaying->update($request->validate([
            'nama_registrasioverstaying' => 'required|string|max:255',
        ]));

        return redirect()->route('registrasi-overstayings.index')->with('success', 'Jenis RegistrasiOverstaying berhasil diperbarui.');
    }

    public function destroy(RegistrasiOverstaying $registrasi_overstaying)
    {
        $registrasi_overstaying->delete();
        return redirect()->route('registrasi-overstayings.index')->with('success', 'Jenis RegistrasiOverstaying berhasil dihapus.');
    }
}
