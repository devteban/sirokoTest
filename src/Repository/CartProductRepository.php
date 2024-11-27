<?php

namespace App\Repository;

use App\Entity\Cart;
use App\Entity\CartProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CartProduct>
 */
class CartProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CartProduct::class);
    }

    public function findProductInCart(Cart $cart, int $productId): ?CartProduct
    {
        return $this->createQueryBuilder('cp')
            ->andWhere('cp.cart = :cart')
            ->andWhere('cp.product = :productId')
            ->setParameter('cart', $cart->getId())
            ->setParameter('productId', $productId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}