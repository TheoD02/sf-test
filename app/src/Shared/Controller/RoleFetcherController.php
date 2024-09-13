<?php

namespace App\Shared\Controller;

use App\Shared\Security\GroupPermissions;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[When('dev')]
#[Route('/role-fetcher', name: 'api_role_fetcher', methods: ['GET'])]
class RoleFetcherController extends AbstractController
{
    public function __invoke(): Response
    {
        $roles = [
            'ROLE_USER',
            'ROLE_ADMIN',
            'ROLE_SUPER_ADMIN',
        ];

        foreach (GroupPermissions::cases() as $case) {
            foreach ($case->getPermissions() as $permission) {
                $roles[] = $permission;
            }
        }

        return $this->json($roles);
    }
}
