<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Spk extends Model
{
    protected $guarded = [];
    protected $casts = ['tanggal' => 'date'];

    public const STATUS = ['unpaid' => 'Unpaid', 'dp' => 'DP', 'paid' => 'Paid'];

    public function vendor() { return $this->belongsTo(Vendor::class); }
    public function project() { return $this->belongsTo(Project::class); }
    public function client() { return $this->belongsTo(Client::class); }
    public function pic() { return $this->belongsTo(Pic::class); }
    public function items() { return $this->hasMany(SpkItem::class); }
    public function payments() { return $this->hasMany(SpkPayment::class); }

    public function getNetAttribute(): int
    {
        return $this->subtotal - $this->discount;
    }

    public function getPaidAttribute(): int
    {
        return (int) $this->payments->sum('amount');
    }

    public function getRestAttribute(): int
    {
        return max(0, $this->grand_total - $this->paid);
    }

    /** 0..1 */
    public function getPaidRatioAttribute(): float
    {
        return $this->grand_total > 0 ? min(1, $this->paid / $this->grand_total) : 0;
    }

    /** Status mengikuti pembayaran yang tercatat */
    public function syncStatus(): void
    {
        $this->load('payments');
        $this->status = $this->paid <= 0 ? 'unpaid' : ($this->paid >= $this->grand_total ? 'paid' : 'dp');
        $this->save();
    }
}
