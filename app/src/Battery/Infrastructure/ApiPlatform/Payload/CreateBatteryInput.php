<?php

namespace App\Battery\Infrastructure\ApiPlatform\Payload;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CreateBatteryInput
{
    #[Assert\PositiveOrZero()]
    public int $level;

    /**
     * @var array <int, array{type: 'cellular', operator: string, radio: string, level: int}|array{type: 'wifi', ssid: string, level: int}>
     */
    #[Assert\Callback(callback: [self::class, 'validateData'])]
    public array $data;

    #[Assert\NotBlank()]
    public string $reason;

    public static function validateData(?array $data, ExecutionContextInterface $context): void
    {
        if ($data === null) {
            $context->buildViolation('[data] must be set')->atPath('data')->addViolation();
            return;
        }

        if (!is_array($data)) {
            $context->buildViolation('[data] must be an array')->atPath('data')->addViolation();
        }

        foreach ($data as $key => $item) {
            if (!is_array($item)) {
                $context->buildViolation(sprintf('[data][%s] must be an array', $key))->atPath('data')->addViolation();
            }

            if (!array_key_exists('type', $item)) {
                $context->buildViolation(sprintf('[data][%s] must contain a type', $key))->atPath('data')->addViolation();
            }

            $type = $item['type'] ?? '';

            if ($type === 'cellular') {
                if (!array_key_exists('operator', $item)) {
                    $context->buildViolation(sprintf('[data][%s][operator] must be set', $key))->atPath('data')->addViolation();
                }
                if (!array_key_exists('radio', $item)) {
                    $context->buildViolation(sprintf('[data][%s][radio] must be set', $key))->atPath('data')->addViolation();
                }
                if (!array_key_exists('level', $item)) {
                    $context->buildViolation(sprintf('[data][%s][level] must be set', $key))->atPath('data')->addViolation();
                }
            } else if ($type === 'wifi') {
                if (!array_key_exists('ssid', $item)) {
                    $context->buildViolation(sprintf('[data][%s][ssid] must be set', $key))->atPath('data')->addViolation();
                }
                if (!array_key_exists('level', $item)) {
                    $context->buildViolation(sprintf('[data][%s][level] must be set', $key))->atPath('data')->addViolation();
                }
            } else {
                $context->buildViolation('data.type must be cellular or wifi')->atPath('data')->addViolation();
            }
        }
    }
}
