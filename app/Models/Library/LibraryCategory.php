<?php

namespace App\Models\Library;

use Illuminate\Database\Eloquent\Model;

class LibraryCategory extends Model
{
    protected $table = 'library_categories';

    protected $fillable = ['name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function books()
    {
        return $this->hasMany(LibraryBook::class, 'category_id');
    }
}
