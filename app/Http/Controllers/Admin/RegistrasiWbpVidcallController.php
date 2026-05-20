<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RegistrasiWbpVidcall;
use Inertia\Inertia;

class RegistrasiWbpVidcallController extends Controller
{
    public function index(Request $request)
    {
        $registrasiwbpvidcalls = RegistrasiWbpVidcall::when($request->search, function ($query, $search) {
                $query->where('nama_registrasiwbpvidcall', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        // Pastikan folder di Vue-nya nanti bernama "RegistrasiWbpVidcall"
        return Inertia::render('admin/datamaster/registrasiwbpvidcall/Index', [
            'registrasiwbpvidcalls' => $registrasiwbpvidcalls,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/datamaster/registrasiwbpvidcall/Create');
    }

    public function store(Request $request)
    {
        RegistrasiWbpVidcall::create($request->validate([
            'nama_registrasiwbpvidcall' => 'required|string|max:255',
        ]));

        return redirect()->route('registrasi-wbp-vidcalls.index')->with('success', 'Jenis RegistrasiWbpVidcall berhasil ditambahkan.');
    }

    public function edit(RegistrasiWbpVidcall $registrasi_wbpvidcall) // Variabel otomatis dari Laravel
    {
        return Inertia::render('admin/datamaster/registrasiwbpvidcall/Edit', ['registrasiwbpvidcall' => $registrasi_wbpvidcall]);
    }

    public function update(Request $request, RegistrasiWbpVidcall $registrasi_wbpvidcall)
    {
        $registrasi_wbpvidcall->update($request->validate([
            'nama_registrasiwbpvidcall' => 'required|string|max:255',
        ]));

        return redirect()->route('registrasi-wbp-vidcalls.index')->with('success', 'Jenis RegistrasiWbpVidcall berhasil diperbarui.');
    }

    public function destroy(RegistrasiWbpVidcall $registrasi_wbpvidcall)
    {
        $registrasi_wbpvidcall->delete();
        return redirect()->route('registrasi-wbp-vidcalls.index')->with('success', 'Jenis RegistrasiWbpVidcall berhasil dihapus.');
    }
}
