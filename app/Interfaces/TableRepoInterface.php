<?php

namespace App\Interfaces;

interface TableRepoInterface
{
    public function getAll();
    public function getById($id);
    public function create(array $data);
    public function delete($id);
}
