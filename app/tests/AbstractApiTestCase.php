<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Tests\Factory\UserFactory;
use App\User\Domain\Model\User;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @internal
 */
class AbstractApiTestCase extends ApiTestCase
{
    private static Client $client;

    protected ?User $user = null;

    public static function assertResponseContent(array $expected): void
    {
        $response = self::getResponse();
        self::assertEquals($expected, $response);
    }

    private static function getResponse(bool $collection = false): array
    {
        $response = self::$client->getResponse()->toArray();

        if ($collection) {
            self::assertArrayHasKey('hydra:member', $response);
            $response = $response['hydra:member'];
        }

        return $response;
    }

    public static function assertPartialResponseContent(array $expected): void
    {
        $response = self::getResponse();
        foreach ($expected as $key => $value) {
            self::assertArrayHasKey($key, $response);
            self::assertEquals($value, $response[$key]);
        }
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        if (! $this->user instanceof User) {
            $this->loginAsUser();
        }

        if (! isset($options['headers']['Content-Type'])) {
            $options['headers']['Content-Type'] = 'application/ld+json';
            if ($method === 'PATCH') {
                $options['headers']['Content-Type'] = 'application/merge-patch+json';
            }
        }

        return self::$client->request($method, $url, $options);
    }

    public function loginAsUser(array $roles = ['ROLE_USER']): void
    {
        $this->user = UserFactory::new()->withoutPersisting()->create([
            'email' => 'user@phpunit.com',
            'password' => 'test',
            'roles' => array_map(
                static fn (string|\BackedEnum $role): int|string => \is_string($role) ? $role : $role->value,
                $roles
            ),
        ])->_real();

        self::$client->loginUser($this->user);
    }

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        self::$client = static::createClient();
    }
}
