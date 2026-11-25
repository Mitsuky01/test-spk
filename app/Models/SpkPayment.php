<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpkPayment extends Model
{
    protected $guarded = [];
    protected $casts = ['paid_at' => 'date'];
}
