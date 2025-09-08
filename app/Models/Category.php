<?php

namespace App\Models;

use App\Traits\DataTableCommonTrait;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use DataTableCommonTrait;
    protected $fillable = ['name','slug', 'parent_id'];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'category_product');
    }
}
