<?php

namespace App\Services;

use App\Interfaces\UserRepoInterface;
use Illuminate\Support\Facades\Hash;

class UserService
{
    protected $userRepository;

    /**
     * Create a new class instance.
     */
    public function __construct(UserRepoInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getAllUsers()
    {
        return $this->userRepository->getAll();
    }

    public function getUserById($id)
    {
        return $this->userRepository->getById($id);
    }

    public function createUser(array $data)
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->userRepository->create($data);
    }

    public function updateUser($id, array $data)
    {
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->userRepository->update($id, $data);
    }

    public function deleteUser($id)
    {
        $this->userRepository->delete($id);
    }
}
