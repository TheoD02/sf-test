<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Factory\UserFactory;
use App\User\Domain\Model\User;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AbstractApiTestCase extends ApiTestCase
{
    protected ?User $user = null;
    protected string $jwtToken;

    private Client $client;

    public function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        if ($this->user === null) {
            $this->loginAsUser();
        }

        return $this->client->request($method, $url, $options);
    }

    public function loginAsUser(array $roles = ['ROLE_USER']): void
    {
        $this->user = UserFactory::new()->createOne([
            'email' => 'test@test.com',
            'password' => 'test',
            'roles' => $roles,
        ]);

        $this->client->loginUser($this->user);
    }
}
