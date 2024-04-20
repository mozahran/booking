<?php

declare(strict_types=1);

namespace App\Controller\V1\User;

use App\Contract\Persistor\UserPersistorInterface;
use App\Contract\Resolver\UserResolverInterface;
use App\Domain\DataObject\User;
use App\Domain\Enum\UserRole;
use App\Request\UserRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UpdateUserController extends AbstractController
{
    public function __construct(
        private readonly UserPersistorInterface $userPersistor,
        private readonly UserResolverInterface $userResolver,
    ) {
    }

    #[Route(path: '/v1/user/{userId}', name: 'app_user_update', methods: ['PUT'])]
    #[IsGranted(attribute: UserRole::ADMIN->value, message: 'Access Denied!')]
    public function __invoke(
        int $userId,
        UserRequest $request,
    ): JsonResponse {
        $user = $this->userResolver->resolve(
            id: $userId,
        );
        $user = new User(
            name: $request->getName(),
            email: $request->getEmail(),
            active: $user->isActive(),
            password: $request->getPassword(),
            id: $userId,
        );
        $user = $this->userPersistor->persist(
            user: $user,
        );

        return $this->json(
            data: [
                'data' => $user->normalize(),
            ],
        );
    }
}
