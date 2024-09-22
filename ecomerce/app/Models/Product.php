<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'price', 'quantity'];

    public const path = "images/products";

    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function sales(): BelongsToMany
    {
        return $this->belongsToMany(Sale::class, 'product_sales');
    }
}
