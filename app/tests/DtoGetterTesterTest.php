<?php

namespace App\Tests;

use App\Tests\Helper\GetterSetterTestHelperTrait;
use PHPUnit\Framework\TestCase;
use Random\RandomException;
use Symfony\Component\Finder\Finder;
use function Symfony\Component\String\u;

class DtoGetterTesterTest extends TestCase
{
    use GetterSetterTestHelperTrait;

    /**
     * @dataProvider classProvider
     * @throws \ReflectionException
     * @throws RandomException
     */
    public function testGetterSetter(string $class): void
    {
        // Arrange
        $this->setupObject($class);

        // Act
        $this->populateObjectAndAssert();
    }

    public function classProvider(): iterable
    {
        $files = (new Finder())
            ->files()
            ->in([
                dirname(__DIR__) . '/src/*/Infrastructure/ApiPlatform/Payload',
                dirname(__DIR__) . '/src/*/Infrastructure/ApiPlatform/Resource',
                dirname(__DIR__) . '/src/*/Domain/Model',
            ])
            ->name('*.php');

        foreach ($files as $file) {
            $content = file_get_contents($file->getRealPath());
            preg_match('/namespace (.*);/', $content, $matches);

            if (empty($matches[1])) {
                continue;
            }

            $namespace = $matches[1];

            preg_match('/class (.*)/', $content, $matches);

            if (empty($matches[1])) {
                continue;
            }

            $className = $matches[1];

            $className = u($className)->before(' extends ')->before(' implements ')->toString();

            $fqcn = $namespace . '\\' . $className;

            if (class_exists($fqcn) === false) {
                continue;
            }

            yield $fqcn => [$fqcn];
        }
    }
}
