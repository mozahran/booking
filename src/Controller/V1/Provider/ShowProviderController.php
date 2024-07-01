<?php

declare(strict_types=1);

namespace App\Controller\V1\Provider;

use App\Contract\Resolver\ProviderResolverInterface;
use App\Contract\Resolver\WorkspaceResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ShowProviderController extends AbstractController
{
    public function __construct(
        private readonly ProviderResolverInterface $providerResolver,
        private readonly WorkspaceResolverInterface $workspaceResolver,
    ) {
    }

    #[Route(path: '/v1/provider/{providerId}', name: 'app_provider_show', methods: ['GET'])]
    public function __invoke(
        int $providerId,
    ): JsonResponse {
        $provider = $this->providerResolver->resolve(
            id: $providerId,
        );

        $workspaces = $this->workspaceResolver->resolveByProvider(providerId: $providerId);

        return $this->json(
            data: [
                'provider' => $provider->normalize(),
                'workspaces' => $workspaces->normalize(),
            ],
        );
    }
}
