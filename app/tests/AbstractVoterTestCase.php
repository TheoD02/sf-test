<?php

namespace App\Tests;

use App\Tests\Factory\UserFactory;
use App\Tests\Trait\KernelTestCaseUserAuthenticatorTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Zenstruck\Foundry\Persistence\Proxy;
use Zenstruck\Foundry\Test\Factories;

abstract class AbstractVoterTestCase extends KernelTestCase
{
    use Factories;
    use KernelTestCaseUserAuthenticatorTrait;

    private static array $currentContext = [];

    /**
     * @return array<\BackedEnum>
     */
    abstract public function getPermissionsCases(): array;

    /**
     * @dataProvider providePermissions
     */
    public function testVoterSupportsAttribute(string $attribute, mixed $subject = null, bool $expectedSupports = true): void
    {
        // Act
        $permission = ($this->getVoterInstance())->supportsAttribute($attribute);

        // Assert
        self::assertSame($expectedSupports, $permission);
    }

    public function getVoterInstance(): Voter
    {
        $class = $this->getVoterFqcn();

        return new $class();
    }

    /**
     * @return class-string<Voter>
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
        $permission = ($this->getVoterInstance())->supportsType(is_object($subject) ? get_class($subject) : get_debug_type($subject));

        // Assert
        self::assertTrue($permission);
    }

    abstract public function getDefaultSubject(): object;

    /**
     * @dataProvider provideVoteOnAttributeData
     */
    public function testVoteOnAttributes(array $roles, array $attributes, mixed $subject = null, int $expectedVote = VoterInterface::ACCESS_DENIED): void
    {
        // Act
        $vote = $this->voteOnAttributes($roles, $attributes, $subject);

        // Assert
        $this->assertVote($vote, $expectedVote);
    }

    public function voteOnAttributes(array $roles = [], array $attributes = [], mixed $subject = null): int
    {
        // Arrange
        $subject ??= $this->getDefaultSubject();

        if ($subject instanceof Proxy) {
            $subject = $subject->_real();
        }

        if (! $this->isLoggedIn()) {
            $user = UserFactory::new()->createOne(['roles' => $roles])->_real();
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
        return ($voterInstance)->vote($this->getAuthenticatedToken(), $subject, $attributes);
    }

    public function assertVote(int $actualVote, int $expectedVote): void
    {
        $friendlyName = static function (int $vote): string {
            return match ($vote) {
                VoterInterface::ACCESS_GRANTED => 'granted',
                VoterInterface::ACCESS_DENIED => 'denied',
                VoterInterface::ACCESS_ABSTAIN => 'abstain',
                default => 'unknown',
            };
        };

        // Assert
        self::assertSame($expectedVote, $actualVote, sprintf(
            'Expected vote "%s" but got "%s" for "%s" with attributes "%s" and subject "%s". Roles: "%s"',
            $friendlyName($expectedVote),
            $friendlyName($actualVote),
            $this->getVoterInstance()::class,
            implode('", "', self::$currentContext['attributes']),
            is_object(self::$currentContext['subject']) ? self::$currentContext['subject']::class : get_debug_type(self::$currentContext['subject']),
            implode('", "', $this->getAuthenticatedUser()->getRoles()),
        ));
    }

    abstract public function provideVoteOnAttributeData(): iterable;

    abstract public function providePermissions(): iterable;
}
