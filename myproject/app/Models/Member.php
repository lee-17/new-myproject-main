<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Member extends Model
{
    protected $fillable = [
        'user_id', 'membership_number', 'first_name', 'last_name', 'email', 'phone', 'address',
        'category', 'level', 'membership_category_id', 'membership_level_id',
        'status', 'membership_expires_at', 'latitude', 'longitude',
    ];

    protected function casts(): array
    {
        return [
            'membership_expires_at' => 'date',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Member $member): void {
            if ($member->membership_number === null) {
                $member->membership_number = self::generateMembershipNumber();
            }
        });
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function categoryRelation(): BelongsTo
    {
        return $this->belongsTo(MembershipCategory::class, 'membership_category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function levelRelation(): BelongsTo
    {
        return $this->belongsTo(MembershipLevel::class, 'membership_level_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(MembershipTransaction::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    private static function generateMembershipNumber(): string
    {
        do {
            $number = 'MEM-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (self::where('membership_number', $number)->exists());

        return $number;
    }
}
