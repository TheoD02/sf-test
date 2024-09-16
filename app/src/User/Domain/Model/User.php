<?php

declare(strict_types=1);

namespace App\User\Domain\Model;

use App\Shared\Trait\EntityUuid;
use App\User\Domain\Event\UserTransitToAdminEvent;
use App\User\Infrastructure\Doctrine\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Rekalogika\Contracts\DomainEvent\DomainEventEmitterInterface;
use Rekalogika\Contracts\DomainEvent\DomainEventEmitterTrait;
use Rekalogika\Mapper\Attribute\AllowDelete;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface, DomainEventEmitterInterface
{
    use DomainEventEmitterTrait;
    use EntityUuid;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[ORM\Column(length: 180)]
    private string $email = '';

    /**
     * @var non-empty-list<string> The user roles
     */
    #[ORM\Column]
    #[AllowDelete]
    private array $roles = ['ROLE_USER'];

    #[ORM\Column]
    private string $password = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    #[\Override]
    public function getUserIdentifier(): string
    {
        if ($this->email === '') {
            throw new \LogicException('User Identifier cannot be empty');
        }

        return $this->email;
    }

    /**
     * @return non-empty-list<string>
     *
     * @see UserInterface
     */
    #[\Override]
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
    }

    /**
     * @param non-empty-list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $currentRoles = $this->roles;
        $this->roles = $roles;

        if (! \in_array('ROLE_ADMIN', $currentRoles, true) && \in_array('ROLE_ADMIN', $this->roles, true)) {
            $this->recordEvent(new UserTransitToAdminEvent($this->uuid));
        }

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    #[\Override]
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    #[\Override]
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
