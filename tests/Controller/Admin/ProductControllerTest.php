<?php

namespace App\Tests\Controller\Admin;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Functional test for the controllers defined inside the ProductController used
 * for managing the product in the backend.
 * Execute the application tests using this command (requires PHPUnit to be installed):
 *
 *     $ cd your-symfony-project/
 *     $ ./vendor/bin/phpunit
 */
class ProductController extends WebTestCase
{
    /**
     * @dataProvider getUrlsForRegularUsers
     */
    public function testAccessDeniedForRegularUsers(string $httpMethod, string $url): void
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'vandana_user',
            'PHP_AUTH_PW' => 'vandana@user',
        ]);

        $client->request($httpMethod, $url);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function getUrlsForRegularUsers(): ?\Generator
    {
        yield ['GET', '/en/admin/product/'];
        yield ['GET', '/en/admin/product/8'];
        yield ['GET', '/en/admin/product/8/edit'];
        //yield ['POST', '/en/admin/product/8/delete'];
    }

    public function testAdminBackendHomePage(): void
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'vandana_admin',
            'PHP_AUTH_PW' => 'vandana@admin',
        ]);
        $client->request('GET', '/en/admin/product/');

        $this->assertResponseIsSuccessful();        
    }

    /**
     * This test changes the database contents by creating a new product post.
     */
    public function testAdminNewProduct(): void
    {
        $name = 'Product Title '.mt_rand();
        $summary = $this->generateRandomString(255);
        $content = $this->generateRandomString(1024);
        $price = 600900;
        //$imageUrl = "https://telecomtalk.info/wp-content/uploads/2020/05/bsnl-rs2-prepaid-plan-extension-you-should-know.jpg";
      
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'vandana_admin',
            'PHP_AUTH_PW' => 'vandana@admin',
        ]);

        $crawler = $client->request('POST', '/en/admin/product/new');
        $form = $crawler->selectButton('Create product')->form([
            'product[name]' => $name,
            'product[summary]' => $summary,
            'product[content]' => $content,
            'product[price]' => $price,
            //'product[imageUrl]' => $imageUrl,
        ]);
        $client->submit($form);

        /** @var \App\Entity\Product $product */
        $product = self::$container->get(ProductRepository::class)->findOneBy(array('name' => $name));
        $this->assertNotNull($product);
        $this->assertSame($name, $product->getName());
        $this->assertSame($content, $product->getContent());
    }

    public function testAdminNewDuplicatedProduct(): void
    {
        $name = 'Product Title '.mt_rand();
        $summary = $this->generateRandomString(255);
        $content = $this->generateRandomString(1024);
        $price = 600900;
        //$imageUrl = "https://telecomtalk.info/wp-content/uploads/2020/05/bsnl-rs2-prepaid-plan-extension-you-should-know.jpg";
      
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'vandana_admin',
            'PHP_AUTH_PW' => 'vandana@admin',
        ]);

        $crawler = $client->request('POST', '/en/admin/product/new');
        $form = $crawler->selectButton('Create product')->form([
            'product[name]' => $name,
            'product[summary]' => $summary,
            'product[content]' => $content,
            'product[price]' => $price,
            //'product[imageUrl]' => $imageUrl,
        ]);
        $client->submit($form);

        // post titles must be unique, so trying to create the same post twice should result in an error
        $client->submit($form);

        $this->assertSelectorTextSame('form .form-group.has-error label', 'Title');
    }

    public function testAdminShowProduct(): void
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'vandana_admin',
            'PHP_AUTH_PW' => 'vandana@admin',
        ]);
        $client->request('GET', '/en/admin/product/8');

        $this->assertResponseIsSuccessful();
    }

    /**
     * This test changes the database contents by editing a product. 
     */
    public function testAdminEditProduct(): void
    {
        $newProductPostTitle = 'Product Title '.mt_rand();

        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'vandana_admin',
            'PHP_AUTH_PW' => 'vandana@admin',
        ]);
        $client->request('GET', '/en/admin/product/8/edit');
        $client->submitForm('Save changes', [
            'product[name]' => $newProductPostTitle,
        ]);

        $this->assertResponseRedirects('/en/admin/product/8/edit', Response::HTTP_FOUND);

        /** @var \App\Entity\Product $product */
        $product = self::$container->get(ProductRepository::class)->find(8);
        $this->assertSame($newProductPostTitle, $product->getName());
    }

    /**
     * This test changes the database contents by deleting a product.
     */
    public function testAdminDeleteProduct(): void
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'vandana_admin',
            'PHP_AUTH_PW' => 'vandana@admin',
        ]);
        
        $maxProductId = self::$container->get(ProductRepository::class)->findMaxId();        

        $crawler = $client->request('GET', '/en/admin/product/'.$maxProductId);
        $client->submit($crawler->filter('#delete-form-product')->form());

        $this->assertResponseRedirects('/en/admin/product/', Response::HTTP_FOUND);

        $product = self::$container->get(ProductRepository::class)->find($maxProductId);
        $this->assertNull($product);
    }

    private function generateRandomString(int $length): string
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return mb_substr(str_shuffle(str_repeat($chars, ceil($length / mb_strlen($chars)))), 1, $length);
    }
}
