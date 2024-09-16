<?php

declare(strict_types=1);

namespace App\User\Domain\EventListener;

use App\Shared\Trait\SecurityTrait;
use App\User\Domain\Event\UserTransitToAdminEvent;
use Rekalogika\Contracts\DomainEvent\Attribute\AsImmediateDomainEventListener;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserEventListener
{
    use SecurityTrait;

    public function __construct(
        #[Autowire(env: 'APP_ENV')] private readonly string $env,
    ) {
    }

    #[AsImmediateDomainEventListener]
    public function immediate(UserTransitToAdminEvent $event): void
    {
        if ($this->env === 'test') {
            return; // If we are in test environment, we don't prevent admin to add admin role because otherwise we can't create a new user
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        throw new HttpException(403, 'Only admin can add admin role to user');
    }
}
