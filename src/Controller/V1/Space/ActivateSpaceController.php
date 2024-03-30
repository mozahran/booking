<?php

declare(strict_types=1);

namespace App\Controller\V1\Space;

use App\Contract\Resolver\SpaceResolverInterface;
use App\Contract\Service\PhoenixInterface;
use App\Request\SpaceRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ActivateSpaceController extends AbstractController
{
    public function __construct(
        private readonly SpaceResolverInterface $spaceResolver,
        private readonly PhoenixInterface $phoenix,
    ) {
    }

    #[Route(path: '/v1/space/{spaceId}/activate', name: 'app_space_activate', methods: ['PUT'])]
    #[IsGranted('MANAGE_SPACE', subject: 'request', message: 'Access Denied!')]
    public function __invoke(
        int $spaceId,
        SpaceRequest $request,
    ): JsonResponse {
        $space = $this->spaceResolver->resolve(id: $spaceId);
        $this->phoenix->activateSpace(space: $space);

        return $this->json([]);
    }
}
