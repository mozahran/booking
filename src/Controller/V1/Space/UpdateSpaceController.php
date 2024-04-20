<?php

declare(strict_types=1);

namespace App\Controller\V1\Space;

use App\Contract\Persistor\SpacePersistorInterface;
use App\Contract\Resolver\SpaceResolverInterface;
use App\Domain\DataObject\Space;
use App\Request\SpaceRequest;
use App\Security\SpaceVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UpdateSpaceController extends AbstractController
{
    public function __construct(
        private readonly SpaceResolverInterface $spaceResolver,
        private readonly SpacePersistorInterface $spacePersistor,
    ) {
    }

    #[Route(path: '/v1/space/{spaceId}', name: 'app_space_update', methods: ['PUT'])]
    #[IsGranted(attribute: SpaceVoter::MANAGE, subject: 'request', message: 'Access Denied!')]
    public function __invoke(
        int $spaceId,
        SpaceRequest $request,
    ): JsonResponse {
        $space = $this->spaceResolver->resolve(
            id: $spaceId,
        );
        $space = new Space(
            name: $request->getName(),
            active: $space->isActive(),
            workspaceId: $space->getWorkspaceId(),
            id: $space->getId(),
        );
        $space = $this->spacePersistor->persist(
            space: $space,
        );

        return $this->json(
            data: [
                'data' => $space->normalize(),
            ],
        );
    }
}
