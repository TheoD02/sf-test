<?php

declare(strict_types=1);

namespace App\User\Infrastructure\ApiPlatform\State\Processor;

use ApiPlatform\Metadata\Operation;
use App\User\Domain\Model\User;
use App\User\Domain\Security\UserPermissionEnum;
use App\User\Infrastructure\ApiPlatform\Payload\CreateUserInput;
use App\User\Infrastructure\ApiPlatform\Resource\UserResource;
use App\User\Infrastructure\Doctrine\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Rekalogika\ApiLite\State\AbstractProcessor;

/**
 * @extends AbstractProcessor<CreateUserInput, UserResource>
 */
class UserPostProcessor extends AbstractProcessor
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
    ) {
    }

    #[\Override]
    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): UserResource {
        $this->denyAccessUnlessGranted(UserPermissionEnum::CREATE->value, $this->userRepository);

        $user = $this->map($data, User::class);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->map($user, UserResource::class);
    }
}
