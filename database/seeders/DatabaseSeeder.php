<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            SettingSeeder::class,
            CategorySeeder::class,
            PostSeeder::class,
            RtmfFrontendSeeder::class,
            RtmfAduanSeeder::class,
            RtmfPendaftaranPantasSeeder::class,
            RtmfProfilingAsnafSeeder::class,
            RtmfProfilingAsnafPhase2Seeder::class,
            RtmfProfilingOrgRcpSeeder::class,
            RtmfProfilingPhase4Seeder::class,
            RtmfProfilingPhase5Seeder::class,
            RtmfBantuanPhase1Seeder::class,
            RtmfBantuanPhase2Seeder::class,
            RtmfBantuanPhase3Seeder::class,
            RtmfBantuanPhase4Seeder::class,
            RtmfPenolongAmilPhase1Seeder::class,
            RtmfPenolongAmilPhase2Seeder::class,
            RtmfPengurusanTunaiPhase1Seeder::class,
            RtmfPengurusanTunaiPhase2Seeder::class,
            RtmfPengurusanTunaiPhase3Seeder::class,
            RtmfPengurusanTunaiPhase4Seeder::class,
            RtmfPengurusanAduanPhase1Seeder::class,
            RtmfPengurusanAduanPhase2Seeder::class,
            RtmfPentadbirSistemPhase1Seeder::class,
            RtmfPentadbirSistemPhase2Seeder::class,
            RtmfMissedProfilingSeeder::class,
            RtmfPublicPortalSeeder::class,
            RtmfSistemGlobalSeeder::class,
        ]);
    }
}
