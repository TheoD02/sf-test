<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Tests\Factory\UserFactory;
use App\User\Domain\PermissionEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    #[\Override]
    public function load(ObjectManager $manager): void
    {
        UserFactory::new()->create([
            'email' => 'admin@domain.tld',
            'password' => '$2y$13$2tqYsgWC3r/xtFMipQCvt.m1aJ4uvfjk4ng8dYW50SlGdiLCWgtT2', // admin
            'roles' => ['ROLE_USER', ...PermissionEnum::cases()],
        ]);

        UserFactory::new()->createMany(100);

        $manager->flush();
    }
}
