<?php

declare(strict_types=1);

namespace App\Controller\V1\User;

use App\Contract\Persistor\UserPersistorInterface;
use App\Domain\DataObject\User;
use App\Domain\Enum\UserRole;
use App\Request\UserRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CreateUserController extends AbstractController
{
    public function __construct(
        private readonly UserPersistorInterface $userPersistor,
    ) {
    }

    #[Route(path: '/v1/user', name: 'app_user_create', methods: ['POST'])]
    #[IsGranted(attribute: UserRole::ADMIN->value, message: 'Access Denied!')]
    public function __invoke(
        UserRequest $request,
    ): JsonResponse {
        $user = new User(
            name: $request->getName(),
            email: $request->getEmail(),
            active: true,
            password: $request->getPassword(),
        );

        $user = $this->userPersistor->persist(
            user: $user,
        );

        return $this->json(
            data: [
                'data' => $user->normalize(),
            ],
            status: Response::HTTP_CREATED,
        );
    }
}
