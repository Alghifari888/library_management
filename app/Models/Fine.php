<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fine extends Model
{
    use HasFactory;

    protected $fillable = [
        'borrowing_id',
        'user_id',
        'amount',
        'reason',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'decimal:2', // Pastikan presisi desimal sesuai
    ];

    // Konstanta untuk status denda
    public const STATUS_UNPAID = 'unpaid';
    public const STATUS_PAID = 'paid';

    // BARU: Konstanta untuk tarif denda per hari
    public const RATE_PER_DAY = 2000; 

    /**
     * Get the borrowing that incurred this fine.
     */
    public function borrowing()
    {
        return $this->belongsTo(Borrowing::class);
    }

    /**
     * Get the user (member) who incurred this fine.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}