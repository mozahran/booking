<?php

declare(strict_types=1);

namespace App\Controller\V1\Space;

use App\Contract\Resolver\SpaceResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ShowSpaceController extends AbstractController
{
    public function __construct(
        private readonly SpaceResolverInterface $spaceResolver,
    ) {
    }

    #[Route(path: '/v1/space/{spaceId}', name: 'app_space_show', methods: ['GET'])]
    public function __invoke(
        int $spaceId,
    ): JsonResponse {
        $space = $this->spaceResolver->resolve(
            id: $spaceId,
        );

        return $this->json(
            data: [
                'data' => $space->normalize(),
            ],
        );
    }
}
