<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            //Milik Kanwil dan Super Admin
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            'bookings.view', 'bookings.create', 'bookings.edit', 'bookings.delete',
            'kanwils.view', 'kanwils.create', 'kanwils.edit', 'kanwils.delete',
            'upts.view', 'upts.create', 'upts.edit', 'upts.delete',
            'jenis-pidanas.vue', 'jenis-pidanas.create', 'jenis-pidanas.edit', 'jenis-pidanas.delete',
            'jenis-kepribadians.vue', 'jenis-kepribadians.create', 'jenis-kepribadians.edit', 'jenis-kepribadians.delete',
            'jenis-kemandirians.vue', 'jenis-kemandirians.create', 'jenis-kemandirians.edit', 'jenis-kemandirians.delete',
            'jenis-remisis.vue', 'jenis-remisis.create', 'jenis-remisis.edit', 'jenis-remisis.delete',
            'jenis-pemindahans.vue', 'jenis-pemindahans.create', 'jenis-pemindahans.edit', 'jenis-pemindahans.delete',
            'jenis-pengawalans.vue', 'jenis-pengawalans.create', 'jenis-pengawalans.edit', 'jenis-pengawalans.delete',
            'jenis-klasifikasis.vue', 'jenis-klasifikasis.create', 'jenis-klasifikasis.edit', 'jenis-klasifikasis.delete',
            'jenis-personels.vue', 'jenis-personels.create', 'jenis-personels.edit', 'jenis-personels.delete',
            'jenis-pendidikans.vue', 'jenis-pendidikans.create', 'jenis-pendidikans.edit', 'jenis-pendidikans.delete',
            'jenis-klasifikasis.vue', 'jenis-klasifikasis.create', 'jenis-klasifikasis.edit', 'jenis-klasifikasis.delete',
            'jenis-usulans.vue', 'jenis-usulans.create', 'jenis-usulans.edit', 'jenis-usulans.delete',
            'jenis-strukturals.vue', 'jenis-strukturals.create', 'jenis-strukturals.edit', 'jenis-strukturals.delete',
            'jenis-golongans.vue', 'jenis-golongans.create', 'jenis-golongans.edit', 'jenis-golongans.delete',
            'jenis-bebans.vue', 'jenis-bebans.create', 'jenis-bebans.edit', 'jenis-bebans.delete',
            'jenis-staffs.vue', 'jenis-staffs.create', 'jenis-staffs.edit', 'jenis-staffs.delete',
            'jenis-integrasis.vue', 'jenis-integrasis.create', 'jenis-integrasis.edit', 'jenis-integrasis.delete',
            'jenis-agamas.vue', 'jenis-agamas.create', 'jenis-agamas.edit', 'jenis-agamas.delete',
            'jenis-pengeluarans.vue', 'jenis-pengeluarans.create', 'jenis-pengeluarans.edit', 'jenis-pengeluarans.delete',
            'akun.view', 'akun.create', 'akun.edit', 'akun.delete',
            'data-booking-rapats.view', 'data-booking-rapats.edit', 'data-booking-rapats.delete',

            //Milik Kanwil dan Super Admin
            'registrasi-umums.view', 'registrasi-umums.create', 'registrasi-umums.edit', 'registrasi-umums.delete',
            'registrasi-pidums.view', 'registrasi-pidums.create', 'registrasi-pidums.edit', 'registrasi-pidums.delete',
            'registrasi-pidsuses.view', 'registrasi-pidsuses.create', 'registrasi-pidsuses.edit', 'registrasi-pidsuses.delete',
            'registrasi-overstayings.view', 'registrasi-overstayings.create', 'registrasi-overstayings.edit', 'registrasi-overstayings.delete',
            'registrasi-integrasis.view', 'registrasi-integrasis.create', 'registrasi-integrasis.edit', 'registrasi-integrasis.delete',
            'registrasi-identitases.view', 'registrasi-identitases.create', 'registrasi-identitases.edit', 'registrasi-identitases.delete',
            'registrasi-pendidikans.view', 'registrasi-pendidikans.create', 'registrasi-pendidikans.edit', 'registrasi-pendidikans.delete',
            'registrasi-detail-napis.view', 'registrasi-detail-napis.create', 'registrasi-detail-napis.edit', 'registrasi-detail-napis.delete',
            'registrasi-detail-tahanans.view', 'registrasi-detail-tahanans.create', 'registrasi-detail-tahanans.edit', 'registrasi-detail-tahanans.delete',
            'registrasi-petugases.view', 'registrasi-petugases.create', 'registrasi-petugases.edit', 'registrasi-petugases.delete',
            'registrasi-pengunjungs.view', 'registrasi-pengunjungs.create', 'registrasi-pengunjungs.edit', 'registrasi-pengunjungs.delete',
            'registrasi-wbp-dikunjungis.view', 'registrasi-wbp-dikunjungis.create', 'registrasi-wbp-dikunjungis.edit', 'registrasi-wbp-dikunjungis.delete',
            'registrasi-wbp-vidcalls.view', 'registrasi-wbp-vidcalls.create', 'registrasi-wbp-vidcalls.edit', 'registrasi-wbp-vidcalls.delete',
            'registrasi-wbp-wartels.view', 'registrasi-wbp-wartels.create', 'registrasi-wbp-wartels.edit', 'registrasi-wbp-wartels.delete',
            'registrasi-barang-titipans.view', 'registrasi-barang-titipans.create', 'registrasi-barang-titipans.edit', 'registrasi-barang-titipans.delete',

            //Miliki Pembinaan
            'data-resgistrasis.view', 'data-resgistrasis.create', 'data-resgistrasis.edit', 'data-resgistrasis.delete',
            'data-residivises.view', 'data-residivises.create', 'data-residivises.edit', 'data-residivises.delete',
            'data-pembinaan-kepribadians.view', 'data-pembinaan-kepribadians.create', 'data-pembinaan-kepribadians.edit', 'data-pembinaan-kepribadians.delete',
            'data-pembinaan-kemandirians.view', 'data-pembinaan-kemandirians.create', 'data-pembinaan-kemandirians.edit', 'data-pembinaan-kemandirians.delete',
            'data-kontrol-keliling.view', 'data-kontrol-keliling.create', 'data-kontrol-keliling.edit', 'data-kontrol-keliling.delete',
            'data-integrasi-tpps.view', 'data-integrasi-tpps.create', 'data-integrasi-tpps.edit', 'data-integrasi-tpps.delete',
            'data-integrasis.view', 'data-integrasis.create', 'data-integrasis.edit', 'data-integrasis.delete',
            'data-remisis.view', 'data-remisis.create', 'data-remisis.edit', 'data-remisis.delete',
            'data-pemindahans.view', 'data-pemindahans.create', 'data-pemindahans.edit', 'data-pemindahans.delete',

            //Milik Pengamanan
            'data-perawatan-harians.view', 'data-perawatan-harians.create', 'data-perawatan-harians.edit', 'data-perawatan-harians.delete',
            'data-perawatan-bulanans.view', 'data-perawatan-bulanans.create', 'data-perawatan-bulanans.edit', 'data-perawatan-bulanans.delete',
            'data-penghuni-riils.view', 'data-penghuni-riils.create', 'data-penghuni-riils.edit', 'data-penghuni-riils.delete',
            'data-kontrol-kelilings.view', 'data-kontrol-kelilings.create', 'data-kontrol-kelilings.edit', 'data-kontrol-kelilings.delete',
            'data-regu-pengamanans.view', 'data-regu-pengamanans.create', 'data-regu-pengamanans.edit', 'data-regu-pengamanans.delete',
            'data-gangguan-kamtibs.view', 'data-gangguan-kamtibs.create', 'data-gangguan-kamtibs.edit', 'data-gangguan-kamtibs.delete',
            'data-sarpras-keamanans.view', 'data-sarpras-keamanans.create', 'data-sarpras-keamanans.edit', 'data-sarpras-keamanans.delete',
            'data-satgas-pengamanans.view', 'data-satgas-pengamanans.create', 'data-satgas-pengamanans.edit', 'data-satgas-pengamanans.delete',
            'data-informasi-intelejens.view', 'data-informasi-intelejens.create', 'data-informasis-intelejens.edit', 'data-informasis-intelejens.delete',
            'data-pelanggaran-tatibs.view', 'data-pelanggaran-tatibs.create', 'data-pelanggaran-tatibs.edit', 'data-pelanggaran-tatibs.delete',
            'data-pengawalan-pengamanans.view', 'data-pengawalan-pengamanans.create', 'data-pengawalan-pengamanans.edit', 'data-pengawalan-pengamanans.delete',
            
            //Milik TU dan Umum
            'data-harian-sdms.view', 'data-harian-sdms.create', 'data-harian-sdms.edit', 'data-harian-sdms.delete',
            'data-bulanan-sdms.view', 'data-bulanan-sdms.create', 'data-bulanan-sdms.edit', 'data-bulanan-sdms.delete',
            'data-sdm-bulanans.view', 'data-sdm-bulanans.create', 'data-sdm-bulanans.edit', 'data-sdm-bulanans.delete',
            'data-yanlik-sdms.view', 'data-yanlik-sdms.create', 'data-yanlik-sdms.edit', 'data-yanlik-sdms.delete',
            'data-publikasi-sdms.view', 'data-publikasi-sdms.create', 'data-publikasi-sdms.edit', 'data-publikasi-sdms.delete',
            'data-mou-pks.view', 'data-mou-pks.create', 'data-mou-pks.edit', 'data-mou-pks.delete',
            'data-realisasi-anggarans.view', 'data-realisasi-anggarans.create', 'data-realisasi-anggarans.edit', 'Tdata-realisasi-anggarans.delete',
            'data-pengadaan-barjas.view', 'data-pengadaan-barjas.create', 'data-pengadaan-barjas.edit', 'data-pengadaan-barjas.delete',
            'data-bmn-sdms.view', 'data-bmn-sdms.create', 'data-bmn-sdms.edit', 'data-bmn-sdms.delete',

            //Milik Pembimbing kemasyarakatan
            'PKKliens.view', 'PKKliens.create', 'PKKliens.edit', 'PKKliens.delete',
            'PKPidanas.view', 'PKPidanas.create', 'PKPidanas.edit', 'PKPidanas.delete',
            'PKJumlahs.view', 'PKJumlahs.create', 'PKJumlahs.edit', 'PKJumlahs.delete',
            'PKPendampingans.view', 'PKPendampingans.create', 'PKPendampingans.edit', 'PKPendampingans.delete',
            'PKDiversis.view', 'PKDiversis.create', 'PKDiversis.edit', 'PKDiversis.delete',
            'PKJumlahDiversis.view', 'PKJumlahDiversis.create', 'PKJumlahDiversis.edit', 'PKJumlahDiversis.delete',
            'PKPermintaanLitmass.view', 'PKPermintaanLitmass.create', 'PKPermintaanLitmass.edit', 'PKPermintaanLitmass.delete',
            'PKPenyelesaianLitmass.view', 'PKPenyelesaianLitmass.create', 'PKPenyelesaianLitmass.edit', 'PKPenyelesaianLitmass.delete',
            'PKPembinaans.view', 'PKPembinaans.create', 'PKPembinaans.edit', 'PKPembinaans.delete',
            'PKAsesmenss.view', 'PKAsesmenss.create', 'PKAsesmenss.edit', 'PKAsesmenss.delete',
            'PKPembimbingans.view', 'PKPembimbingans.create', 'PKPembimbingans.edit', 'PKPembimbingans.delete',
            'PKPengawasans.view', 'PKPengawasans.create', 'PKPengawasans.edit', 'PKPengawasans.delete',
            'PKPelanggarans.view', 'PKPelanggarans.create', 'PKPelanggarans.edit', 'PKPelanggarans.delete',
            'PKGriyas.view', 'PKGriyas.create', 'PKGriyas.edit', 'PKGriyas.delete',
            
            //15 Program Akselerasi
            'AkselerasiEnams.view', 'AkselerasiEnams.create', 'AkselerasiEnams.edit', 'AkselerasiEnams.delete',
            'AkselerasiTujuhs.view', 'AkselerasiTujuhs.create', 'AkselerasiTujuhs.edit', 'AkselerasiTujuhs.delete',
            'AkselerasiDelapans.view', 'AkselerasiDelapans.create', 'AkselerasiDelapans.edit', 'AkselerasiDelapans.delete',
            'AkselerasiSembilans.view', 'AkselerasiSembilans.create', 'AkselerasiSembilans.edit', 'AkselerasiSembilans.delete',
            'AkselerasiSepuluhs.view', 'AkselerasiSepuluhs.create', 'AkselerasiSepuluhs.edit', 'AkselerasiSepuluhs.delete',
            'AkselerasisSebelass.view', 'AkselerasisSebelass.create', 'AkselerasisSebelass.edit', 'AkselerasisSebelass.delete',
            'AkselerasisTigabelass.view', 'AkselerasisTigabelass.create', 'AkselerasisTigabelass.edit', 'AkselerasisTigabelass.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);

        $superAdmin->syncPermissions(Permission::all()); 
    }
}