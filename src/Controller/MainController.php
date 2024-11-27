<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(ProductRepository $productRepository, EntityManagerInterface $entityManager): Response
    {
        $products = $productRepository->findProducts();

        if (empty($products)) {
            $product1 = new Product();
            $product1->setName('Pantalones blancos')
                ->setPrice(29.99)
                ->setShortDescription('Cómodos pantalones deportivos')
                ->setImage('crop_center.webp');

            $product2 = new Product();
            $product2->setName('Pantalones lila')
                ->setPrice(79.99)
                ->setShortDescription('Pantalones deportivos de alta calidad')
                ->setImage('crop_center-_1_.webp');

            $entityManager->persist($product1);
            $entityManager->persist($product2);
            $entityManager->flush();

            $products = $productRepository->findProducts();
        }

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
