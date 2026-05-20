<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JenisUsulan;
use Inertia\Inertia;

class JenisUsulanController extends Controller
{
    public function index(Request $request)
    {
        $usulans = JenisUsulan::when($request->search, function ($query, $search) {
                $query->where('nama_usulan', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        // Pastikan folder di Vue-nya nanti bernama "JenisUsulan"
        return Inertia::render('admin/datamaster/jenisusulan/Index', [
            'usulans' => $usulans,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/datamaster/jenisusulan/Create');
    }

    public function store(Request $request)
    {
        JenisUsulan::create($request->validate([
            'nama_usulan' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-usulans.index')->with('success', 'Jenis Usulan berhasil ditambahkan.');
    }

    public function edit(JenisUsulan $jenis_usulan) // Variabel otomatis dari Laravel
    {
        return Inertia::render('admin/datamaster/jenisusulan/Edit', ['usulan' => $jenis_usulan]);
    }

    public function update(Request $request, JenisUsulan $jenis_usulan)
    {
        $jenis_usulan->update($request->validate([
            'nama_usulan' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-usulans.index')->with('success', 'Jenis Usulan berhasil diperbarui.');
    }

    public function destroy(JenisUsulan $jenis_usulan)
    {
        $jenis_usulan->delete();
        return redirect()->route('jenis-usulans.index')->with('success', 'Jenis Usulan berhasil dihapus.');
    }
}
