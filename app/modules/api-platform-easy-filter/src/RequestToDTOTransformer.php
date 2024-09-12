<?php

declare(strict_types=1);

namespace Module\ApiPlatformEasyFilter;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestPayloadValueResolver;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RequestToDTOTransformer
{
    public function __construct(
        private readonly HttpKernelInterface         $httpKernel,
        #[Autowire(service: 'argument_resolver.request_payload')]
        private readonly RequestPayloadValueResolver $requestPayloadValueResolver,
    )
    {
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $type
     *
     * @return T
     */
    public function transformPayload(Request $request, string $type): object
    {
        $mapRequestPayload = new MapRequestPayload();
        $mapRequestPayload->metadata = new ArgumentMetadata('RequestPayloadMetadata', $type, false, false, false);
        $event = new ControllerArgumentsEvent(
            $this->httpKernel,
            static fn (): null => null,
            [$mapRequestPayload],
            $request,
            HttpKernelInterface::SUB_REQUEST,
        );
        $this->requestPayloadValueResolver->onKernelControllerArguments($event);

        /** @var T */
        return $event->getArguments()[0];
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $type
     *
     * @return ?T
     */
    public function transformQueryString(Request $request, string $type): ?object
    {
        $mapQueryString = new MapQueryString(resolver: RequestPayloadValueResolver::class);
        $mapQueryString->metadata = new ArgumentMetadata('QueryStringMetadata', $type, false, false, false);
        $event = new ControllerArgumentsEvent(
            $this->httpKernel,
            static fn (): null => null,
            [$mapQueryString],
            $request,
            HttpKernelInterface::SUB_REQUEST,
        );
        try {
            $this->requestPayloadValueResolver->onKernelControllerArguments($event);
        } catch (HttpException $httpException) {
            if ($httpException->getStatusCode() === 404) {
                return null;
            }

            throw $httpException;
        }

        /** @var T */
        return $event->getArguments()[0];
    }
}
