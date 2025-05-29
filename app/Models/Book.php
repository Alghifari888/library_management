<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'author',
        'publisher',
        'publication_year',
        'isbn',
        'stock_quantity',
        'available_quantity',
        'cover_image_path',
        'description',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($book) {
            if (empty($book->slug)) {
                $book->slug = Str::slug($book->title);
            }
            // Set available_quantity same as stock_quantity on creation
            if (is_null($book->available_quantity)) {
                $book->available_quantity = $book->stock_quantity;
            }
        });

        static::updating(function ($book) {
            if ($book->isDirty('title') && empty($book->slug)) {
                 $book->slug = Str::slug($book->title);
            } else if ($book->isDirty('title') && !empty($book->slug)) {
                 // Opsi: selalu update slug saat title berubah
                 $book->slug = Str::slug($book->title);
            }

            // Jika stock_quantity diupdate, dan available_quantity tidak secara eksplisit diubah,
            // kita mungkin perlu logika tambahan di sini, tapi lebih baik ditangani di service layer
            // atau controller saat update stok. Untuk sekarang, biarkan seperti ini.
        });
    }

    /**
     * Get the category that owns the book.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the borrowings for the book.
     */
    public function borrowings()
    {
        return $this->hasMany(Borrowing::class);
    }
}