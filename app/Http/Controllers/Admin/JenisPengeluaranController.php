<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Jenispengeluaran;
use Inertia\Inertia;

class JenisPengeluaranController extends Controller
{
    public function index(Request $request)
    {
        $pengeluarans = JenisPengeluaran::when($request->search, function ($query, $search) {
                $query->where('nama_pengeluaran', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        // Pastikan folder di Vue-nya nanti bernama "JenisPengeluaran"
        return Inertia::render('admin/datamaster/jenispengeluaran/Index', [
            'pengeluarans' => $pengeluarans,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/datamaster/jenispengeluaran/Create');
    }

    public function store(Request $request)
    {
        JenisPengeluaran::create($request->validate([
            'nama_pengeluaran' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-pengeluarans.index')->with('success', 'Jenis Pengeluaran berhasil ditambahkan.');
    }

    public function edit(JenisPengeluaran $jenis_pengeluaran) // Variabel otomatis dari Laravel
    {
        return Inertia::render('admin/datamaster/jenispengeluaran/Edit', ['pengeluaran' => $jenis_pengeluaran]);
    }

    public function update(Request $request, JenisPengeluaran $jenis_pengeluaran)
    {
        $jenis_pengeluaran->update($request->validate([
            'nama_pengeluaran' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-pengeluarans.index')->with('success', 'Jenis Pengeluaran berhasil diperbarui.');
    }

    public function destroy(JenisPengeluaran $jenis_pengeluaran)
    {
        $jenis_pengeluaran->delete();
        return redirect()->route('jenis-pengeluarans.index')->with('success', 'Jenis Pengeluaran berhasil dihapus.');
    }
}
