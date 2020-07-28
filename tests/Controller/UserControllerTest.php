<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Functional test for the controllers defined inside the UserController used
 * for managing the current logged user.
 * Execute the application tests using this command (requires PHPUnit to be installed):
 *
 *     $ cd your-symfony-project/
 *     $ ./vendor/bin/phpunit
 */
class UserControllerTest extends WebTestCase
{
    /**
     * @dataProvider getUrlsForAnonymousUsers
     */
    public function testAccessDeniedForAnonymousUsers(string $httpMethod, string $url): void
    {
        $client = static::createClient();
        $client->request($httpMethod, $url);

        $this->assertResponseRedirects(
            'http://localhost/en/login',
            Response::HTTP_FOUND,
            sprintf('The %s secure URL redirects to the login form.', $url)
        );
    }

    public function getUrlsForAnonymousUsers(): ?\Generator
    {
        yield ['GET', '/en/profile/edit'];
        yield ['GET', '/en/profile/change-password'];
    }

    public function testEditUser(): void
    {
        $newUserEmail = 'admin_vadnana@symfony.com';

        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'vandana_admin',
            'PHP_AUTH_PW' => 'vandana@admin',
        ]);
        $client->request('GET', '/en/profile/edit');
        $client->submitForm('Save changes', [
            'user[email]' => $newUserEmail,
        ]);

        // /** @var \App\Entity\User $user */
        $user = self::$container->get(UserRepository::class)->findOneByEmail($newUserEmail);

        $this->assertNotNull($user);
        $this->assertSame($newUserEmail, $user->getEmail());
    }

    public function testChangePassword(): void
    {
        $newUserPassword = 'vandana@admin';

        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'vandana_admin',
            'PHP_AUTH_PW' => 'vandana@admin',
        ]);
        $client->request('GET', '/en/profile/change-password');
        $client->submitForm('Save changes', [
            'change_password[currentPassword]' => 'vandana@admin',
            'change_password[newPassword][first]' => $newUserPassword,
            'change_password[newPassword][second]' => $newUserPassword,
        ]);

        $this->assertResponseRedirects(
            '/en/logout',
            Response::HTTP_FOUND,
            'Changing password logout the user.'
        );
    }
}
