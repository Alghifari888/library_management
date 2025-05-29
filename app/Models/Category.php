<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                 $category->slug = Str::slug($category->name);
            } else if ($category->isDirty('name') && !empty($category->slug)) {
                 // Jika slug diisi manual, jangan override, tapi jika kosong dan nama berubah, update slug
                 // Atau jika Anda ingin slug selalu update saat nama berubah:
                 $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * Get the books for the category.
     */
    public function books()
    {
        return $this->hasMany(Book::class);
    }
}