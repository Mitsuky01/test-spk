<?php

namespace App\Http\Controllers;

use App\Models\Vendor;

class VendorController extends MasterController
{
    protected function cfg(): array
    {
        return [
            'model' => Vendor::class,
            'route' => 'vendors',
            'title' => 'Vendor',
            'columns' => [
                'nama' => 'Nama', 'alamat' => 'Alamat', 'phone' => 'Phone',
                'no_rekening' => 'No Rekening', 'nama_rekening' => 'Nama Rekening', 'created_at' => 'Created Date',
            ],
            'fields' => [
                'nama' => ['Nama', true, 6],
                'alamat' => ['Alamat', true, 6],
                'phone' => ['Phone', true, 6],
                'npwp' => ['NPWP', false, 6],
                'nama_bank' => ['Nama Bank', true, 6],
                'no_rekening' => ['No Rekening', true, 6],
                'nama_rekening' => ['Nama Rekening', true, 6],
            ],
            'search' => ['nama', 'alamat', 'phone', 'no_rekening', 'nama_rekening'],
            'placeholder' => 'Search Nama, Alamat, Phone, No Rek, Nama Rek ...',
        ];
    }
}
