<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Upt;
use App\Models\Kanwil;
use App\Models\JenisGolongan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DataAkunController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with(['roles', 'upt', 'kanwil', 'jenisGolongan'])
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'Pengunjung');
            })
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('nip', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/datamaster/dataakun/Index', [
            'users' => $users,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        $roles = Role::where('name', '!=', 'Pengunjung')->get();
        $kanwils = Kanwil::where('is_active', true)->orderBy('name')->get();
        $upts = Upt::where('is_active', true)->orderBy('name')->get(); 
        $jenisGolongans = JenisGolongan::orderBy('nama_golongan')->get();

        return Inertia::render('admin/datamaster/dataakun/Create', [
            'roles' => $roles,
            'kanwils' => $kanwils,
            'upts' => $upts,
            'jenisGolongans' => $jenisGolongans
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'required|string|unique:users,nip',
            'jabatan' => 'required|string',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
            'password' => 'required|string|min:8',
            'upt_id' => 'nullable|exists:upts,id',
            'kanwil_id' => 'nullable|exists:kanwils,id',
            'jenis_golongan_id' => 'nullable|exists:jenis_golongans,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'nip' => $request->nip,
            'username' => $request->nip, 
            'jabatan' => $request->jabatan,
            'upt_id' => $request->upt_id,
            'kanwil_id' => $request->kanwil_id,
            'jenis_golongan_id' => $request->jenis_golongan_id,
            'password' => Hash::make($request->password),
        ]);

        $user->syncRoles($request->roles);

        return redirect()->route('data-akun.index')->with('success', 'Akun pegawai berhasil dibuat.');
    }
    public function edit($id) 
    {
        $user = User::findOrFail($id); 

        $user->load(['roles', 'upt', 'kanwil']);
        $roles = Role::where('name', '!=', 'Pengunjung')->get();
        $kanwils = Kanwil::where('is_active', true)->orderBy('name')->get();
        $upts = Upt::where('is_active', true)->orderBy('name')->get();
        $jenisGolongans = JenisGolongan::orderBy('nama_golongan')->get();

        return Inertia::render('admin/datamaster/dataakun/Edit', [
            'user' => $user,
            'roles' => $roles,
            'kanwils' => $kanwils,
            'upts' => $upts,
            'jenisGolongans' => $jenisGolongans,
            'currentKanwilId' => $user->kanwil_id,
            'userRoles' => $user->roles->pluck('name')->toArray()
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'required|string|unique:users,nip,' . $user->id,
            'jabatan' => 'required|string',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
            'upt_id' => 'nullable|exists:upts,id',
            'kanwil_id' => 'nullable|exists:kanwils,id',
            'jenis_golongan_id' => 'nullable|exists:jenis_golongans,id',
        ]);

        $user->update([
            'name' => $request->name,
            'nip' => $request->nip,
            'username' => $request->nip, 
            'jabatan' => $request->jabatan,
            'upt_id' => $request->upt_id,
            'kanwil_id' => $request->kanwil_id,
            'jenis_golongan_id' => $request->jenis_golongan_id,
        ]);

        if ($request->password) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->syncRoles($request->roles);

        return redirect()->route('data-akun.index')->with('success', 'Akun pegawai berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        $user->delete();
        return redirect()->route('data-akun.index')->with('success', 'Akun berhasil dihapus.');
    }
}