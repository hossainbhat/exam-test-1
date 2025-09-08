<?php

namespace App\Models;

use App\Traits\DataTableCommonTrait;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use DataTableCommonTrait;
    protected $fillable = ['name','slug', 'description'];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product');
    }
}
