<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JenisPengawalan;
use Inertia\Inertia;

class JenisPengawalanController extends Controller
{
    public function index(Request $request)
    {
        $pengawalans = JenisPengawalan::when($request->search, function ($query, $search) {
                $query->where('nama_pengawalan', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        // Pastikan folder di Vue-nya nanti bernama "JenisPengawalan"
        return Inertia::render('admin/datamaster/jenispengawalan/Index', [
            'pengawalans' => $pengawalans,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/datamaster/jenispengawalan/Create');
    }

    public function store(Request $request)
    {
        JenisPengawalan::create($request->validate([
            'nama_pengawalan' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-pengawalans.index')->with('success', 'Jenis Pengawalan berhasil ditambahkan.');
    }

    public function edit(JenisPengawalan $jenis_pengawalan) // Variabel otomatis dari Laravel
    {
        return Inertia::render('admin/datamaster/jenispengawalan/Edit', ['pengawalan' => $jenis_pengawalan]);
    }

    public function update(Request $request, JenisPengawalan $jenis_pengawalan)
    {
        $jenis_pengawalan->update($request->validate([
            'nama_pengawalan' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-pengawalans.index')->with('success', 'Jenis Pengawalan berhasil diperbarui.');
    }

    public function destroy(JenisPengawalan $jenis_pengawalan)
    {
        $jenis_pengawalan->delete();
        return redirect()->route('jenis-pengawalans.index')->with('success', 'Jenis Pengawalan berhasil dihapus.');
    }
}