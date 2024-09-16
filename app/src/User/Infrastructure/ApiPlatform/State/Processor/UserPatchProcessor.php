<?php

declare(strict_types=1);

namespace App\User\Infrastructure\ApiPlatform\State\Processor;

use ApiPlatform\Metadata\Operation;
use App\User\Domain\Security\UserPermissionEnum;
use App\User\Infrastructure\ApiPlatform\Payload\PatchUserInput;
use App\User\Infrastructure\ApiPlatform\Resource\UserResource;
use App\User\Infrastructure\Doctrine\UserRepository;
use AutoMapper\AutoMapper;
use AutoMapper\AutoMapperInterface;
use Doctrine\ORM\EntityManagerInterface;
use Rekalogika\ApiLite\Exception\NotFoundException;
use Rekalogika\ApiLite\State\AbstractProcessor;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

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

        $this->denyAccessUnlessGranted(UserPermissionEnum::UPDATE->value, $user);

        try {
            $this->map($data, $user);
        } catch (\Throwable $e) { // TODO: Manage this in event exception listener (can be thrown many times)
            $previous = $e->getPrevious();
            if ($previous instanceof HttpException) {
                throw $previous;
            }
        }

        $this->entityManager->flush();

        return $this->map($user, UserResource::class);
    }
}
