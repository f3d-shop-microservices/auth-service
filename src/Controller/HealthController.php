<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HealthController extends AbstractController
{
    public function __construct(private RequestStack $requestStack) {}

    #[Route('/health', name: 'health_check')]
    public function index(): JsonResponse
    {
        $request = $this->requestStack->getCurrentRequest();
        $host = $request ? $request->getHost() : 'unknown';

        return new JsonResponse([
            'status' => 'ok',
            'service_id'=> 'auth',
            'container_id' => $this->getDockerContainerId(),
            'host' => $host,
            'time' => (new \DateTime())->format(\DateTime::ATOM),
        ]);
    }

    private function getDockerContainerId(): string
    {
        // Внутри Docker контейнера можно получить короткий ID из /proc/self/cgroup
        $cgroupFile = '/proc/self/cgroup';

        if (!file_exists($cgroupFile)) {
            return 'n/a';
        }

        $content = file_get_contents($cgroupFile);
        if (preg_match('/docker[-\/](?<id>[0-9a-f]{12,64})/', $content, $matches)) {
            return substr($matches['id'], 0, 12);
        }

        return 'unknown';
    }
}
