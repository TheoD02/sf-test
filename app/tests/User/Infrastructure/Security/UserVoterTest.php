<?php

namespace App\Tests\User\Infrastructure\Security;

use App\Tests\AbstractVoterTestCase;
use App\Tests\Factory\UserFactory;
use App\User\Domain\Model\User;
use App\User\Domain\Security\UserPermissionEnum;
use App\User\Infrastructure\Doctrine\UserRepository;
use App\User\Infrastructure\Security\UserVoter;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class UserVoterTest extends AbstractVoterTestCase
{
    public function providePermissions(): iterable
    {
        foreach ($this->getVoterInstance()->getPermissionsCases() as $permission) {
            yield $permission->value => [$permission->value, new User(), true];
        }

        yield 'custom-get-one' => [UserPermissionEnum::GET_ONE->value, new UserRepository($this->createMock(ManagerRegistry::class)), true];

        yield 'not-exists' => ['not-exists', null, false];
    }

    public function getPermissionsCases(): array
    {
        return UserPermissionEnum::cases();
    }

    public function provideVoteOnAttributeData(): \Generator
    {
        yield 'user-get-one' => [['ROLE_USER'], [UserPermissionEnum::GET_ONE->value], null, VoterInterface::ACCESS_GRANTED];
        yield 'admin-get-one' => [['ROLE_ADMIN'], [UserPermissionEnum::GET_ONE->value], null, VoterInterface::ACCESS_GRANTED];
        yield 'user-get-collection' => [['ROLE_USER'], [UserPermissionEnum::GET_COLLECTION->value], null, VoterInterface::ACCESS_GRANTED];
        yield 'admin-get-collection' => [['ROLE_ADMIN'], [UserPermissionEnum::GET_COLLECTION->value], null, VoterInterface::ACCESS_GRANTED];
        yield 'user-create' => [['ROLE_USER'], [UserPermissionEnum::CREATE->value], null, VoterInterface::ACCESS_DENIED];
        yield 'admin-create' => [['ROLE_ADMIN'], [UserPermissionEnum::CREATE->value], null, VoterInterface::ACCESS_GRANTED];
        yield 'user-update' => [['ROLE_USER'], [UserPermissionEnum::UPDATE->value], null, VoterInterface::ACCESS_DENIED];
        yield 'admin-update' => [['ROLE_ADMIN'], [UserPermissionEnum::UPDATE->value], null, VoterInterface::ACCESS_GRANTED];
        yield 'user-delete' => [['ROLE_USER'], [UserPermissionEnum::DELETE->value], null, VoterInterface::ACCESS_DENIED];
        yield 'admin-delete' => [['ROLE_ADMIN'], [UserPermissionEnum::DELETE->value], null, VoterInterface::ACCESS_GRANTED];
    }

    public function testVoteOnAttributesUpdateWithSelfUser(): void
    {
        // Arrange
        $user = UserFactory::new()->createOne(['roles' => ['ROLE_USER']])->_real();
        $this->loginUser($user);

        // Act
        $vote = $this->voteOnAttributes(
            attributes: [UserPermissionEnum::UPDATE->value],
            subject: $user,
        );

        // Assert
        $this->assertVote(
            actualVote: $vote,
            expectedVote: VoterInterface::ACCESS_GRANTED,
        );
    }

    public function testVoteOnAttributesUpdateWithOtherUser(): void
    {
        // Arrange
        $user = UserFactory::new()->createOne(['roles' => ['ROLE_USER']])->_real();
        $otherUser = UserFactory::new()->createOne(['roles' => ['ROLE_USER']])->_real();
        $this->loginUser($user);

        // Act
        $vote = $this->voteOnAttributes(
            attributes: [UserPermissionEnum::UPDATE->value],
            subject: $otherUser,
        );

        // Assert
        $this->assertVote(
            actualVote: $vote,
            expectedVote: VoterInterface::ACCESS_DENIED,
        );
    }

    public function getVoterFqcn(): string
    {
        return UserVoter::class;
    }

    public function getDefaultSubject(): object
    {
        return UserFactory::new()->create();
    }
}
