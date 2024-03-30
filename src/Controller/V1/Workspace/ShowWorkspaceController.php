<?php

declare(strict_types=1);

namespace App\Controller\V1\Workspace;

use App\Contract\Resolver\WorkspaceResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ShowWorkspaceController extends AbstractController
{
    public function __construct(
        private readonly WorkspaceResolverInterface $workspaceResolver,
    ) {
    }

    #[Route(path: '/v1/workspace/{workspaceId}', name: 'app_workspace_show', methods: ['GET'])]
    public function __invoke(
        int $workspaceId,
    ): JsonResponse {
        $workspace = $this->workspaceResolver->resolve(id: $workspaceId);

        return $this->json([
            'data' => $workspace->normalize(),
        ]);
    }
}
