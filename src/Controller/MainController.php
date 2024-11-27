<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findProducts();

        return $this->render('main/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/cart', name: 'app_cart')]
    public function cart(ProductRepository $productRepository): Response
    {
        return $this->render('main/cart.html.twig', [
        ]);
    }
}
