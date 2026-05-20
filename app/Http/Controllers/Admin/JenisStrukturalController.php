<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JenisStruktural;
use Inertia\Inertia;

class JenisStrukturalController extends Controller
{
    public function index(Request $request)
    {
        $strukturals = JenisStruktural::when($request->search, function ($query, $search) {
                $query->where('nama_struktural', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        // Pastikan folder di Vue-nya nanti bernama "JenisStruktural"
        return Inertia::render('admin/datamaster/jenisstruktural/Index', [
            'strukturals' => $strukturals,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/datamaster/jenisstruktural/Create');
    }

    public function store(Request $request)
    {
        JenisStruktural::create($request->validate([
            'nama_struktural' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-strukturals.index')->with('success', 'Jenis Struktural berhasil ditambahkan.');
    }

    public function edit(JenisStruktural $jenis_struktural) // Variabel otomatis dari Laravel
    {
        return Inertia::render('admin/datamaster/jenisstruktural/Edit', ['struktural' => $jenis_struktural]);
    }

    public function update(Request $request, JenisStruktural $jenis_struktural)
    {
        $jenis_struktural->update($request->validate([
            'nama_struktural' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-strukturals.index')->with('success', 'Jenis Struktural berhasil diperbarui.');
    }

    public function destroy(JenisStruktural $jenis_struktural)
    {
        $jenis_struktural->delete();
        return redirect()->route('jenis-strukturals.index')->with('success', 'Jenis Struktural berhasil dihapus.');
    }
}
