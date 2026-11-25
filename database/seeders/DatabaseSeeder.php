<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Pic;
use App\Models\Project;
use App\Models\User;
use App\Models\Vendor;
use App\Support\Numbering;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@decorind.test'],
            ['name' => 'Admin', 'password' => Hash::make('password')]
        );

        // Data contoh supaya halaman tidak kosong. Hapus bagian di bawah ini kalau tidak perlu.
        $clients = collect([
            ['PT. Contoh Utama', 'Jl. Contoh No. 1, Jakarta', '021-1111111', '01.234.567.8-901.000'],
            ['CV. Maju Bersama', 'Jl. Merdeka No. 20, Bekasi', '021-2222222', null],
        ])->map(fn ($c) => Client::create(['nama' => $c[0], 'alamat' => $c[1], 'phone' => $c[2], 'npwp' => $c[3]]));

        $pics = collect(['Andi', 'Budi'])->map(fn ($n) => Pic::create(['nama' => $n]));

        Vendor::create([
            'nama' => 'Toko Cat Contoh', 'alamat' => 'Karawang', 'phone' => '0812-0000-0000',
            'nama_bank' => 'BCA', 'no_rekening' => '1234567890', 'nama_rekening' => 'Contoh Vendor',
        ]);

        Project::create([
            'number' => Numbering::project(), 'nama' => 'Contoh Project Booth',
            'client_id' => $clients[0]->id, 'pic_id' => $pics[0]->id, 'area' => 'Nasional',
        ]);
    }
}
