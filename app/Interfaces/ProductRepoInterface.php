<?php

namespace App\Interfaces;

interface ProductRepoInterface
{
    public function getAll(array $filters);
    public function getById($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
}
