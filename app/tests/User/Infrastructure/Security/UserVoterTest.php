<?php

declare(strict_types=1);

namespace App\Tests\User\Infrastructure\Security;

use App\Tests\AbstractVoterTestCase;
use App\Tests\Factory\UserFactory;
use App\User\Domain\Model\User;
use App\User\Domain\Security\UserPermissionEnum;
use App\User\Infrastructure\Doctrine\UserRepository;
use App\User\Infrastructure\Security\UserVoter;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @extends AbstractVoterTestCase<value-of<UserPermissionEnum>, User>
 *
 * @internal
 */
final class UserVoterTest extends AbstractVoterTestCase
{
    #[\Override]
    public function providePermissions(): iterable
    {
        foreach ($this->getPermissionsCases() as $backedEnum) {
            yield (string) $backedEnum->value => [
                'attribute' => (string) $backedEnum->value,
                'subject' => new User(),
                'expectedSupports' => true,
            ];
        }

        yield 'custom-get-one' => [
            'attribute' => UserPermissionEnum::GET_ONE->value,
            'subject' => new UserRepository($this->createMock(ManagerRegistry::class)),
            'expectedSupports' => true,
        ];
    }

    #[\Override]
    public function getPermissionsCases(): array
    {
        return UserPermissionEnum::cases();
    }

    #[\Override]
    public function provideVoteOnAttributesCases(): iterable
    {
        yield 'user-get-one' => [
            'roles' => ['ROLE_USER', UserPermissionEnum::GET_ONE->value],
            'attributes' => [UserPermissionEnum::GET_ONE->value],
            'subject' => null,
            'expectedVote' => VoterInterface::ACCESS_GRANTED,
        ];
        yield 'user-get-one-deny' => [
            'roles' => ['ROLE_USER'],
            'attributes' => [UserPermissionEnum::GET_ONE->value],
            'subject' => null,
            'expectedVote' => VoterInterface::ACCESS_DENIED,
        ];
        yield 'admin-get-one' => [
            'roles' => ['ROLE_ADMIN'],
            'attributes' => [UserPermissionEnum::GET_ONE->value],
            'subject' => null,
            'expectedVote' => VoterInterface::ACCESS_GRANTED,
        ];
        yield 'user-get-collection' => [
            'roles' => ['ROLE_USER', UserPermissionEnum::GET_COLLECTION],
            'attributes' => [UserPermissionEnum::GET_COLLECTION->value],
            'subject' => null,
            'expectedVote' => VoterInterface::ACCESS_GRANTED,
        ];
        yield 'user-get-collection-deny' => [
            'roles' => ['ROLE_USER'],
            'attributes' => [UserPermissionEnum::GET_COLLECTION->value],
            'subject' => null,
            'expectedVote' => VoterInterface::ACCESS_DENIED,
        ];
        yield 'admin-get-collection' => [
            'roles' => ['ROLE_ADMIN'],
            'attributes' => [UserPermissionEnum::GET_COLLECTION->value],
            'subject' => null,
            'expectedVote' => VoterInterface::ACCESS_GRANTED,
        ];
        yield 'user-create' => [
            'roles' => ['ROLE_USER', UserPermissionEnum::CREATE],
            'attributes' => [UserPermissionEnum::CREATE->value],
            'subject' => null,
            'expectedVote' => VoterInterface::ACCESS_GRANTED,
        ];
        yield 'user-create-deny' => [
            'roles' => ['ROLE_USER'],
            'attributes' => [UserPermissionEnum::CREATE->value],
            'subject' => null,
            'expectedVote' => VoterInterface::ACCESS_DENIED,
        ];
        yield 'admin-create' => [
            'roles' => ['ROLE_ADMIN'],
            'attributes' => [UserPermissionEnum::CREATE->value],
            'subject' => null,
            'expectedVote' => VoterInterface::ACCESS_GRANTED,
        ];
        yield 'user-update' => [ // Case when user is not admin and attempt to update another user
            'roles' => ['ROLE_USER', UserPermissionEnum::UPDATE],
            'attributes' => [UserPermissionEnum::UPDATE->value],
            'subject' => null,
            'expectedVote' => VoterInterface::ACCESS_DENIED,
        ];
        yield 'user-update-deny' => [
            'roles' => ['ROLE_USER'],
            'attributes' => [UserPermissionEnum::UPDATE->value],
            'subject' => null,
            'expectedVote' => VoterInterface::ACCESS_DENIED,
        ];
        yield 'admin-update' => [
            'roles' => ['ROLE_ADMIN'],
            'attributes' => [UserPermissionEnum::UPDATE->value],
            'subject' => null,
            'expectedVote' => VoterInterface::ACCESS_GRANTED,
        ];
        yield 'user-delete' => [ // Case when user is not admin and attempt to delete another user
            'roles' => ['ROLE_USER', UserPermissionEnum::DELETE],
            'attributes' => [UserPermissionEnum::DELETE->value],
            'subject' => null,
            'expectedVote' => VoterInterface::ACCESS_DENIED,
        ];
        yield 'user-delete-deny' => [
            'roles' => ['ROLE_USER'],
            'attributes' => [UserPermissionEnum::GET_ONE->value],
            'subject' => null,
            'expectedVote' => VoterInterface::ACCESS_DENIED,
        ];
        yield 'admin-delete' => [
            'roles' => ['ROLE_ADMIN'],
            'attributes' => [UserPermissionEnum::DELETE->value],
            'subject' => null,
            'expectedVote' => VoterInterface::ACCESS_GRANTED,
        ];
    }

    public function testVoteOnAttributesUpdateWithSelfUser(): void
    {
        // Arrange
        $user = UserFactory::new()->createOne([
            'roles' => ['ROLE_USER', UserPermissionEnum::UPDATE],
        ])->_real();
        $this->loginUser($user);

        // Act
        $vote = $this->voteOnAttributes(attributes: [UserPermissionEnum::UPDATE->value], subject: $user);

        // Assert
        $this->assertVote(actualVote: $vote, expectedVote: VoterInterface::ACCESS_GRANTED);
    }

    public function testVoteOnAttributesUpdateWithSelfUserButNoPermission(): void
    {
        // Arrange
        $user = UserFactory::new()->createOne([
            'roles' => ['ROLE_USER'],
        ])->_real();
        $this->loginUser($user);

        // Act
        $vote = $this->voteOnAttributes(attributes: [UserPermissionEnum::UPDATE->value], subject: $user);

        // Assert
        $this->assertVote(actualVote: $vote, expectedVote: VoterInterface::ACCESS_DENIED);
    }

    public function testVoteOnAttributesUpdateWithOtherUser(): void
    {
        // Arrange
        $user = UserFactory::new()->createOne([
            'roles' => ['ROLE_USER'],
        ])->_real();
        $otherUser = UserFactory::new()->createOne([
            'roles' => ['ROLE_USER'],
        ])->_real();
        $this->loginUser($user);

        // Act
        $vote = $this->voteOnAttributes(attributes: [UserPermissionEnum::UPDATE->value], subject: $otherUser);

        // Assert
        $this->assertVote(actualVote: $vote, expectedVote: VoterInterface::ACCESS_DENIED);
    }


    #[\Override]
    public function getVoterFqcn(): string
    {
        return UserVoter::class;
    }

    #[\Override]
    public function getDefaultSubject(): object
    {
        return UserFactory::new()->create();
    }
}
