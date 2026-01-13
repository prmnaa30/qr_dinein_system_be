<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Authenticated;

#[Group('User Management', 'APIs for managing users in the system.')]
class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    #[Endpoint('Daftar Pengguna', 'Menampilkan daftar semua pengguna yang tersedia.')]
    #[Authenticated]
    #[Response(content: '[{"id": 1,"name": "John Doe","email": "john@example.com","username": "johndoe","role": "cashier","created_at": "2023-01-01T0:00.000Z","updated_at": "2023-01-01T00:00.000000Z"}]', status: 200)]
    public function index()
    {
        Gate::authorize('viewAny', User::class);
        $users = $this->userService->getAllUsers();

        return UserResource::collection($users);
    }

    #[Endpoint('Buat Pengguna Baru', 'Membuat pengguna baru dengan data yang diberikan.')]
    #[Authenticated]
    public function store(StoreUserRequest $request)
    {
        Gate::authorize('create', User::class);
        $user = $this->userService->createUser($request->validated());

        return new UserResource($user);
    }

    #[Endpoint('Tampilkan Pengguna', 'Menampilkan detail pengguna berdasarkan ID.')]
    #[Authenticated]
    #[Response(content: '{"id": 1,"name": "John Doe","email": "john@example.com","username": "johndoe","role": "cashier","created_at": "2023-01-01T00:00.000Z","updated_at": "2023-01-01T00:00.000000Z"}', status: 200)]
    public function show(string $id)
    {
        $user = $this->userService->getUserById($id);
        Gate::authorize('view', $user);

        return new UserResource($user);
    }

    #[Endpoint('Perbarui Pengguna', 'Memperbarui data pengguna berdasarkan ID.')]
    #[Authenticated]
    public function update(UpdateUserRequest $request, string $id)
    {
        $targetUser = $this->userService->getUserById($id);
        Gate::authorize('update', $targetUser);

        $user = $this->userService->updateUser($id, $request->validated());

        return new UserResource($user);
    }

    #[Endpoint('Hapus Pengguna', 'Menghapus pengguna berdasarkan ID.')]
    #[Authenticated]
    #[Response(content: '{"message": "Pengguna berhasil dihapus"}', status: 200)]
    #[Response(content: '{"message": "Error message"}', status: 400)]
    public function destroy(string $id)
    {
        $userTarget = $this->userService->getUserById($id);
        Gate::authorize('delete', $userTarget);

        try {
            $this->userService->deleteUser($id);
            return response()->json(['message' => 'Pengguna berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
