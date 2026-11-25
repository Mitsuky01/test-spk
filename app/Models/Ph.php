<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ph extends Model
{
    protected $guarded = [];
    protected $casts = ['tanggal' => 'date'];

    public const STATUS = [
        'on_process' => 'On Process',
        'released'   => 'Released',
        'po'         => 'Purchase Order',
        'invoice'    => 'Invoice',
        'canceled'   => 'Canceled',
    ];

    public function project() { return $this->belongsTo(Project::class); }
    public function client() { return $this->belongsTo(Client::class); }
    public function pic() { return $this->belongsTo(Pic::class); }
    public function items() { return $this->hasMany(PhItem::class); }

    /** Nilai sebelum PPN (setelah diskon) */
    public function getNetAttribute(): int
    {
        return $this->subtotal - $this->discount;
    }
}
