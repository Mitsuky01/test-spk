<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $guarded = [];

    public const STATUS = ['open' => 'Open', 'done' => 'Done', 'canceled' => 'Canceled'];

    public function client() { return $this->belongsTo(Client::class); }
    public function pic() { return $this->belongsTo(Pic::class); }
    public function phs() { return $this->hasMany(Ph::class); }
    public function spks() { return $this->hasMany(Spk::class); }

    /** Pendapatan = total PH (sebelum PPN) yang tidak dibatalkan */
    public function getRevenueAttribute(): int
    {
        return (int) $this->phs->where('status', '!=', 'canceled')->sum(fn ($p) => $p->net);
    }

    /** Biaya = total SPK (sebelum PPN) */
    public function getCostAttribute(): int
    {
        return (int) $this->spks->sum(fn ($s) => $s->net);
    }

    /** Profit % = (pendapatan - biaya) / pendapatan. null jika belum ada PH. */
    public function getProfitPercentAttribute(): ?float
    {
        return $this->revenue > 0
            ? round(($this->revenue - $this->cost) / $this->revenue * 100, 2)
            : null;
    }

    public function getSpkPaidAttribute(): int
    {
        return (int) $this->spks->sum(fn ($s) => $s->paid);
    }
}
