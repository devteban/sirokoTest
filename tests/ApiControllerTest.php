<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiControllerTest extends WebTestCase
{
    public function testAddProductToCart()
    {
        $client = static::createClient();
        $router = $client->getContainer()->get('router');
        $url = $router->generate('api_cart_add');

        $client->request('POST', $url, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'product_id' => 1,
            'quantity' => 2,
        ]));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Product added to cart', $responseData['message']);
    }

    public function testListCartProducts()
    {
        $client = static::createClient();
        $router = $client->getContainer()->get('router');
        $url = $router->generate('api_cart_products');

        $client->request('GET', $url);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('products', $responseData);
    }

    public function testUpdateCartQuantity()
    {
        $client = static::createClient();
        $router = $client->getContainer()->get('router');
        $url = $router->generate('api_cart_update');

        $client->request('POST', $url, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'product_id' => 1,
            'change' => 1,
        ]));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('quantity', $responseData);
        $this->assertArrayHasKey('total_price', $responseData);
    }

    public function testFinalizePurchase()
    {
        $client = static::createClient();
        $router = $client->getContainer()->get('router');
        $url = $router->generate('api_cart_finalize');

        $client->request('POST', $url, [], [], ['CONTENT_TYPE' => 'application/json']);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Purchase finalized', $responseData['message']);
    }
}