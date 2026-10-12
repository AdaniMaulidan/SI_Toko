<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerDebt extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'customer_id',
        'total_debt',
        'remaining_debt',
        'due_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'total_debt' => 'decimal:2',
        'remaining_debt' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(DebtPayment::class);
    }

    /**
     * Scope hutang yang belum lunas
     */
    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->whereIn('status', ['unpaid', 'partial']);
    }

    /**
     * Scope hutang yang sudah jatuh tempo / lewat tenggat
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereIn('status', ['unpaid', 'partial'])
                      ->where('due_date', '<', Carbon::today());
    }
}
