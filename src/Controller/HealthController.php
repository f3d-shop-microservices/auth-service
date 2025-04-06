<?php

namespace App\Controller;

use Shop\Common\Health\HealthStatusProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Attribute\Route;

final class HealthController extends AbstractController
{

    public function __construct(
        private RequestStack $requestStack,
        private HealthStatusProvider $healthStatusProvider
    ) {}
    #[Route('/health', name: 'health_check')]
    public function index(): JsonResponse
    {
        $request = $this->requestStack->getCurrentRequest();
        $host = $request ? $request->getHost() : 'unknown';

        $status = $this->healthStatusProvider->getStatus($_ENV['SERVICE_ID'] ?? 'unknown', $host);

        return new JsonResponse($status);
    }

}
