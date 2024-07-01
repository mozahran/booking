<?php

declare(strict_types=1);

namespace App\Controller\V1\Auth;

use App\Contract\Resolver\UserResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ResolveByEmailController extends AbstractController
{
    public function __construct(
        private readonly UserResolverInterface $userResolver,
    ) {
    }

    #[Route(path: '/v1/auth/resolve', name: 'app_auth_resolve_by_email', methods: ['GET'])]
    public function __invoke(
        Request $request,
    ): JsonResponse {
        $user = $this->userResolver->resolveByEmail(
            email: $request->get('email'),
        );

        return $this->json(
            data: [
                'data' => $user->normalize(),
            ],
        );
    }
}
