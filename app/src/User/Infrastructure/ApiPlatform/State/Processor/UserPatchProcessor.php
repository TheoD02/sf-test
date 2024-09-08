<?php

declare(strict_types=1);

namespace App\User\Infrastructure\ApiPlatform\State\Processor;

use ApiPlatform\Metadata\Operation;
use App\User\Domain\Repository\UserRepository;
use App\User\Infrastructure\ApiPlatform\Payload\PatchUserInput;
use App\User\Infrastructure\ApiPlatform\Resource\UserResource;
use Doctrine\ORM\EntityManagerInterface;
use Rekalogika\ApiLite\Exception\NotFoundException;
use Rekalogika\ApiLite\State\AbstractProcessor;

/**
 * @extends AbstractProcessor<PatchUserInput, UserResource>
 */
class UserPatchProcessor extends AbstractProcessor
{
    public function __construct(
        private readonly UserRepository         $userRepository,
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    #[\Override]
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): UserResource
    {
        $user = $this->userRepository->find($uriVariables['id'] ?? null) ?? throw new NotFoundException();

        $this->map($data, $user);

        $this->entityManager->flush();

        return $this->map($user, UserResource::class);
    }
}
