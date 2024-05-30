<?php

declare(strict_types=1);

namespace App\Controller\V1\User;

use App\Contract\Resolver\ProviderUserDataResolverInterface;
use App\Contract\Resolver\UserResolverInterface;
use App\Domain\DataObject\Set\UserSet;
use App\Domain\Enum\UserRole;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ListUserController extends AbstractController
{
    public function __construct(
        private readonly UserResolverInterface $userResolver,
        private readonly ProviderUserDataResolverInterface $providerUserDataResolver,
    ) {
    }

    #[Route(path: '/v1/user', name: 'app_user_list', methods: ['GET'])]
    #[IsGranted(attribute: UserRole::ADMIN->value, message: 'Access Denied!')]
    public function __invoke(): JsonResponse
    {
        $userSet = $this->userResolver->resolveMany();

        return $this->json(
            data: [
                'data' => $this->translateUserSet(userSet: $userSet),
            ],
        );
    }

    private function translateUserSet(
        UserSet $userSet,
    ): array {
        $providerUserDataSet = $this->providerUserDataResolver->resolveByUsers(
            userIds: $userSet->ids(),
        );
        $providerUserDataIndexedByUserId = $providerUserDataSet->indexByUserId();

        $data = [];
        $users = $userSet->items();
        foreach ($users as $user) {
            $userId = $user->getId();
            $userData['id'] = $user->getId();
            $userData['name'] = $user->getName();
            $userData['email'] = $user->getEmail();
            $userData['system_roles'] = $user->getRoles();
            $userData['provider_data'] = $providerUserDataIndexedByUserId[$userId] ?? [];
            $userData['active'] = $user->isActive();
            $data[] = $userData;
        }

        return $data;
    }
}
