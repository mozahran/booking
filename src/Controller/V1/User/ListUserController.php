<?php

declare(strict_types=1);

namespace App\Controller\V1\User;

use App\Contract\Resolver\UserResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ListUserController extends AbstractController
{
    public function __construct(
        private readonly UserResolverInterface $userResolver,
    ) {
    }

    #[Route(path: '/v1/user', name: 'app_user_list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN', message: 'Access Denied!')]
    public function __invoke(): JsonResponse
    {
        $userSet = $this->userResolver->resolveMany();

        return $this->json([
            'data' => $userSet->normalize(),
        ]);
    }
}
