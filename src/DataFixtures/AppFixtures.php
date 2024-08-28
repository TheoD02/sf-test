<?php

namespace App\DataFixtures;

use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        UserFactory::new()->create([
            'email' => 'admin@example.com',
            'password' => '$2y$10$2b2cU8CPhOTaKRmyFDkZo.1uUpQHN0wJaPeFBMd7bC0bXga18C3O2', // password
            'roles' => ['ROLE_ADMIN'],
        ]);

        $manager->flush();
    }
}
