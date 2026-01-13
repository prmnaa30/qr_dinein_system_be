<?php

namespace App\Interfaces;

interface OrderRepoInterface
{
    public function create(array $data);
    public function createItem(array $data);
    public function getById($id);
    public function getByStatus(array $statuses);
}
