<?php

declare(strict_types=1);

namespace App\Shared\Security;

use App\Shared\Trait\PermissionTrait;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @template TAttribute of string
 * @template TSubject of mixed
 *
 * @template-extends Voter<TAttribute, TSubject>
 */
abstract class AbstractPermissionVoter extends Voter
{
    #[\Override]
    public function supportsType(string $subjectType): bool
    {
        return is_a($subjectType, $this->getSubjectClass(), true) || \in_array(
                $subjectType,
                $this->getAdditionalAuthorizedSubjects(),
                true,
            );
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

    #[\Override]
    public function supportsAttribute(string $attribute): bool
    {
        return \in_array($attribute, $this->getPermissionValues(), true);
    }

    /**
     * @return list<string>
     */
    protected function getPermissionValues(): array
    {
        $class = $this->getPermissionsEnum();

        /** @var list<string> */
        return $class::values();
    }

    /**
     * @return class-string
     */
    abstract public function getPermissionsEnum(): string;

    #[\Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        // TODO: This should be only run in dev mode
        foreach ($this->getPermissionsCases() as $backedEnum) {
            if (! method_exists($backedEnum, 'getMethodName')) {
                throw new \LogicException(\sprintf(
                    'Please add use of "%s" trait to "%s"',
                    PermissionTrait::class,
                    $backedEnum::class,
                ));
            }

            /** @var string $methodName */
            $methodName = $backedEnum->getMethodName();
            if (! method_exists(static::class, $methodName)) {
                throw new \LogicException(\sprintf(
                    'Please implement the "%s" method in "%s"',
                    $methodName,
                    static::class,
                ));
            }
        }

        /** @phpstan-ignore-next-line method.nonObject (Already checked in foreach on top) */
        $methodName = $this->getPermissionsEnum()::from($attribute)->getMethodName();

        /** @var bool */
        return $this->{$methodName}($attribute, $subject, $token);
    }

    /**
     * @return list<\BackedEnum>
     */
    public function getPermissionsCases(): array
    {
        $class = $this->getPermissionsEnum();

        /** @var list<\BackedEnum> */
        return $class::cases();
    }
}
