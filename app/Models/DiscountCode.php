<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'discount_amount',
        'discount_percentage',
        'minimum_order_value',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    public function isValid(): bool
    {
        $now = now();

        return $this->is_active
            && $this->valid_from <= $now
            && $this->valid_until >= $now;
    }

    public function isApplicable(float $orderAmount): bool
    {
        return $orderAmount >= $this->minimum_order_value;
    }
}
