<?php

namespace App\Repositories;

use App\Interfaces\TableRepoInterface;
use App\Models\Table;

class TableRepository implements TableRepoInterface
{
    public function getAll()
    {
        return Table::latest()->get();
    }

    public function getById($id)
    {
        return Table::findOrFail($id);
    }

    public function create(array $data)
    {
        return Table::create($data);
    }

    public function delete($id)
    {
        return Table::destroy($id);
    }
}
