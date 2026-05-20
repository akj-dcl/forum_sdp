<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JenisBeban;
use Inertia\Inertia;

class JenisBebanController extends Controller
{
    public function index(Request $request)
    {
        $bebans = JenisBeban::when($request->search, function ($query, $search) {
                $query->where('nama_beban', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        // Pastikan folder di Vue-nya nanti bernama "JenisBeban"
        return Inertia::render('admin/datamaster/jenisbeban/Index', [
            'bebans' => $bebans,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/datamaster/jenisbeban/Create');
    }

    public function store(Request $request)
    {
        JenisBeban::create($request->validate([
            'nama_beban' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-bebans.index')->with('success', 'Jenis Beban berhasil ditambahkan.');
    }

    public function edit(JenisBeban $jenis_beban) // Variabel otomatis dari Laravel
    {
        return Inertia::render('admin/datamaster/jenisbeban/Edit', ['beban' => $jenis_beban]);
    }

    public function update(Request $request, JenisBeban $jenis_beban)
    {
        $jenis_beban->update($request->validate([
            'nama_beban' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-bebans.index')->with('success', 'Jenis Beban berhasil diperbarui.');
    }

    public function destroy(JenisBeban $jenis_beban)
    {
        $jenis_beban->delete();
        return redirect()->route('jenis-bebans.index')->with('success', 'Jenis Beban berhasil dihapus.');
    }
}
