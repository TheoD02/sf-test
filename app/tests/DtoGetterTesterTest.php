<?php

declare(strict_types=1);

namespace App\Tests;

use App\Tests\Helper\GetterSetterTestHelperTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

use function Symfony\Component\String\u;

/**
 * @internal
 */
final class DtoGetterTesterTest extends TestCase
{
    use GetterSetterTestHelperTrait;

    /**
     * @param class-string $class
     *
     * @dataProvider provideGetterSetterCases
     */
    public function testGetterSetter(string $class): void
    {
        $this->markTestSkipped('Needs to be fixed, not very stable yet');
        // Arrange
        $this->setupObject($class); // @phpstan-ignore-line

        // Act
        $this->populateObjectAndAssert();
    }

    /**
     * @return iterable<class-string, class-string>
     */
    public function provideGetterSetterCases(): iterable
    {
        $files = (new Finder()) // @phpstan-ignore-line (false positive, ignore exception, for test is not really problematic)
            ->files()
            ->in([
                \dirname(__DIR__) . '/src/*/Infrastructure/ApiPlatform/Payload',
                \dirname(__DIR__) . '/src/*/Infrastructure/ApiPlatform/Resource',
                \dirname(__DIR__) . '/src/*/Domain/Model',
            ])
            ->name('*.php')
        ;

        foreach ($files as $file) {
            if ($file->getRealPath() === false) {
                continue;
            }

            $content = file_get_contents($file->getRealPath());
            if ($content === false) {
                continue;
            }

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

            /** @var class-string $fqcn */
            $fqcn = $namespace . '\\' . $className;

            if (class_exists($fqcn) === false) {
                continue;
            }

            yield $fqcn => [$fqcn]; // @phpstan-ignore-line (false positive, that is OK)
        }
    }
}
