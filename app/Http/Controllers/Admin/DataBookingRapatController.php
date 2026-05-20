<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataBookingRapat;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class DataBookingRapatController extends Controller
{
    public function index(Request $request)
    {
        $query = DataBookingRapat::query();

        if ($request->tanggal) $query->whereDate('tanggal_rapat', $request->tanggal);
        if ($request->status) $query->where('status', $request->status);

        $databookingrapats = $query->orderBy('tanggal_rapat', 'desc')
                                   ->orderBy('waktu_mulai', 'asc')
                                   ->paginate(10)
                                   ->withQueryString();

        return Inertia::render('admin/kanwil/databookingrapat/Index', [
            'databookingrapats' => $databookingrapats,
            'filters' => $request->only(['tanggal', 'status'])
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/kanwil/databookingrapat/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'divisi_pemohon' => 'required|string|max:255',
            'ruangan' => 'required|string|max:255',
            'judul_rapat' => 'required|string|max:255',
            'tanggal_rapat' => 'required|date',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required|after:waktu_mulai',
            'jumlah_peserta' => 'required|integer|min:1',
            'fasilitas' => 'nullable|array',
            'keterangan' => 'nullable|string',
            'surat_undangan' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        // LOGIKA MEWAH: Cek Jadwal Bentrok (Anti-Double Booking)
        $bentrok = DataBookingRapat::where('ruangan', $request->ruangan)
            ->where('tanggal_rapat', $request->tanggal_rapat)
            ->where('status', '!=', 'Ditolak') // Yang ditolak tidak dihitung bentrok
            ->where(function ($query) use ($request) {
                $query->where('waktu_mulai', '<', $request->waktu_selesai)
                      ->where('waktu_selesai', '>', $request->waktu_mulai);
            })->first();

        if ($bentrok) {
            return redirect()->back()->withErrors([
                'waktu_mulai' => 'Jadwal bentrok! Ruangan sudah dibooking oleh ' . $bentrok->divisi_pemohon . ' pada jam ' . $bentrok->waktu_mulai . ' - ' . $bentrok->waktu_selesai
            ]);
        }

        if ($request->hasFile('surat_undangan')) {
            $validated['surat_undangan'] = $request->file('surat_undangan')->store('booking_rapat/undangan', 'public');
        }

        DataBookingRapat::create($validated);
        return redirect()->route('data-booking-rapats.index')->with('success', 'Berhasil melakukan booking ruang rapat.');
    }

    public function edit($id)
    {
        $databookingrapat = DataBookingRapat::findOrFail($id);
        return Inertia::render('admin/kanwil/databookingrapat/Edit', [
            'databookingrapat' => $databookingrapat
        ]);
    }

    public function update(Request $request, $id)
    {
        $booking = DataBookingRapat::findOrFail($id);

        $validated = $request->validate([
            'divisi_pemohon' => 'required|string|max:255',
            'ruangan' => 'required|string|max:255',
            'judul_rapat' => 'required|string|max:255',
            'tanggal_rapat' => 'required|date',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required|after:waktu_mulai',
            'jumlah_peserta' => 'required|integer|min:1',
            'fasilitas' => 'nullable|array',
            'keterangan' => 'nullable|string',
            'status' => 'required|string',
            'catatan_admin' => 'nullable|string',
            'surat_undangan' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        // Cek Bentrok lagi (kecuali dengan dirinya sendiri)
        if ($request->status !== 'Ditolak') {
            $bentrok = DataBookingRapat::where('ruangan', $request->ruangan)
                ->where('tanggal_rapat', $request->tanggal_rapat)
                ->where('id', '!=', $id)
                ->where('status', '!=', 'Ditolak')
                ->where(function ($query) use ($request) {
                    $query->where('waktu_mulai', '<', $request->waktu_selesai)
                          ->where('waktu_selesai', '>', $request->waktu_mulai);
                })->first();

            if ($bentrok) {
                return redirect()->back()->withErrors([
                    'waktu_mulai' => 'Jadwal bentrok! Ruangan sudah dibooking oleh ' . $bentrok->divisi_pemohon . ' pada jam ' . $bentrok->waktu_mulai . ' - ' . $bentrok->waktu_selesai
                ]);
            }
        }

        unset($validated['surat_undangan']);
        if ($request->hasFile('surat_undangan')) {
            if ($booking->surat_undangan) Storage::disk('public')->delete($booking->surat_undangan);
            $validated['surat_undangan'] = $request->file('surat_undangan')->store('booking_rapat/undangan', 'public');
        }

        $booking->update($validated);
        return redirect()->route('data-booking-rapats.index')->with('success', 'Data booking berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $booking = DataBookingRapat::findOrFail($id);
        if ($booking->surat_undangan) Storage::disk('public')->delete($booking->surat_undangan);
        $booking->delete();
        return redirect()->route('data-booking-rapats.index')->with('success', 'Data booking dihapus.');
    }
}