<?php

namespace App\Tests\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Functional test for the controllers defined inside ProductControllerTest.
 * Execute the application tests using this command (requires PHPUnit to be installed):
 *
 *     $ cd your-symfony-project/
 *     $ ./vendor/bin/phpunit
 */
class ProductControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'vandana_user',
            'PHP_AUTH_PW' => 'vandana@user',
        ]);
        $crawler = $client->request('GET', '/en/product/');

        $this->assertResponseIsSuccessful();

        $this->assertCount(
            Product::NUM_ITEMS,
            $crawler->filter('article.post'),
            'The homepage displays the right number of posts.'
        );
    }

    /**
     * This test changes the database contents by creating a new comment.
     */
    public function testNewComment(): void
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'vandana_user',
            'PHP_AUTH_PW' => 'vandana@user',
        ]);
        $client->followRedirects();

        // Find first product
        $crawler = $client->request('GET', '/en/product/');
        $postLink = $crawler->filter('article.post > h2 a')->link();        
        $client->click($postLink);
        $crawler = $client->submitForm('Publish comment', [
            'comment[content]' => 'Hi, Symfony!',
        ]);
        $newComment = $crawler->filter('.post-comment')->first()->filter('div > p')->text();
        //$newComment = 'Hi, Symfony!';
        $this->assertSame('Hi, Symfony!', $newComment);
    }

    public function testAjaxSearch(): void
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'vandana_user',
            'PHP_AUTH_PW' => 'vandana@user',
        ]);

        $client->xmlHttpRequest('GET', '/en/product/search', ['q' => 'know']);

        $results = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $this->assertCount(1, $results);
        $this->assertSame('BSNL Introduces Rs 2 Prepaid Plan Extension Offer, All You Should Know', $results[0]['title']);
        $this->assertSame('Vandana Silgari', $results[0]['author']);
    }
}
