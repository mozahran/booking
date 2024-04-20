<?php

declare(strict_types=1);

namespace App\Controller\V1\Workspace;

use App\Contract\Resolver\WorkspaceResolverInterface;
use App\Contract\Service\PhoenixInterface;
use App\Request\WorkspaceRequest;
use App\Security\WorkspaceVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ActivateWorkspaceController extends AbstractController
{
    public function __construct(
        private readonly WorkspaceResolverInterface $workspaceResolver,
        private readonly PhoenixInterface $phoenix,
    ) {
    }

    #[Route(path: '/v1/workspace/{workspaceId}/activate', name: 'app_workspace_activate', methods: ['PUT'])]
    #[IsGranted(attribute: WorkspaceVoter::MANAGE, subject: 'request', message: 'Access Denied!')]
    public function __invoke(
        int $workspaceId,
        WorkspaceRequest $request,
    ): JsonResponse {
        $workspace = $this->workspaceResolver->resolve(
            id: $workspaceId,
        );
        $this->phoenix->activateWorkspace(
            workspace: $workspace,
        );

        return $this->json(
            data: [],
        );
    }
}
