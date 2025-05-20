<?php

declare(strict_types=1);

namespace App\Product\Controller;

use App\Product\Request\CreateProductRequest;
use App\Product\Request\UpdateProductRequest;
use App\Product\Serializer\SerializationGroups;
use App\Product\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/api')]
final class ProductController extends AbstractController
{
    public function __construct(
        private ProductService $productService,
        private SerializerInterface $serializer,
        private TranslatorInterface $translator,
    ) {}

    #[Route('/products', methods: [Request::METHOD_GET])]
    public function list(): JsonResponse
    {
        $products = $this->productService->getAllProducts();

        return new JsonResponse(
            $this->serializer->normalize($products, 'json', ['groups' => SerializationGroups::PRODUCT_READ]),
            Response::HTTP_OK,
        );
    }

    #[Route('/products/{id}', methods: [Request::METHOD_GET])]
    public function getProductById(int $id): JsonResponse
    {
        $product = $this->productService->getProductById($id);
        if (!$product) {
            return new JsonResponse(['error' => $this->translator->trans('product.not_found')], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(
            $this->serializer->normalize($product, 'json', ['groups' => SerializationGroups::PRODUCT_READ]),
            Response::HTTP_OK,
        );
    }

    #[Route('/products', methods: [Request::METHOD_POST])]
    public function create(
        #[MapRequestPayload]
        CreateProductRequest $request,
    ): JsonResponse {
        $product = $this->productService->createProduct($request);

        return $this->json($product, Response::HTTP_CREATED, [], ['groups' => SerializationGroups::PRODUCT_READ]);
    }

    #[Route('/products/{id}', methods: [Request::METHOD_PUT])]
    public function update(
        int $id,
        #[MapRequestPayload]
        UpdateProductRequest $request,
    ): JsonResponse {
        $product = $this->productService->updateProduct($id, $request);

        if (!$product) {
            return new JsonResponse(['error' => $this->translator->trans('product.not_found')], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(
            $this->serializer->normalize($product, 'json', ['groups' => SerializationGroups::PRODUCT_READ]),
            Response::HTTP_OK,
        );
    }

    #[Route('/products/{id}', methods: [Request::METHOD_DELETE])]
    public function delete(int $id): JsonResponse
    {
        $deleted = $this->productService->deleteProduct($id);

        if (!$deleted) {
            return new JsonResponse(['error' => $this->translator->trans('product.not_found')], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['message' => $this->translator->trans('product.deleted_successfully')], Response::HTTP_NO_CONTENT);
    }
}
