<?php

declare(strict_types=1);

namespace App\Shared\Listener;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * This listener is used to fix the issue with the Delete operation processor. (see https://github.com/api-platform/api-platform/issues/2539).
 */
#[AsEventListener(event: RequestEvent::class)]
final class DeleteOperationProcessorFix
{
    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();

        /** @var Operation|null $operation */
        $operation = $request->attributes->get('_api_operation');
        $processor = $operation?->getProcessor();

        if ($operation instanceof Delete && $processor !== null) {
            $request->attributes->set('data', null);
        }
    }
}
