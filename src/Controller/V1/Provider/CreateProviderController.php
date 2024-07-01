<?php

declare(strict_types=1);

namespace App\Controller\V1\Provider;

use App\Contract\Persistor\ProviderPersistorInterface;
use App\Domain\DataObject\Provider;
use App\Domain\Enum\UserRole;
use App\Request\ProviderRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CreateProviderController extends AbstractController
{
    public function __construct(
        private readonly ProviderPersistorInterface $providerPersistor,
    ) {
    }

    #[Route(path: '/v1/provider', name: 'app_provider_create', methods: ['POST'])]
    #[IsGranted(attribute: UserRole::ADMIN->value, message: 'Access Denied!')]
    public function __invoke(
        ProviderRequest $request,
    ): JsonResponse {
        $provider = new Provider(
            name: $request->getName(),
            active: true,
            userId: $request->getUserId(),
        );
        $provider = $this->providerPersistor->persist(
            provider: $provider,
        );

        return $this->json(
            data: $provider->normalize(),
            status: Response::HTTP_CREATED,
        );
    }
}
