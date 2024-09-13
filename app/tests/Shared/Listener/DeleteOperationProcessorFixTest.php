<?php

declare(strict_types=1);

namespace App\Tests\Shared\Listener;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use App\Shared\Listener\DeleteOperationProcessorFix;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * @internal
 */
final class DeleteOperationProcessorFixTest extends TestCase
{
    public function testDeleteOperationProcessorFix(): void
    {
        // Arrange
        $event = $this->createPartialMock(RequestEvent::class, ['getRequest']);
        $request = new Request(attributes: [
            '_api_operation' => new Delete(),
        ]);

        $event->expects(self::once())->method('getRequest')->willReturn($request);

        // Act
        (new DeleteOperationProcessorFix())->__invoke($event);

        // Assert
        self::assertNull($request->attributes->get('data'));
    }

    public function testDeleteOperationProcessorFixNotInvoked(): void
    {
        // Arrange
        $event = $this->createPartialMock(RequestEvent::class, ['getRequest']);
        $data = new \stdClass();
        $request = new Request(attributes: [
            '_api_operation' => new Post(),
            'data' => $data,
        ]);

        $event->expects(self::once())->method('getRequest')->willReturn($request);

        // Act
        (new DeleteOperationProcessorFix())->__invoke($event);

        // Assert
        self::assertSame($data, $request->attributes->get('data'));
    }
}
