<?php

declare(strict_types=1);

namespace App\Tests\Cart\Controller;

use App\Cart\Entity\Cart;
use App\Cart\Entity\CartItem;
use App\Product\Entity\Product;
use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class CartControllerTest extends BaseWebTestCase
{
    public function createCartForUser($user): Cart
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();

        $cart = $entityManager->getRepository(Cart::class)->findOneBy(['user' => $user]);

        if (!$cart) {
            $cart = new Cart($user);

            $entityManager->persist($cart);
            $entityManager->flush();

            $product = $this->getProduct();

            if ($product) {
                $cartItem = new CartItem(
                    $cart,
                    $product,
                    2,
                );

                $entityManager->persist($cartItem);
                $entityManager->flush();
            }
        }

        return $cart;
    }

    public function testGetCart(): void
    {
        $client = $this->createAuthenticatedClient(true);

        $user = $this->getAuthenticatedUser();
        $cart = $this->createCartForUser($user);
        $cartId = $cart->getId();
        $client->jsonRequest('GET', '/api/cart');
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertArrayHasKey('cart_id', $responseData);
        self::assertEquals($cartId, $responseData['cart_id']);
    }

    public function testAddItemToCart(): void
    {
        $client = $this->createAuthenticatedClient(true);

        $product = $this->getProduct();

        $this->addItemToCart($client, $product->getId(), 1);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertNotEmpty($responseData['items']);
    }

    public function testRemoveItemFromCart(): void
    {
        $client = $this->createAuthenticatedClient(true);
        $product = $this->getProduct();

        $this->addItemToCart($client, $product->getId(), 3);

        $response = $client->getResponse();
        $cartData = json_decode($response->getContent(), true);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertNotEmpty($cartData['items']);

        $initialQuantity = $cartData['items'][0]['quantity'];

        $this->removeItemFromCart($client, $product->getId(), 1);

        $responseAfter = $client->getResponse();
        $updatedData = json_decode($responseAfter->getContent(), true);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertEquals($initialQuantity - 1, $updatedData['items'][0]['quantity']);
    }

    public function testClearCart(): void
    {
        $client = $this->createAuthenticatedClient(true);

        $product = $this->getProduct();

        $productData = [
            'productId' => $product->getId(),
            'quantity' => 1,
        ];

        $client->jsonRequest('POST', '/api/cart/add', $productData);

        $client->jsonRequest('DELETE', '/api/cart/clear');
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertEmpty($responseData['items']);
    }

    private function addItemToCart($client, int $productId, int $quantity): void
    {
        $client->jsonRequest('POST', '/api/cart/add', [
            'productId' => $productId,
            'quantity' => $quantity,
        ]);
    }

    private function removeItemFromCart($client, int $productId, int $quantity): void
    {
        $client->jsonRequest('POST', '/api/cart/remove', [
            'productId' => $productId,
            'quantity' => $quantity,
        ]);
    }

    private function getProduct(int $id = 1): ?Product
    {
        return self::getContainer()->get('doctrine')->getRepository(Product::class)->find($id);
    }
}
