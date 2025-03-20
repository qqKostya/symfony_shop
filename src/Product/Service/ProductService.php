<?php

namespace App\Product\Service;

use App\Product\Entity\Product;
use App\Product\Repository\ProductRepository;
use App\Product\Request\RequestCreateProduct;
use App\Product\Request\RequestUpdateProduct;

class ProductService
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getProductById(int $productId): ?Product
    {
        return $this->productRepository->findById($productId);
    }

    public function getAllProducts(): array
    {
        return $this->productRepository->findAll();
    }

    public function createProduct(RequestCreateProduct $request): Product
    {
        $product = new Product();
        $product->setName($request->name);
        $product->setDescription($request->description);
        $product->setCost($request->cost);
        $product->setTax($request->tax);
        $product->setWeight($request->weight);
        $product->setHeight($request->height);
        $product->setWidth($request->width);
        $product->setLength($request->length);

        $this->productRepository->save($product);
        return $product;
    }

    public function updateProduct(int $id, RequestUpdateProduct $request): ?Product
    {
        $product = $this->productRepository->findById($id);
        if (!$product) {
            return null;
        }

        if ($request->name !== null) {
            $product->setName($request->name);
        }

        if ($request->description !== null) {
            $product->setDescription($request->description);
        }

        if ($request->cost !== null) {
            $product->setCost($request->cost);
        }

        if ($request->tax !== null) {
            $product->setTax($request->tax);
        }

        if ($request->weight !== null) {
            $product->setWeight($request->weight);
        }

        if ($request->height !== null) {
            $product->setHeight($request->height);
        }

        if ($request->width !== null) {
            $product->setWidth($request->width);
        }

        if ($request->length !== null) {
            $product->setLength($request->length);
        }

        $this->productRepository->save($product);

        return $product;
    }

    public function deleteProduct(int $id): bool
    {
        $product = $this->productRepository->findById($id);
        if (!$product) {
            return false;
        }

        $this->productRepository->delete($product);

        return true;
    }
}
