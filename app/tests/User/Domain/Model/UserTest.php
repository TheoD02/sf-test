<?php

namespace App\Tests\User\Domain\Model;

use App\Tests\Helper\GetterSetterTestHelperTrait;
use App\User\Domain\Model\User;
use PHPUnit\Framework\TestCase;
use Zenstruck\Foundry\Test\Factories;

class UserTest extends TestCase
{
    use Factories;
    use GetterSetterTestHelperTrait;

    public function testGetterSetter(): void
    {
        // Arrange
        $this->setupObject(new User());

        // Act
        $this->populateObject();

        // Assert
        $this->assertObject();
    }
}
