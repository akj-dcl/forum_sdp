<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JenisStaff;
use Inertia\Inertia;

class JenisStaffController extends Controller
{
    public function index(Request $request)
    {
        $staffs = JenisStaff::when($request->search, function ($query, $search) {
                $query->where('nama_staff', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        // Pastikan folder di Vue-nya nanti bernama "JenisStaff"
        return Inertia::render('admin/datamaster/jenisstaff/Index', [
            'staffs' => $staffs,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/datamaster/jenisstaff/Create');
    }

    public function store(Request $request)
    {
        JenisStaff::create($request->validate([
            'nama_staff' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-staffs.index')->with('success', 'Jenis Staff berhasil ditambahkan.');
    }

    public function edit(JenisStaff $jenis_staff) // Variabel otomatis dari Laravel
    {
        return Inertia::render('admin/datamaster/jenisstaff/Edit', ['staff' => $jenis_staff]);
    }

    public function update(Request $request, JenisStaff $jenis_staff)
    {
        $jenis_staff->update($request->validate([
            'nama_staff' => 'required|string|max:255',
        ]));

        return redirect()->route('jenis-staffs.index')->with('success', 'Jenis Staff berhasil diperbarui.');
    }

    public function destroy(JenisStaff $jenis_staff)
    {
        $jenis_staff->delete();
        return redirect()->route('jenis-staffs.index')->with('success', 'Jenis Staff berhasil dihapus.');
    }
}
