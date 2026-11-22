<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MembershipTransaction extends Model
{
    protected $fillable = [
        'member_id', 'membership_level_id', 'created_by', 'transaction_number',
        'type', 'amount', 'status', 'effective_date', 'expires_at',
        'approved_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'effective_date' => 'date',
            'expires_at' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (MembershipTransaction $transaction): void {
            if ($transaction->transaction_number === null) {
                $transaction->transaction_number = self::generateTransactionNumber();
            }
        });
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(MembershipLevel::class, 'membership_level_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getPaidAmountAttribute(): float
    {
        return (float) $this->payments->sum('amount');
    }

    public function getBalanceAttribute(): float
    {
        return max(0, (float) $this->amount - $this->paid_amount);
    }

    private static function generateTransactionNumber(): string
    {
        do {
            $number = 'TXN-'.now()->format('Ymd').'-'.Str::upper(Str::random(7));
        } while (self::where('transaction_number', $number)->exists());

        return $number;
    }
}
