<?php

namespace App\Shared\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @template TAttribute of object
 * @template TSubject of object
 *
 * @template-extends Voter<TAttribute, TSubject>
 */
abstract class AbstractPermissionVoter extends Voter
{
    public function supportsType(string $subjectType): bool
    {
        return is_a($subjectType, $this->getSubjectClass(), true) || \in_array($subjectType, $this->getAdditionalAuthorizedSubjects(), true);
    }

    /**
     * @return class-string<TSubject>
     */
    abstract public function getSubjectClass(): string;

    /**
     * @return list<class-string>
     */
    public function getAdditionalAuthorizedSubjects(): array
    {
        return [];
    }

    public function supportsAttribute(string $attribute): bool
    {
        return \in_array($attribute, $this->getPermissionValues(), true);
    }

    protected function getPermissionValues(): array
    {
        return call_user_func([$this->getPermissionsEnum(), 'values']);
    }

    /**
     * @return class-string<TAttribute>
     */
    abstract public function getPermissionsEnum(): string;

    #[\Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        // TODO: This should be only run in dev mode
        foreach ($this->getPermissionsCases() as $permission) {
            $methodName = $permission->getMethodName();
            if (! \method_exists($this::class, $methodName)) {
                throw new \LogicException(sprintf('Please implement the "%s" method in "%s"', $methodName, $this::class));
            }
        }

        $methodName = $this->getPermissionsEnum()::from($attribute)->getMethodName();
        return $this->{$methodName}($attribute, $subject, $token);
    }

    /**
     * @return list<TAttribute>
     */
    public function getPermissionsCases(): array
    {
        return call_user_func([$this->getPermissionsEnum(), 'cases']);
    }
}
