<?php

namespace App\Support;

class DocTotals
{
    /**
     * Hitung ulang total di server (jangan percaya angka dari browser).
     *
     * @return array{rows:array,subtotal:int,discount:int,tax_amount:int,grand_total:int}
     */
    public static function calc(array $items, int|float|string|null $discount, string $taxType): array
    {
        $rows = [];
        $subtotal = 0;

        foreach ($items as $it) {
            if (blank($it['item'] ?? null)) {
                continue;
            }
            $qty = max(1, (int) ($it['qty'] ?? 1));
            $price = max(0, (int) ($it['price'] ?? 0));
            $total = $qty * $price;
            $subtotal += $total;
            $rows[] = array_merge($it, ['qty' => $qty, 'price' => $price, 'total' => $total]);
        }

        $discount = min(max(0, (int) $discount), $subtotal);
        $net = $subtotal - $discount;
        $tax = $taxType === 'PPN' ? (int) round($net * config('spk.ppn_rate') / 100) : 0;

        return [
            'rows' => $rows,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax_amount' => $tax,
            'grand_total' => $net + $tax,
        ];
    }
}
