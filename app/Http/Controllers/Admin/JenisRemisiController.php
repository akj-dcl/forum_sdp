<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JenisRemisi;
use Inertia\Inertia;

class JenisRemisiController extends Controller
{
    public function index(Request $request)
    {
        $remisis = JenisRemisi::when($request->search, function ($query, $search) {
                $query->where('nama_remisi', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        // Pastikan folder di Vue-nya nanti bernama "JenisRemisi"
        return Inertia::render('admin/datamaster/jenisremisi/Index', [
            'remisis' => $remisis,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/datamaster/jenisremisi/Create');
    }

    public function store(Request $request)
    {
        JenisRemisi::create($request->validate([
            'nama_remisi' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-remisis.index')->with('success', 'Jenis Remisi berhasil ditambahkan.');
    }

    public function edit(JenisRemisi $jenis_remisi) // Variabel otomatis dari Laravel
    {
        return Inertia::render('admin/datamaster/jenisremisi/Edit', ['remisi' => $jenis_remisi]);
    }

    public function update(Request $request, JenisRemisi $jenis_remisi)
    {
        $jenis_remisi->update($request->validate([
            'nama_remisi' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-remisis.index')->with('success', 'Jenis Remisi berhasil diperbarui.');
    }

    public function destroy(JenisRemisi $jenis_remisi)
    {
        $jenis_remisi->delete();
        return redirect()->route('jenis-remisis.index')->with('success', 'Jenis Remisi berhasil dihapus.');
    }
}
