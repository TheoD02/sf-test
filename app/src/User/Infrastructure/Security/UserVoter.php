<?php

namespace App\User\Infrastructure\Security;

use App\User\Domain\Model\User;
use App\User\Domain\PermissionEnum;
use App\User\Domain\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use function Symfony\Component\String\u;

class UserVoter extends Voter
{
    public function __construct(
        private readonly Security $security,
    )
    {
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        // if the user token does not have the required role, deny access
        if (!in_array($attribute, $token->getRoleNames(), true)) {
            return false;
        }

        $method = 'can' . ucfirst(u($attribute)->lower()->camel()->toString());
        if (!method_exists($this, $method)) {
            throw new \LogicException(sprintf('Method "%s" does not exist', $method));
        }

        if (!$this->$method($attribute, $subject, $token)) {
            return false;
        }

        return true;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // if the subject is not a UserRepository or a User, deny access
        if (!$subject instanceof UserRepository && !$subject instanceof User) {
            return false;
        }

        // if the attribute is not a part of the PermissionEnum, deny access
        if (!in_array($attribute, PermissionEnum::values(), true)) {
            return false;
        }

        return true;
    }

    private function canUserGetOne(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return true;
    }

    private function canUserGetCollection(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return true;
    }

    private function canUserCreate(string $attribute, mixed $subject, TokenInterface $token): bool
    {

        return true;
    }

    private function canUserUpdate(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return true;
    }

    private function canUserDelete(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            throw new \LogicException('You must be an admin to delete a user');
        }

        return true;
    }
}
