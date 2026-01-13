<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Authenticated;

#[Group('Category Management', 'APIs for managing categories in the system.')]
class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    #[Endpoint('Daftar Kategori', 'Menampilkan daftar semua kategori yang tersedia.')]
    #[Authenticated]
    #[Response(content: '[{"id": 1,"name": "Makanan","description": "Berbagai jenis makanan","created_at": "2023-01-01T00:00.000Z","updated_at": "2023-01-01T00:00.000000Z"}]', status: 200)]
    public function index()
    {
        Gate::authorize('viewAny', Category::class);
        $categories = $this->categoryService->getAllCategories();

        return CategoryResource::collection($categories);
    }

    #[Endpoint('Buat Kategori Baru', 'Membuat kategori baru dengan data yang diberikan.')]
    #[Authenticated]
    public function store(StoreCategoryRequest $request)
    {
        Gate::authorize('create', Category::class);
        $category = $this->categoryService->createCategory($request->validated());

        return new CategoryResource($category);
    }

    #[Endpoint('Tampilkan Kategori', 'Menampilkan detail kategori berdasarkan ID.')]
    #[Authenticated]
    #[Response(content: '{"id": 1,"name": "Makanan","description": "Berbagai jenis makanan","created_at": "2023-01T00:00.000Z","updated_at": "2023-01-01T00:00.000000Z"}', status: 200)]
    public function show(string $id)
    {
        $category = $this->categoryService->getCategoryById($id);
        Gate::authorize('view', $category);

        return new CategoryResource($category);
    }

    #[Endpoint('Perbarui Kategori', 'Memperbarui data kategori berdasarkan ID.')]
    #[Authenticated]
    public function update(UpdateCategoryRequest $request, string $id)
    {
        $categoryTarget = $this->categoryService->getCategoryById($id);
        Gate::authorize('update', $categoryTarget);

        $updatedCategory = $this->categoryService->updateCategory($id, $request->validated());

        return new CategoryResource($updatedCategory);
    }

    #[Endpoint('Hapus Kategori', 'Menghapus kategori berdasarkan ID.')]
    #[Authenticated]
    #[Response(content: '{"message": "Kategori berhasil dihapus"}', status: 200)]
    #[Response(content: '{"message": "Error message"}', status: 400)]
    public function destroy(string $id)
    {
        $categoryTarget = $this->categoryService->getCategoryById($id);
        Gate::authorize('delete', $categoryTarget);

        try {
            $this->categoryService->deleteCategory($id);
            return response()->json(['message' => 'Kategori berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
