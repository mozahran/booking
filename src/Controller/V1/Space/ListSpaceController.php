<?php

declare(strict_types=1);

namespace App\Controller\V1\Space;

use App\Contract\Resolver\SpaceResolverInterface;
use App\Contract\Resolver\WorkspaceResolverInterface;
use App\Request\SpaceRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ListSpaceController extends AbstractController
{
    public function __construct(
        private readonly WorkspaceResolverInterface $workspaceResolver,
        private readonly SpaceResolverInterface $spaceResolver,
    ) {
    }

    #[Route(path: '/v1/space', name: 'app_space_list', methods: ['GET'])]
    public function __invoke(
        SpaceRequest $request,
    ): JsonResponse {
        $workspace = $this->workspaceResolver->resolve(
            id: $request->getWorkspaceId(),
        );
        $spaceSet = $this->spaceResolver->resolveByWorkspace(
            workspaceId: $workspace->getId(),
        );

        return $this->json(
            data: [
                'data' => $spaceSet->normalize(),
            ],
        );
    }
}
