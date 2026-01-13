<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Authenticated;

#[Group('Product Management', 'APIs for managing products in the system.')]
class ProductController extends Controller
{
    protected $productService;

    /**
     * Inject product service.
     */
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    #[Endpoint('Daftar Produk', 'Menampilkan daftar semua produk yang tersedia.')]
    #[Authenticated]
    #[Response(content: '[{"id": 1,"name": "Nasi Goreng","description": "Nasi goreng spesial","price": 25000,"image": "nasi_goreng.jpg","category_id": 1,"created_at": "2023-01-01T00:00.000Z","updated_at": "2023-01-01T00:00.000000Z"}]', status: 200)]
    public function index(Request $request)
    {
        Gate::authorize('viewAny', User::class);
        $products = $this->productService->getAllProducts($request->all());

        return ProductResource::collection($products);
    }

    #[Endpoint('Buat Produk Baru', 'Membuat produk baru dengan data yang diberikan.')]
    #[Authenticated]
    public function store(StoreProductRequest $request)
    {
        Gate::authorize('create', User::class);
        $product = $this->productService->createProduct($request->validated());

        return new ProductResource($product);
    }

    #[Endpoint('Tampilkan Produk', 'Menampilkan detail produk berdasarkan ID.')]
    #[Authenticated]
    #[Response(content: '{"id": 1,"name": "Nasi Goreng","description": "Nasi goreng spesial","price": 25000,"image": "nasi_goreng.jpg","category_id": 1,"created_at": "2023-01-01T0:00.000Z","updated_at": "2023-01-01T00:00.000000Z"}', status: 200)]
    public function show(string $id)
    {
        $product = $this->productService->getProductById($id);
        Gate::authorize('view', $product);

        return new ProductResource($product);
    }

    #[Endpoint('Perbarui Produk', 'Memperbarui data produk berdasarkan ID.')]
    #[Authenticated]
    public function update(UpdateProductRequest $request, string $id)
    {
        $productTarget = $this->productService->getProductById($id);
        Gate::authorize('update', $productTarget);

        $updatedProduct = $this->productService->updateProduct($id, $request->validated());

        return new ProductResource($updatedProduct);
    }

    #[Endpoint('Hapus Produk', 'Menghapus produk berdasarkan ID.')]
    #[Authenticated]
    #[Response(content: '{"message": "Produk berhasil dihapus"}', status: 200)]
    #[Response(content: '{"message": "Error message"}', status: 400)]
    public function destroy(string $id)
    {
        $productTarget = $this->productService->getProductById($id);
        Gate::authorize('delete', $productTarget);

        try {
            $this->productService->deleteProduct($id);
            return response()->json(['message' => 'Produk berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
