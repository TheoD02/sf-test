<?php

namespace App\Battery\Infrastructure\ApiPlatform\State\Processor;

use ApiPlatform\Metadata\Operation;
use App\Battery\Domain\Model\Battery;
use App\Battery\Infrastructure\ApiPlatform\Payload\CreateBatteryInput;
use App\Battery\Infrastructure\ApiPlatform\Resource\BatteryResource;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use Rekalogika\ApiLite\State\AbstractProcessor;

/**
 * @extends AbstractProcessor<CreateBatteryInput, BatteryResource>
 */
class BatteryPostProcessor extends AbstractProcessor
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    )
    {
    }

    #[Override]
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $battery = $this->map($data, Battery::class);

        $this->em->persist($battery);
        $this->em->flush();

        return $this->map($battery, BatteryResource::class);
    }
}
