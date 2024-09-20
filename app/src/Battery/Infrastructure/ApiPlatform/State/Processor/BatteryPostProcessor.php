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

        $dataList = $battery->getData();
        foreach ($dataList as $key => $row) {
            if ($row['type'] === 'cellular') {
                $isDoubleSim = str_contains($row['level'], "\n");
                if ($isDoubleSim) {
                    [$levelOperator1, $levelOperator2] = explode("\n", $row['level']);
                    [$operator1, $operator2] = explode("\n", $row['operator']);
                    [$radio1, $radio2] = explode("\n", $row['radio']);
                    unset($dataList[$key]);
                    $dataList[] = [
                        'type' => 'cellular',
                        'operator' => $operator1,
                        'radio' => $radio1,
                        'level' => (int)$levelOperator1,
                    ];
                    $dataList[] = [
                        'type' => 'cellular',
                        'operator' => $operator2,
                        'radio' => $radio2,
                        'level' => (int) $levelOperator2,
                    ];
                }
            }
        }
        $battery->setData($dataList);

        $this->em->persist($battery);
        $this->em->flush();

        return $this->map($battery, BatteryResource::class);
    }
}
