<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'user_id',
        'expense_category',
        'amount',
        'date',
        'description',
        'receipt_image',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('date', Carbon::now()->month)
                      ->whereYear('date', Carbon::now()->year);
    }
}
