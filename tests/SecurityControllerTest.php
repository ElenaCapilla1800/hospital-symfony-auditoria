<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testLoginPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
    }

    public function testLoginWithValidCredentialsSucceeds(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $csrfToken = $crawler->filter('input[name="_csrf_token"]')->attr('value');

        $client->request('POST', '/login', [
            'email' => 'doctor@test.com',
            'password' => '123456',
            '_csrf_token' => $csrfToken,
        ]);

        // Login correcto = redirección (normalmente a la home)
        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testLoginWithInvalidCredentialsFails(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $csrfToken = $crawler->filter('input[name="_csrf_token"]')->attr('value');

        $client->request('POST', '/login', [
            'email' => 'doctor@test.com',
            'password' => 'contraseña_incorrecta',
            '_csrf_token' => $csrfToken,
        ]);

        // Login fallido = redirección de vuelta a /login (no un 200 directo)
        $this->assertResponseRedirects('/login');
    }
}