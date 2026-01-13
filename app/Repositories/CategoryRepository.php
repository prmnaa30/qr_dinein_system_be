<?php

namespace App\Repositories;

use App\Interfaces\CategoryRepoInterface;
use App\Models\Category;

class CategoryRepository implements CategoryRepoInterface
{
    public function getAll()
    {
        return Category::latest()->get();
    }

    public function getById($id)
    {
        return Category::findOrFail($id);
    }

    public function create(array $data)
    {
        return Category::create($data);
    }

    public function update($id, array $data)
    {
        $category = Category::findOrFail($id);
        $category->update($data);
        return $category;
    }

    public function delete($id)
    {
        return Category::destroy($id);
    }
}
