<?php

namespace App\Controller;

use Shop\Common\Contract\HealthProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class HealthController extends AbstractController
{

    public function __construct(
        private HealthProviderInterface $healthStatusProvider
    ) {}

    #[Route('/health', name: 'health_check')]
    public function index(): JsonResponse
    {
        return new JsonResponse($this->healthStatusProvider->getStatus());
    }


}
