<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Tests\Factory\UserFactory;
use App\User\Domain\Model\User;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * @internal
 */
abstract class AbstractApiTestCase extends ApiTestCase // @phpstan-ignore-line (don't need suffix for this class)
{
    use Factories;
    use ResetDatabase;

    private static Client $client;

    protected ?User $user = null;

    /**
     * @param array<mixed> $expected
     */
    public static function assertResponseContent(array $expected): void
    {
        $response = self::getResponse();
        self::assertEquals($expected, $response);
    }

    /**
     * @return array<mixed>
     */
    public static function getResponse(bool $collection = false): array
    {
        $response = self::$client->getResponse()?->toArray() ?? [];

        if ($collection) {
            self::assertArrayHasKey('hydra:member', $response);
            /** @var array<mixed> $response */
            $response = $response['hydra:member'] ?? [];
        }

        return $response;
    }

    /**
     * @param array<mixed> $expected
     */
    public static function assertPartialResponseContent(array $expected): void
    {
        $response = self::getResponse();
        foreach ($expected as $key => $value) {
            self::assertArrayHasKey($key, $response);
            self::assertEquals($value, $response[$key]);
        }
    }

    /**
     * @param array<mixed> $parameters
     */
    abstract public function url(array $parameters = []): string;

    /**
     * @param array<mixed> $options
     */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        if (! $this->user instanceof User) {
            $this->loginAsUser();
        }

        if (! isset($options['headers']['Content-Type'])) { // @phpstan-ignore-line offsetAccess.nonOffsetAccessible
            $options['headers']['Content-Type'] = 'application/ld+json'; // @phpstan-ignore-line offsetAccess.nonOffsetAccessible
            if ($method === 'PATCH') {
                $options['headers']['Content-Type'] = 'application/merge-patch+json'; // @phpstan-ignore-line offsetAccess.nonOffsetAccessible
            }
        }

        return self::$client->request($method, $url, $options);
    }

    /**
     * @param list<string|\BackedEnum> $roles
     * @param array<string, mixed>     $attributes
     */
    public function loginAsUser(array $roles = ['ROLE_USER'], array $attributes = [], bool $persist = false): User
    {
        $user = UserFactory::new()->withoutPersisting()->create([
            'email' => 'user@phpunit.com',
            'password' => 'test',
            'roles' => array_map(
                static fn (string|\BackedEnum $role): int|string => \is_string($role) ? $role : $role->value,
                $roles,
            ),
            ...$attributes,
        ]);

        if ($persist) {
            $user->_save();
        }

        $this->user = $user->_real();

        self::$client->loginUser($this->user, 'api');

        return $this->user;
    }

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        self::$client = static::createClient();
    }
}
