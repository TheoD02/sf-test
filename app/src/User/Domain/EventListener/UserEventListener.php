<?php

namespace App\User\Domain\EventListener;

use App\Shared\Trait\SecurityTrait;
use App\User\Domain\Event\UserTransitToAdminEvent;
use Rekalogika\Contracts\DomainEvent\Attribute\AsImmediateDomainEventListener;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserEventListener
{
    use SecurityTrait;

    #[AsImmediateDomainEventListener]
    public function immediate(UserTransitToAdminEvent $event): void
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        throw new HttpException(403, 'Only admin can add admin role to user');
    }
}
