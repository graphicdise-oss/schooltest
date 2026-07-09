<?php

namespace App\Models\Library;

use Illuminate\Database\Eloquent\Model;

class LibraryBook extends Model
{
    protected $table = 'library_books';

    protected $fillable = [
        'category_id', 'code', 'title', 'author', 'publisher', 'isbn',
        'total_copies', 'available_copies', 'shelf_location', 'price',
        'cover_image', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function category()
    {
        return $this->belongsTo(LibraryCategory::class, 'category_id');
    }

    public function loans()
    {
        return $this->hasMany(LibraryLoan::class, 'book_id');
    }

    public function getOnLoanCountAttribute(): int
    {
        return max(0, $this->total_copies - $this->available_copies);
    }
}
