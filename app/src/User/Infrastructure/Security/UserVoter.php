<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Security;

use App\Shared\Security\AbstractPermissionVoter;
use App\Shared\Trait\SecurityTrait;
use App\User\Domain\Model\User;
use App\User\Domain\Security\UserPermissionEnum;
use App\User\Infrastructure\Doctrine\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<value-of<UserPermissionEnum>, User>
 */
class UserVoter extends AbstractPermissionVoter
{
    use SecurityTrait;

    public function getPermissionsEnum(): string
    {
        return UserPermissionEnum::class;
    }

    public function getSubjectClass(): string
    {
        return User::class;
    }

    public function getAdditionalAuthorizedSubjects(): array
    {
        return [UserRepository::class];
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return true;
    }

    protected function canUserGetOne(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if ($this->security->isGranted('ROLE_USER')) {
            return true;
        }

        return false;
    }

    protected function canUserGetCollection(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if ($this->security->isGranted('ROLE_USER')) {
            return true;
        }

        return false;
    }

    protected function canUserCreate(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return false;
    }

    protected function canUserUpdate(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        $user = $this->security->getUser();
        if ($subject instanceof User && $user instanceof User && $user?->getId() === $subject->getId()) {
            return true;
        }

        return false;
    }

    protected function canUserDelete(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return false;
    }
}
