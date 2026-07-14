<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JenisGolongan;
use Inertia\Inertia;

class JenisGolonganController extends Controller
{
    public function index(Request $request)
    {
        $golongans = JenisGolongan::when($request->search, function ($query, $search) {
                $query->where('nama_golongan', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/datamaster/jenisgolongan/Index', [
            'golongans' => $golongans,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/datamaster/jenisgolongan/Create');
    }

    public function store(Request $request)
    {
        JenisGolongan::create($request->validate([
            'nama_golongan' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-golongans.index')->with('success', 'Jenis Golongan berhasil ditambahkan.');
    }

    public function edit(JenisGolongan $jenis_golongan)
    {
        return Inertia::render('admin/datamaster/jenisgolongan/Edit', ['golongan' => $jenis_golongan]);
    }

    public function update(Request $request, JenisGolongan $jenis_golongan)
    {
        $jenis_golongan->update($request->validate([
            'nama_golongan' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-golongans.index')->with('success', 'Jenis Golongan berhasil diperbarui.');
    }

    public function destroy(JenisGolongan $jenis_golongan)
    {
        $jenis_golongan->delete();
        return redirect()->route('jenis-golongans.index')->with('success', 'Jenis Golongan berhasil dihapus.');
    }
}
