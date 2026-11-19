<?php

namespace App\Models;

class Category extends Base
{
    public function parent() { return $this->belongsTo(Category::class, 'parent_id'); }
    public function children() { return $this->hasMany(Category::class, 'parent_id')->orderBy('sort'); }
    public function products() { return $this->hasMany(Product::class); }

    /** ids of this category and all its descendants */
    public function descendantIds(): array
    {
        $ids = [$this->id];
        foreach ($this->children as $child) $ids = array_merge($ids, $child->descendantIds());
        return $ids;
    }

    public function activeProductCount(): int
    {
        return Product::active()->whereIn('category_id', $this->descendantIds())->count();
    }
}
