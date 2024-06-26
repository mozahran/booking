<?php

declare(strict_types=1);

namespace App\Controller\V1\User;

use App\Contract\Resolver\UserResolverInterface;
use App\Contract\Service\PhoenixInterface;
use App\Domain\Enum\UserRole;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ActivateUserController extends AbstractController
{
    public function __construct(
        private readonly UserResolverInterface $userResolver,
        private readonly PhoenixInterface $phoenix,
    ) {
    }

    #[Route(path: '/v1/user/{userId}/activate', name: 'app_user_activate', methods: ['PUT'])]
    #[IsGranted(attribute: UserRole::ADMIN->value, message: 'Access Denied!')]
    public function __invoke(
        int $userId,
    ): JsonResponse {
        $user = $this->userResolver->resolve(
            id: $userId,
        );
        $this->phoenix->activateUser(
            user: $user,
        );

        return $this->json(
            data: [],
        );
    }
}
