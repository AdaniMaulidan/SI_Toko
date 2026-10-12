<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_name',
        'address',
        'phone',
        'email',
        'header_receipt',
        'footer_receipt',
        'tax_percentage',
        'currency_symbol',
        'logo',
    ];

    protected $casts = [
        'tax_percentage' => 'decimal:2',
    ];

    /**
     * Singleton helper untuk mengambil data pengaturan toko pertama
     */
    public static function getSettings(): self
    {
        return static::firstOrCreate([], [
            'store_name' => 'SI Toko Retail',
            'currency_symbol' => 'Rp',
            'tax_percentage' => 0,
        ]);
    }
}
