<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartProduct;
use App\Repository\CartProductRepository;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    // Endpoint to add a product to the cart
    #[Route('/api/cart/add', name: 'api_cart_add', methods: ['POST'])]
    public function addProductToCart(
        Request $request,
        EntityManagerInterface $entityManager,
        CartRepository $cartRepository,
        CartProductRepository $cartProductRepository,
        ProductRepository $productRepository,
        SessionInterface $session
    ): JsonResponse {
        try {
            // Decode the JSON request to get product ID and quantity
            $data = json_decode($request->getContent(), true);
            $productId = $data['product_id'] ?? null;
            $quantity = $data['quantity'] ?? 1;

            // Find the product by ID
            $product = $productRepository->find($productId);
            if (!$product) {
                return new JsonResponse(['error' => 'Product not found'], 404);
            }

            // Get or create the cart
            $cart = $this->getCart($cartRepository, $session, $entityManager);

            // Check if the product is already in the cart
            $cartProduct = $cartProductRepository->findProductInCart($cart, $product->getId());
            if ($cartProduct) {
                // If the product is already in the cart, update the quantity
                $cartProduct->setQuantity($cartProduct->getQuantity() + $quantity);
            } else {
                // If the product is not in the cart, create a new CartProduct entity
                $cartProduct = new CartProduct();
                $cartProduct->setProduct($product);
                $cartProduct->setQuantity($quantity);
                $cartProduct->setCart($cart);
                $cart->addCartProduct($cartProduct);
            }

            // Persist the CartProduct entity to the database
            $entityManager->persist($cartProduct);
            $entityManager->flush();

            return new JsonResponse(['message' => 'Product added to cart'], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    // Endpoint to list all products in the cart
    #[Route('/api/cart/products', name: 'api_cart_products', methods: ['GET'])]
    public function listCartProducts(
        SessionInterface $session,
        CartRepository $cartRepository
    ): JsonResponse {
        try {
            // Get the cart
            $cart = $this->getCart($cartRepository, $session);

            if (!$cart) {
                return new JsonResponse(['products' => []], 200);
            }

            // Map the CartProduct entities to an array of product details
            $products = array_map(function (CartProduct $cartProduct) {
                return [
                    'id' => $cartProduct->getProduct()->getId(),
                    'name' => $cartProduct->getProduct()->getName(),
                    'price' => $cartProduct->getProduct()->getPrice(),
                    'quantity' => $cartProduct->getQuantity(),
                ];
            }, $cart->getCartProducts()->toArray());

            return new JsonResponse(['products' => $products], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    // Endpoint to update the quantity of a product in the cart
    #[Route('/api/cart/update', name: 'api_cart_update', methods: ['POST'])]
    public function updateCartQuantity(
        Request $request,
        EntityManagerInterface $entityManager,
        CartRepository $cartRepository,
        CartProductRepository $cartProductRepository,
        ProductRepository $productRepository,
        SessionInterface $session
    ): JsonResponse {
        try {
            // Decode the JSON request to get product ID and quantity change
            $data = json_decode($request->getContent(), true);
            $productId = $data['product_id'] ?? null;
            $change = $data['change'] ?? 0;

            // Find the product by ID
            $product = $productRepository->find($productId);
            if (!$product) {
                return new JsonResponse(['error' => 'Product not found'], 404);
            }

            // Get the cart
            $cart = $this->getCart($cartRepository, $session);

            if (!$cart) {
                return new JsonResponse(['error' => 'Cart not found'], 404);
            }

            // Check if the product is already in the cart
            $cartProduct = $cartProductRepository->findProductInCart($cart, $product->getId());
            if ($cartProduct) {
                // Update the quantity of the product in the cart
                $newQuantity = $cartProduct->getQuantity() + $change;
                if ($newQuantity <= 0) {
                    // If the new quantity is zero or less, remove the product from the cart
                    $cart->removeCartProduct($cartProduct);
                    $entityManager->remove($cartProduct);
                    $newQuantity = 0;
                } else {
                    $cartProduct->setQuantity($newQuantity);
                }
            } else {
                if ($change > 0) {
                    // If the product is not in the cart and the change is positive, add the product to the cart
                    $cartProduct = new CartProduct();
                    $cartProduct->setProduct($product);
                    $cartProduct->setQuantity($change);
                    $cartProduct->setCart($cart);
                    $cart->addCartProduct($cartProduct);
                } else {
                    return new JsonResponse(['error' => 'Product not in cart'], 404);
                }
            }

            // Persist the CartProduct entity to the database
            $entityManager->persist($cartProduct);
            $entityManager->flush();

            // Calculate the total price of the cart
            $totalPrice = array_reduce($cart->getCartProducts()->toArray(), function ($total, CartProduct $cartProduct) {
                return $total + $cartProduct->getProduct()->getPrice() * $cartProduct->getQuantity();
            }, 0);

            return new JsonResponse(['quantity' => $newQuantity, 'total_price' => $totalPrice], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    // Endpoint to finalize the purchase
    #[Route('/api/cart/finalize', name: 'api_cart_finalize', methods: ['POST'])]
    public function finalizePurchase(
        SessionInterface $session,
        EntityManagerInterface $entityManager,
        CartRepository $cartRepository
    ): JsonResponse {
        try {
            // Get the cart
            $cart = $this->getCart($cartRepository, $session);

            if (!$cart) {
                return new JsonResponse(['error' => 'Cart not found'], 404);
            }

            // Set the payment date to the current date and time
            $cart->setPayedAt(new \DateTime());
            $entityManager->persist($cart);
            $entityManager->flush();

            return new JsonResponse(['message' => 'Purchase finalized'], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    // Helper method to get or create a cart
    private function getCart(
        CartRepository $cartRepository,
        SessionInterface $session,
        EntityManagerInterface $entityManager = null
    ): ?Cart {
        // Check if the user is logged in
        $user = $this->getUser();
        if ($user) {
            // Find the cart by user
            $cart = $cartRepository->findCartByUser($user);
            if (!$cart && $entityManager) {
                // If no cart is found, create a new cart for the user
                $cart = new Cart();
                $cart->setUser($user);
                $entityManager->persist($cart);
            }
        } else {
            // If the user is not logged in, use the session ID to find the cart
            $sessionId = $session->getId();
            $cart = $cartRepository->findCartBySessionId($sessionId);
            if (!$cart && $entityManager) {
                // If no cart is found, create a new cart for the session
                $cart = new Cart();
                $cart->setSessionId($sessionId);
                $entityManager->persist($cart);
            }
        }
        return $cart;
    }
}