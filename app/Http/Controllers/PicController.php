<?php

namespace App\Http\Controllers;

use App\Models\Pic;

class PicController extends MasterController
{
    protected function cfg(): array
    {
        return [
            'model' => Pic::class,
            'route' => 'pics',
            'title' => 'PIC',
            'columns' => ['nama' => 'Nama', 'created_at' => 'Created Date'],
            'fields' => ['nama' => ['Nama', true, 6]],
            'search' => ['nama'],
            'placeholder' => 'Search PIC...',
        ];
    }
}
