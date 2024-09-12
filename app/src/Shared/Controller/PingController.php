<?php

declare(strict_types=1);

namespace App\Shared\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/ping', name: 'ping')]
class PingController extends AbstractController
{
    public function __invoke(): JsonResponse
    {
        return $this->json([
            'status' => 'ok',
        ]);
    }
}
