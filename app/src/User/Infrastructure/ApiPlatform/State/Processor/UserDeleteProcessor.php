<?php

namespace App\User\Infrastructure\ApiPlatform\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\User\Domain\Repository\UserRepository;
use App\User\Infrastructure\ApiPlatform\Payload\CreateUserInput;
use App\User\Infrastructure\ApiPlatform\Resource\UserResource;
use Doctrine\ORM\EntityManagerInterface;
use Rekalogika\ApiLite\Exception\NotFoundException;
use Rekalogika\ApiLite\State\AbstractProcessor;

/**
 * @extends AbstractProcessor<void, void>
 */
class UserDeleteProcessor extends AbstractProcessor
{
    public function __construct(
        private readonly UserRepository         $userRepository,
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $user = $this->userRepository->find($uriVariables['id'] ?? null) ?? throw new NotFoundException();


        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }
}
