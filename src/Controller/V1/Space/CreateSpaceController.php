<?php

declare(strict_types=1);

namespace App\Controller\V1\Space;

use App\Contract\Persistor\SpacePersistorInterface;
use App\Domain\DataObject\Space;
use App\Request\SpaceRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CreateSpaceController extends AbstractController
{
    public function __construct(
        private readonly SpacePersistorInterface $workspacePersistor,
    ) {
    }

    #[Route(path: '/v1/space', name: 'app_space_create', methods: ['POST'])]
    #[IsGranted('MANAGE_WORKSPACE', subject: 'request', message: 'Access Denied!')]
    public function __invoke(
        SpaceRequest $request,
    ): JsonResponse {
        $space = new Space(
            name: $request->getName(),
            active: true,
            workspaceId: $request->getWorkspaceId(),
        );

        $space = $this->workspacePersistor->persist(space: $space);

        return $this->json([
            'data' => $space->normalize(),
        ]);
    }
}
