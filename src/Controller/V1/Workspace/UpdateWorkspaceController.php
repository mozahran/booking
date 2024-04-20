<?php

declare(strict_types=1);

namespace App\Controller\V1\Workspace;

use App\Contract\Persistor\WorkspacePersistorInterface;
use App\Contract\Resolver\WorkspaceResolverInterface;
use App\Domain\DataObject\Workspace;
use App\Request\WorkspaceRequest;
use App\Security\WorkspaceVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UpdateWorkspaceController extends AbstractController
{
    public function __construct(
        private readonly WorkspaceResolverInterface $workspaceResolver,
        private readonly WorkspacePersistorInterface $workspacePersistor,
    ) {
    }

    #[Route(path: '/v1/workspace/{workspaceId}', name: 'app_workspace_update', methods: ['PUT'])]
    #[IsGranted(attribute: WorkspaceVoter::MANAGE, subject: 'request', message: 'Access Denied!')]
    public function __invoke(
        int $workspaceId,
        WorkspaceRequest $request,
    ): JsonResponse {
        $workspace = $this->workspaceResolver->resolve(
            id: $workspaceId,
        );
        $workspace = new Workspace(
            name: $request->getName(),
            active: $workspace->isActive(),
            providerId: $workspace->getProviderId(),
            id: $workspace->getId(),
        );
        $workspace = $this->workspacePersistor->persist(
            workspace: $workspace,
        );

        return $this->json(
            data: [
                'data' => $workspace->normalize(),
            ],
        );
    }
}
