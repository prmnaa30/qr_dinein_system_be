<?php

namespace App\Services;

use App\Interfaces\ProductRepoInterface;
use Illuminate\Http\UploadedFile;

class ProductService
{
    protected $productRepository;
    protected $imageUploadService;

    public function __construct(
        ProductRepoInterface $productRepository,
        ImageUploadService $imageUploadService
    ) {
        $this->productRepository = $productRepository;
        $this->imageUploadService = $imageUploadService;
    }

    public function getAllProducts(array $filters)
    {
        return $this->productRepository->getAll($filters);
    }

    public function getProductById($id)
    {
        return $this->productRepository->getById($id);
    }

    public function createProduct(array $data)
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $path = $this->imageUploadService->uploadImage($data['image'], 'products');
            $data['image'] = $path;
        }

        return $this->productRepository->create($data);
    }

    public function updateProduct($id, array $data)
    {
        $product = $this->productRepository->getById($id);

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $this->imageUploadService->deleteImage($product->image);

            $path = $this->imageUploadService->uploadImage($data['image'], 'products');
            $data['image'] = $path;
        }

        return $this->productRepository->update($id, $data);
    }

    public function deleteProduct($id)
    {
        $product = $this->productRepository->getById($id);

        $this->imageUploadService->deleteImage($product->image);

        return $this->productRepository->delete($id);
    }
}
