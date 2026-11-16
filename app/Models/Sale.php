<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'invoice_no', 'customer_id', 'user_id',
        'subtotal', 'discount', 'tax', 'total', 'paid',
        'payment_method', 'status', 'notes',
        'pad_type', 'pad_number',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'paid' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function getBalanceAttribute(): float
    {
        return (float) $this->total - (float) $this->paid;
    }

    public static function nextInvoiceNo(): string
    {
        $latest = static::orderByDesc('id')->value('invoice_no');
        $number = $latest ? ((int) substr($latest, 4)) + 1 : 1;

        return 'INV-'.str_pad((string) $number, 5, '0', STR_PAD_LEFT);
    }
}
