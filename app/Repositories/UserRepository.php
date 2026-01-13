<?php

namespace App\Repositories;

use App\Interfaces\UserRepoInterface;
use App\Models\User;

class UserRepository implements UserRepoInterface
{
    public function getAll()
    {
        return User::whereNot('role', 'admin')->latest()->get();
    }

    public function getById($id)
    {
        return User::findOrFail($id);
    }

    public function create(array $data)
    {
        return User::create($data);
    }

    public function update($id, array $data)
    {
        $user = User::findOrFail($id);
        $user->update($data);
        return $user;
    }

    public function delete($id)
    {
        return User::destroy($id);
    }
}
