<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'description',
        'properties',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Pengguna yang melakukan aktivitas
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Objek model terkait (polymorphic)
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Helper statis untuk mencatat aktivitas log ke database
     */
    public static function record(
        string $action,
        string $description,
        ?Model $subject = null,
        array $properties = [],
        ?User $user = null
    ): self {
        $userId = $user ? $user->id : Auth::id();
        $ip = request() ? request()->ip() : null;
        $userAgent = request() ? request()->userAgent() : null;

        return self::create([
            'user_id'      => $userId,
            'action'       => strtolower($action),
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id'   => $subject ? $subject->getKey() : null,
            'description'  => $description,
            'properties'   => !empty($properties) ? $properties : null,
            'ip_address'   => $ip,
            'user_agent'   => $userAgent,
            'created_at'   => now(),
        ]);
    }

    /**
     * Scope filter aksi
     */
    public function scopeFilterAction($query, ?string $action)
    {
        if ($action && $action !== 'all') {
            return $query->where('action', strtolower($action));
        }
        return $query;
    }

    /**
     * Scope filter user
     */
    public function scopeFilterUser($query, $userId)
    {
        if ($userId && $userId !== 'all') {
            return $query->where('user_id', $userId);
        }
        return $query;
    }

    /**
     * Scope filter tanggal
     */
    public function scopeFilterDate($query, ?string $date)
    {
        if ($date) {
            return $query->whereDate('created_at', $date);
        }
        return $query;
    }
}
