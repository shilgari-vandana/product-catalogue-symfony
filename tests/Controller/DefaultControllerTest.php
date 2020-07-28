<?php
namespace App\Tests\Controller;


use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Functional test that implements a "smoke test" of all the public and secure
 * URLs of the application.
 * Execute the application tests using this command (requires PHPUnit to be installed):
 *
 *     $ cd your-symfony-project/
 *     $ ./vendor/bin/phpunit
 */
class DefaultControllerTest extends WebTestCase
{
    /**
     * PHPUnit's data providers allow to execute the same tests repeated times
     * using a different set of data each time.
     * @dataProvider getPublicUrls
     */
    public function testPublicUrls(string $url): void
    {
        $client = static::createClient();
        $client->request('GET', $url);

        $this->assertResponseIsSuccessful(sprintf('The %s public URL loads correctly.', $url));
    }

    /**
     * A good practice for tests is to not use the service container, to make
     * them more robust. However, in this example we must access to the container
     * to get the entity manager and make a database query. The reason is that
     * product post fixtures are randomly generated and there's no guarantee that
     * a given product post slug will be available.
     */
    public function testPublicProductPost(): void
    {
        $client = static::createClient();
        // the service container is always available via the test client
        $productPost = $client->getContainer()->get('doctrine')->getRepository(Product::class)->find(241);
        $client->request('GET', '/en/login');

        $this->assertResponseIsSuccessful();
    }

    /**
     * The application contains a lot of secure URLs which shouldn't be
     * publicly accessible. This tests ensures that whenever a user tries to
     * access one of those pages, a redirection to the login form is performed.
     *
     * @dataProvider getSecureUrls
     */
    public function testSecureUrls(string $url): void
    {   
        $client = static::createClient();
        $client->request('GET', $url);
        $this->assertResponseRedirects('http://localhost/en/login', Response::HTTP_FOUND);
    }

    public function getPublicUrls(): ?\Generator
    {
        yield ['/en/login'];            
    }

    public function getSecureUrls(): ?\Generator
    {
        yield ['/en/admin/product/'];
        yield ['/en/admin/product/new'];
        yield ['/en/admin/product/8'];
        yield ['/en/admin/product/8/edit'];
        yield ['/en/product/'];
        yield ['/en/product/search']; 
    }
}
