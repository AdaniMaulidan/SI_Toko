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
     * Scope hutang yang belum lunas (aktif)
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
                      ->whereNotNull('due_date')
                      ->whereDate('due_date', '<', Carbon::today());
    }

    /**
     * Scope hutang yang jatuh tempo hari ini
     */
    public function scopeDueToday(Builder $query): Builder
    {
        return $query->whereIn('status', ['unpaid', 'partial'])
                      ->whereNotNull('due_date')
                      ->whereDate('due_date', Carbon::today());
    }

    /**
     * Scope hutang yang akan jatuh tempo dalam beberapa hari ke depan
     */
    public function scopeDueSoon(Builder $query, int $days = 7): Builder
    {
        return $query->whereIn('status', ['unpaid', 'partial'])
                      ->whereNotNull('due_date')
                      ->whereBetween('due_date', [Carbon::today(), Carbon::today()->addDays($days)]);
    }

    /**
     * Scope hutang yang sudah lunas
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    /**
     * Cek apakah hutang sudah melewati tanggal jatuh tempo
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== 'paid' 
            && $this->due_date 
            && $this->due_date->isPast() 
            && !$this->due_date->isToday();
    }

    /**
     * Cek apakah hutang jatuh tempo hari ini
     */
    public function getIsDueTodayAttribute(): bool
    {
        return $this->status !== 'paid' 
            && $this->due_date 
            && $this->due_date->isToday();
    }

    /**
     * Cek apakah jatuh tempo mendekat (<= 3 hari)
     */
    public function getIsDueSoonAttribute(): bool
    {
        return $this->status !== 'paid' 
            && $this->due_date 
            && $this->due_date->isFuture() 
            && $this->due_date->diffInDays(Carbon::today()) <= 3;
    }

    /**
     * Hitung berapa hari keterlambatan
     */
    public function getDaysOverdueAttribute(): int
    {
        if (!$this->is_overdue || !$this->due_date) {
            return 0;
        }

        return (int) abs(Carbon::today()->diffInDays($this->due_date));
    }

    /**
     * Hitung sisa hari menuju jatuh tempo
     */
    public function getDaysRemainingAttribute(): int
    {
        if (!$this->due_date || $this->is_overdue) {
            return 0;
        }

        return (int) abs(Carbon::today()->diffInDays($this->due_date));
    }

    /**
     * Nominal yang sudah dibayarkan (total - sisa)
     */
    public function getPaidAmountAttribute(): float
    {
        return (float) max(0, $this->total_debt - $this->remaining_debt);
    }

    /**
     * Persentase pelunasan (0 - 100%)
     */
    public function getPaymentPercentageAttribute(): float
    {
        if ($this->total_debt <= 0) {
            return 100;
        }

        return round(($this->paid_amount / $this->total_debt) * 100, 1);
    }
}
