<?php

declare(strict_types=1);

namespace App\Controller\V1\Workspace;

use App\Contract\Resolver\ProviderResolverInterface;
use App\Contract\Resolver\WorkspaceResolverInterface;
use App\Request\WorkspaceRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ListWorkspaceController extends AbstractController
{
    public function __construct(
        private readonly ProviderResolverInterface $providerResolver,
        private readonly WorkspaceResolverInterface $workspaceResolver,
    ) {
    }

    #[Route(path: '/v1/workspace', name: 'app_workspace_list', methods: ['GET'])]
    public function __invoke(
        WorkspaceRequest $request,
    ): JsonResponse {
        $provider = $this->providerResolver->resolve(
            id: $request->getProviderId(),
        );
        $workspaceSet = $this->workspaceResolver->resolveByProvider(
            providerId: $provider->getId(),
        );

        return $this->json(
            data: [
                'data' => $workspaceSet->normalize(),
            ],
        );
    }
}
