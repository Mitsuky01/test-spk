<?php

namespace App\Http\Controllers;

use App\Models\Client;

class ClientController extends MasterController
{
    protected function cfg(): array
    {
        return [
            'model' => Client::class,
            'route' => 'clients',
            'title' => 'Client',
            'columns' => ['nama' => 'Nama', 'alamat' => 'Alamat', 'phone' => 'Phone', 'npwp' => 'NPWP', 'created_at' => 'Created Date'],
            'fields' => [
                'nama' => ['Nama', true, 6],
                'alamat' => ['Alamat', true, 6],
                'phone' => ['Phone', true, 6],
                'npwp' => ['NPWP', false, 6],
            ],
            'search' => ['nama', 'alamat', 'phone', 'npwp'],
            'placeholder' => 'Search Nama, Alamat, Phone, NPWP ...',
        ];
    }
}
