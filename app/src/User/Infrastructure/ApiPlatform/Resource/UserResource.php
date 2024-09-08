<?php

declare(strict_types=1);

namespace App\User\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\User\Infrastructure\ApiPlatform\Payload\CreateUserInput;
use App\User\Infrastructure\ApiPlatform\Payload\PatchUserInput;
use App\User\Infrastructure\ApiPlatform\State\Processor\UserDeleteProcessor;
use App\User\Infrastructure\ApiPlatform\State\Processor\UserPatchProcessor;
use App\User\Infrastructure\ApiPlatform\State\Processor\UserPostProcessor;
use App\User\Infrastructure\ApiPlatform\State\Provider\UserCollectionProvider;
use App\User\Infrastructure\ApiPlatform\State\Provider\UserMeProvider;
use App\User\Infrastructure\ApiPlatform\State\Provider\UserProvider;

#[ApiResource(
    shortName: 'User',
    operations: [
        new GetCollection(provider: UserCollectionProvider::class),
        new Get(provider: UserProvider::class),
        new Get(uriTemplate: '/me', provider: UserMeProvider::class),
        new Post(input: CreateUserInput::class, processor: UserPostProcessor::class),
        new Patch(input: PatchUserInput::class, read: false, processor: UserPatchProcessor::class),
        new Delete(read: false, processor: UserDeleteProcessor::class),
    ]
)]
class UserResource
{
    private ?int $id = null;

    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    private array $roles = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param list<string> $roles
     *
     * @return $this
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }
}
