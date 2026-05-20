<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JenisKepribadian;
use Inertia\Inertia;

class JenisKepribadianController extends Controller
{
    public function index(Request $request)
    {
        $kepribadians = JenisKepribadian::when($request->search, function ($query, $search) {
                $query->where('nama_kepribadian', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        // Pastikan folder di Vue-nya nanti bernama "JenisKepribadian"
        return Inertia::render('admin/datamaster/jeniskepribadian/Index', [
            'kepribadians' => $kepribadians,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/datamaster/jeniskepribadian/Create');
    }

    public function store(Request $request)
    {
        JenisKepribadian::create($request->validate([
            'nama_kepribadian' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-kepribadians.index')->with('success', 'Jenis Kepribadian berhasil ditambahkan.');
    }

    public function edit(JenisKepribadian $jenis_kepribadian) // Variabel otomatis dari Laravel
    {
        return Inertia::render('admin/datamaster/jeniskepribadian/Edit', ['kepribadian' => $jenis_kepribadian]);
    }

    public function update(Request $request, JenisKepribadian $jenis_kepribadian)
    {
        $jenis_kepribadian->update($request->validate([
            'nama_kepribadian' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-kepribadians.index')->with('success', 'Jenis Kepribadian berhasil diperbarui.');
    }

    public function destroy(JenisKepribadian $jenis_kepribadian)
    {
        $jenis_kepribadian->delete();
        return redirect()->route('jenis-kepribadians.index')->with('success', 'Jenis Kepribadian berhasil dihapus.');
    }
}
