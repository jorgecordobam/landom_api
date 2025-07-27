<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'contact_person',
        'email',
        'phone',
        'address',
        'website',
        'category',
        'rating',
        'status'
    ];

    protected $casts = [
        'rating' => 'decimal:2',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
