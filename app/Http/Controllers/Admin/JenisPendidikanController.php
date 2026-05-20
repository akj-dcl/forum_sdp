<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JenisPendidikan;
use Inertia\Inertia;

class JenisPendidikanController extends Controller
{
    public function index(Request $request)
    {
        $pendidikans = JenisPendidikan::when($request->search, function ($query, $search) {
                $query->where('nama_pendidikan', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        // Pastikan folder di Vue-nya nanti bernama "JenisPendidikan"
        return Inertia::render('admin/datamaster/jenispendidikan/Index', [
            'pendidikans' => $pendidikans,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/datamaster/jenispendidikan/Create');
    }

    public function store(Request $request)
    {
        JenisPendidikan::create($request->validate([
            'nama_pendidikan' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-pendidikans.index')->with('success', 'Jenis Pendidikan berhasil ditambahkan.');
    }

    public function edit(JenisPendidikan $jenis_pendidikan) // Variabel otomatis dari Laravel
    {
        return Inertia::render('admin/datamaster/jenispendidikan/Edit', ['pendidikan' => $jenis_pendidikan]);
    }

    public function update(Request $request, JenisPendidikan $jenis_pendidikan)
    {
        $jenis_pendidikan->update($request->validate([
            'nama_pendidikan' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-pendidikans.index')->with('success', 'Jenis Pendidikan berhasil diperbarui.');
    }

    public function destroy(JenisPendidikan $jenis_pendidikan)
    {
        $jenis_pendidikan->delete();
        return redirect()->route('jenis-pendidikans.index')->with('success', 'Jenis Pendidikan berhasil dihapus.');
    }
}
