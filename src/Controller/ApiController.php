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
    #[Route('/api/cart/add', name: 'api_cart_add', methods: ['POST'])]
    public function addProductToCart(Request $request, EntityManagerInterface $entityManager, CartRepository $cartRepository, CartProductRepository $cartProductRepository, ProductRepository $productRepository, SessionInterface $session): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $productId = $data['product_id'] ?? null;
            $quantity = $data['quantity'] ?? 1;

            $product = $productRepository->find($productId);
            if (!$product) {
                return new JsonResponse(['error' => 'Product not found'], 404);
            }

            $user = $this->getUser();
            if ($user) {
                $cart = $cartRepository->findCartByUser($user);
                if (!$cart) {
                    $cart = new Cart();
                    $cart->setUser($user);
                    $entityManager->persist($cart);
                }
            } else {
                $sessionId = $session->getId();
                $cart = $cartRepository->findCartBySessionId($sessionId);
                if (!$cart) {
                    $cart = new Cart();
                    $cart->setSessionId($sessionId);
                    $entityManager->persist($cart);
                }
            }

            $cartProduct = $cartProductRepository->findProductInCart($cart, $product->getId());

            if ($cartProduct) {
                $cartProduct->setQuantity($cartProduct->getQuantity() + $quantity);
            } else {
                $cartProduct = new CartProduct();
                $cartProduct->setProduct($product);
                $cartProduct->setQuantity($quantity);
                $cartProduct->setCart($cart);
                $cart->addCartProduct($cartProduct);
            }

            $entityManager->persist($cartProduct);
            $entityManager->flush();

            return new JsonResponse(['message' => 'Product added to cart'], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/api/cart/products', name: 'api_cart_products', methods: ['GET'])]
    public function listCartProducts(SessionInterface $session, CartRepository $cartRepository): JsonResponse
    {
        try {
            $user = $this->getUser();
            if ($user) {
                $cart = $cartRepository->findCartByUser($user);
            } else {
                $sessionId = $session->getId();
                $cart = $cartRepository->findCartBySessionId($sessionId);
            }

            if (!$cart) {
                return new JsonResponse(['products' => []], 200);
            }

            $products = [];
            foreach ($cart->getCartProducts() as $cartProduct) {
                $products[] = [
                    'id' => $cartProduct->getProduct()->getId(),
                    'name' => $cartProduct->getProduct()->getName(),
                    'price' => $cartProduct->getProduct()->getPrice(),
                    'quantity' => $cartProduct->getQuantity(),
                ];
            }

            return new JsonResponse(['products' => $products], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/api/cart/update', name: 'api_cart_update', methods: ['POST'])]
    public function updateCartQuantity(Request $request, EntityManagerInterface $entityManager, CartRepository $cartRepository, CartProductRepository $cartProductRepository, ProductRepository $productRepository, SessionInterface $session): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $productId = $data['product_id'] ?? null;
            $change = $data['change'] ?? 0;

            $product = $productRepository->find($productId);
            if (!$product) {
                return new JsonResponse(['error' => 'Product not found'], 404);
            }

            $user = $this->getUser();
            if ($user) {
                $cart = $cartRepository->findCartByUser($user);
            } else {
                $sessionId = $session->getId();
                $cart = $cartRepository->findCartBySessionId($sessionId);
            }

            if (!$cart) {
                return new JsonResponse(['error' => 'Cart not found'], 404);
            }

            $cartProduct = $cartProductRepository->findProductInCart($cart, $product->getId());

            if ($cartProduct) {
                $newQuantity = $cartProduct->getQuantity() + $change;
                if ($newQuantity <= 0) {
                    $cart->removeCartProduct($cartProduct);
                    $entityManager->remove($cartProduct);
                    $newQuantity = 0;
                } else {
                    $cartProduct->setQuantity($newQuantity);
                }
            } else {
                if ($change > 0) {
                    $cartProduct = new CartProduct();
                    $cartProduct->setProduct($product);
                    $cartProduct->setQuantity($change);
                    $cartProduct->setCart($cart);
                    $cart->addCartProduct($cartProduct);
                } else {
                    return new JsonResponse(['error' => 'Product not in cart'], 404);
                }
            }

            $entityManager->persist($cartProduct);
            $entityManager->flush();

            // Calculate the total price of the cart
            $totalPrice = 0;
            foreach ($cart->getCartProducts() as $cartProduct) {
                $totalPrice += $cartProduct->getProduct()->getPrice() * $cartProduct->getQuantity();
            }

            return new JsonResponse(['quantity' => $newQuantity, 'total_price' => $totalPrice], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/api/cart/finalize', name: 'api_cart_finalize', methods: ['POST'])]
    public function finalizePurchase(SessionInterface $session, EntityManagerInterface $entityManager, CartRepository $cartRepository): JsonResponse
    {
        try {
            $user = $this->getUser();
            if ($user) {
                $cart = $cartRepository->findCartByUser($user);
            } else {
                $sessionId = $session->getId();
                $cart = $cartRepository->findCartBySessionId($sessionId);
            }

            if (!$cart) {
                return new JsonResponse(['error' => 'Cart not found'], 404);
            }

            $cart->setPayedAt(new \DateTime());
            $entityManager->persist($cart);
            $entityManager->flush();

            return new JsonResponse(['message' => 'Purchase finalized'], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}