<?php

declare(strict_types=1);

namespace App\Shared\Trait;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Service\Attribute\Required;

trait EntityManagerTrait // @phpstan-ignore-line (Don't used for now, remove when used)
{
    private EntityManagerInterface $em;

    #[Required]
    public function setEntityManager(EntityManagerInterface $em): void
    {
        $this->em = $em;
    }
}
