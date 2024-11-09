<?php

namespace App\Repositories;

use App\Models\Category;
class CategoryRepository{
    public function saveOrUpdate(Category $category){
        $category->save();
        return $category;
    }
    public function getCategoryById($id){
        return Category::where('id', $id)->first();
    }
}