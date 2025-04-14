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
            $cart = new Cart();
            $cart->setUser($user);

            $entityManager->persist($cart);
            $entityManager->flush();

            $product = self::getContainer()->get('doctrine')->getRepository(Product::class)->find(1);

            if ($product) {
                $cartItem = new CartItem(
                    $cart,
                    $product,
                    2,
                );
                //                $cartItem->setCart($cart);
                //                $cartItem->setProduct($product);
                //                $cartItem->setQuantity(2);

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

        $product = self::getContainer()->get('doctrine')->getRepository(Product::class)->find(1);

        $productData = [
            'productId' => $product->getId(),
            'quantity' => 1,
        ];

        $client->jsonRequest('POST', '/api/cart/add', $productData);
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertNotEmpty($responseData['items']);
    }

    public function testRemoveItemFromCart(): void
    {
        $client = $this->createAuthenticatedClient(true);

        $product = self::getContainer()->get('doctrine')->getRepository(Product::class)->find(1);
        $productData = [
            'productId' => $product->getId(),
            'quantity' => 3,
        ];

        $client->jsonRequest('POST', '/api/cart/add', $productData);
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertNotEmpty($responseData['items']);

        $quantity = $responseData['items'][0]['quantity'];

        $cartItemIdToModify = $responseData['items'][0]['product_id'];

        $productDataToModify = [
            'productId' => $cartItemIdToModify,
            'quantity' => 1,
        ];

        $client->jsonRequest('POST', '/api/cart/remove', $productDataToModify);
        $responseDataAfterModify = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertEquals($quantity - 1, $responseDataAfterModify['items'][0]['quantity']);
    }

    public function testClearCart(): void
    {
        $client = $this->createAuthenticatedClient(true);

        $product = self::getContainer()->get('doctrine')->getRepository(Product::class)->find(1);

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
}
