<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Payment extends Model
{
    protected $fillable = [
        'membership_transaction_id', 'received_by', 'payment_reference',
        'amount', 'payment_method', 'paid_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Payment $payment): void {
            if ($payment->payment_reference === null) {
                $payment->payment_reference = self::generatePaymentReference();
            }
        });
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(MembershipTransaction::class, 'membership_transaction_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    private static function generatePaymentReference(): string
    {
        do {
            $reference = 'PAY-'.now()->format('Ymd').'-'.Str::upper(Str::random(7));
        } while (self::where('payment_reference', $reference)->exists());

        return $reference;
    }
}
