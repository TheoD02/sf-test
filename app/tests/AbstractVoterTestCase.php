<?php

declare(strict_types=1);

namespace App\Tests;

use App\Tests\Factory\UserFactory;
use App\Tests\Trait\KernelTestCaseUserAuthenticatorTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Zenstruck\Foundry\Persistence\Proxy;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * @template TAttribute of string
 * @template TSubject of mixed
 */
abstract class AbstractVoterTestCase extends KernelTestCase
{
    use Factories;
    use KernelTestCaseUserAuthenticatorTrait;
    use ResetDatabase;

    /**
     * @var array{attributes: array<string>, subject: mixed}|array{}
     */
    private static array $currentContext = [];

    /**
     * @return list<\BackedEnum>
     */
    abstract public function getPermissionsCases(): array;

    /**
     * @dataProvider providePermissions
     */
    public function testVoterSupportsAttribute(
        string $attribute,
        mixed $subject = null,
        bool $expectedSupports = true,
    ): void {
        // Act
        $permission = $this->getVoterInstance()->supportsAttribute($attribute);

        // Assert
        self::assertSame($expectedSupports, $permission);
    }

    /**
     * @return Voter<TAttribute, TSubject>
     */
    public function getVoterInstance(): Voter
    {
        $class = $this->getVoterFqcn();

        return new $class();
    }

    /**
     * @return class-string<Voter<TAttribute, TSubject>>
     */
    abstract public function getVoterFqcn(): string;

    /**
     * @dataProvider providePermissions
     */
    public function testVoterSupportsType(string $attribute, mixed $subject = null, bool $expectedSupports = true): void
    {
        // Arrange
        $subject ??= $this->getDefaultSubject();

        // Act
        $permission = $this->getVoterInstance()->supportsType(get_debug_type($subject));

        // Assert
        if ($expectedSupports) {
            self::assertTrue($permission);
        } else {
            self::assertFalse($permission);
        }
    }

    abstract public function getDefaultSubject(): object;

    /**
     * @param array<string>            $roles
     * @param array<string>            $attributes
     * @param VoterInterface::ACCESS_* $expectedVote
     *
     * @dataProvider provideVoteOnAttributesCases
     */
    public function testVoteOnAttributes(
        array $roles,
        array $attributes,
        mixed $subject = null,
        int $expectedVote = VoterInterface::ACCESS_DENIED,
    ): void {
        // Act
        $vote = $this->voteOnAttributes($roles, $attributes, $subject);

        // Assert
        $this->assertVote($vote, $expectedVote);
    }

    /**
     * @param array<string> $roles
     * @param array<string> $attributes
     */
    public function voteOnAttributes(array $roles = [], array $attributes = [], mixed $subject = null): int
    {
        // Arrange
        $subject ??= $this->getDefaultSubject();

        if ($subject instanceof Proxy) {
            $subject = $subject->_real();
        }

        if (! $this->isLoggedIn()) {
            $user = UserFactory::new()->createOne([
                'roles' => $roles,
            ])->_real();
            $this->loginUser($user);
        }

        // Arrange
        $voterInstance = $this->getVoterInstance();

        if (method_exists($voterInstance, 'setSecurity')) {
            $voterInstance->setSecurity($this->getSecurity());
        }

        // Act
        self::$currentContext = [
            'attributes' => $attributes,
            'subject' => $subject,
        ];

        return $voterInstance->vote($this->getAuthenticatedToken(), $subject, $attributes);
    }

    /**
     * @param VoterInterface::ACCESS_* $expectedVote
     */
    public function assertVote(int $actualVote, int $expectedVote): void
    {
        /** @phpstan-ignore-next-line shipmonk.unusedMatchResult (false positive, it used through callable) */
        $friendlyName = static fn (int $vote): string => match ($vote) {
            VoterInterface::ACCESS_GRANTED => 'granted',
            VoterInterface::ACCESS_DENIED => 'denied',
            VoterInterface::ACCESS_ABSTAIN => 'abstain',
            default => 'unknown',
        };

        // Assert
        self::assertSame($expectedVote, $actualVote, \sprintf(
            'Expected vote "%s" but got "%s" for "%s" with attributes "%s" and subject "%s". Roles: "%s"',
            $friendlyName($expectedVote),
            $friendlyName($actualVote),
            $this->getVoterInstance()::class,
            implode('", "', self::$currentContext['attributes'] ?? []),
            get_debug_type(self::$currentContext['subject'] ?? null),
            implode('", "', $this->getAuthenticatedUser()->getRoles()),
        ));
    }

    /**
     * @return iterable<array{roles: array<string>, attributes: array<string>, subject: mixed, expectedVote: VoterInterface::ACCESS_*}>
     */
    abstract public function provideVoteOnAttributesCases(): iterable;

    /**
     * @return iterable<string, array{attribute: string, subject: mixed, expectedSupports: bool}>
     */
    abstract public function providePermissions(): iterable;
}
