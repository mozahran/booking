<?php

declare(strict_types=1);

namespace App\Controller\V1\Workspace;

use App\Contract\Persistor\WorkspacePersistorInterface;
use App\Domain\DataObject\Workspace;
use App\Request\WorkspaceRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CreateWorkspaceController extends AbstractController
{
    public function __construct(
        private readonly WorkspacePersistorInterface $workspacePersistor,
    ) {
    }

    #[Route(path: '/v1/workspace', name: 'app_workspace_create', methods: ['POST'])]
    #[IsGranted('MANAGE_PROVIDER', subject: 'request', message: 'Access Denied!')]
    public function __invoke(
        WorkspaceRequest $request,
    ): JsonResponse {
        $workspace = new Workspace(
            name: $request->getName(),
            active: true,
            providerId: $request->getProviderId(),
        );

        $workspace = $this->workspacePersistor->persist(workspace: $workspace);

        return $this->json([
            'data' => $workspace->normalize(),
        ]);
    }
}
