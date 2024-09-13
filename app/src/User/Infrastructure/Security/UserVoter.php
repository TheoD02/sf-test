<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Security;

use App\Shared\Security\AbstractPermissionVoter;
use App\Shared\Trait\SecurityTrait;
use App\User\Domain\Model\User;
use App\User\Domain\Security\UserPermissionEnum;
use App\User\Infrastructure\Doctrine\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @extends AbstractPermissionVoter<value-of<UserPermissionEnum>, User>
 */
class UserVoter extends AbstractPermissionVoter
{
    use SecurityTrait;

    #[\Override]
    public function getPermissionsEnum(): string
    {
        return UserPermissionEnum::class;
    }

    #[\Override]
    public function getSubjectClass(): string
    {
        return User::class;
    }

    #[\Override]
    public function getAdditionalAuthorizedSubjects(): array
    {
        return [UserRepository::class];
    }

    #[\Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        return true;
    }

    protected function canUserGetOne(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return $this->security->isGranted('ROLE_USER');
    }

    protected function canUserGetCollection(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return $this->security->isGranted('ROLE_USER');
    }

    protected function canUserCreate(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }

    protected function canUserUpdate(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        $user = $this->security->getUser();

        return $subject instanceof User && $user instanceof User && $user->getId() === $subject->getId();
    }

    protected function canUserDelete(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }
}
