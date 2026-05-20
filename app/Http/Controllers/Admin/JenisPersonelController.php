<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JenisPersonel;
use Inertia\Inertia;

class JenisPersonelController extends Controller
{
    public function index(Request $request)
    {
        $personels = JenisPersonel::when($request->search, function ($query, $search) {
                $query->where('nama_personel', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        // Pastikan folder di Vue-nya nanti bernama "JenisPersonel"
        return Inertia::render('admin/datamaster/jenispersonel/Index', [
            'personels' => $personels,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/datamaster/jenispersonel/Create');
    }

    public function store(Request $request)
    {
        JenisPersonel::create($request->validate([
            'nama_personel' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-personels.index')->with('success', 'Jenis Personel berhasil ditambahkan.');
    }

    public function edit(JenisPersonel $jenis_personel) // Variabel otomatis dari Laravel
    {
        return Inertia::render('admin/datamaster/jenispersonel/Edit', ['personel' => $jenis_personel]);
    }

    public function update(Request $request, JenisPersonel $jenis_personel)
    {
        $jenis_personel->update($request->validate([
            'nama_personel' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-personels.index')->with('success', 'Jenis Personel berhasil diperbarui.');
    }

    public function destroy(JenisPersonel $jenis_personel)
    {
        $jenis_personel->delete();
        return redirect()->route('jenis-personels.index')->with('success', 'Jenis Personel berhasil dihapus.');
    }
}