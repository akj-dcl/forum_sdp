<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JenisPemindahan;
use Inertia\Inertia;

class JenisPemindahanController extends Controller
{
    public function index(Request $request)
    {
        $pemindahans = JenisPemindahan::when($request->search, function ($query, $search) {
                $query->where('nama_pemindahan', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        // Pastikan folder di Vue-nya nanti bernama "JenisPemindahan"
        return Inertia::render('admin/datamaster/jenispemindahan/Index', [
            'pemindahans' => $pemindahans,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/datamaster/jenispemindahan/Create');
    }

    public function store(Request $request)
    {
        JenisPemindahan::create($request->validate([
            'nama_pemindahan' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-pemindahans.index')->with('success', 'Jenis Pemindahan berhasil ditambahkan.');
    }

    public function edit(JenisPemindahan $jenis_pemindahan) // Variabel otomatis dari Laravel
    {
        return Inertia::render('admin/datamaster/jenispemindahan/Edit', ['pemindahan' => $jenis_pemindahan]);
    }

    public function update(Request $request, JenisPemindahan $jenis_pemindahan)
    {
        $jenis_pemindahan->update($request->validate([
            'nama_pemindahan' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-pemindahans.index')->with('success', 'Jenis Pemindahan berhasil diperbarui.');
    }

    public function destroy(JenisPemindahan $jenis_pemindahan)
    {
        $jenis_pemindahan->delete();
        return redirect()->route('jenis-pemindahans.index')->with('success', 'Jenis Pemindahan berhasil dihapus.');
    }
}
