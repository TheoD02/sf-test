<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Security;

use App\User\Domain\Model\User;
use App\User\Domain\PermissionEnum;
use App\User\Domain\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

use function Symfony\Component\String\u;

/**
 * @extends Voter<value-of<PermissionEnum>, User>
 */
class UserVoter extends Voter
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    #[\Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        // if the user token does not have the required role, deny access
        if (! \in_array($attribute, $token->getRoleNames(), true)) {
            return false;
        }

        $method = 'can' . ucfirst(u($attribute)->lower()->camel()->toString());
        if (! method_exists($this, $method)) {
            throw new \LogicException(\sprintf('Method "%s" does not exist', $method));
        }

        return (bool) $this->{$method}($attribute, $subject, $token);
    }

    #[\Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        // if the subject is not a UserRepository or a User, deny access
        if (! $subject instanceof UserRepository && ! $subject instanceof User) {
            return false;
        }

        // if the attribute is not a part of the PermissionEnum, deny access
        return \in_array($attribute, PermissionEnum::values(), true);
    }

    private function canUserGetOne(): bool
    {
        return true;
    }

    private function canUserGetCollection(): bool
    {
        return true;
    }

    private function canUserCreate(): bool
    {
        return true;
    }

    private function canUserUpdate(): bool
    {
        return true;
    }

    private function canUserDelete(): bool
    {
        if (! $this->security->isGranted('ROLE_ADMIN')) {
            throw new \LogicException('You must be an admin to delete a user');
        }

        return true;
    }
}
