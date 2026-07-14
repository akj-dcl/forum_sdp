<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::find(1); 

        if ($user) {
            $role = Role::firstOrCreate(['name' => 'Super Admin']);

            $permissions = Permission::pluck('id', 'id')->all();
            $role->syncPermissions($permissions);

            $user->assignRole([$role->id]);
            
            $this->command->info('User pertama berhasil jadi Super Admin!');
        } else {
            $this->command->error('User dengan ID 1 tidak ditemukan. Bikin akun dulu di menu register!');
        }
    }
}