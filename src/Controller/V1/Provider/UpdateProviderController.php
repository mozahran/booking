<?php

declare(strict_types=1);

namespace App\Controller\V1\Provider;

use App\Contract\Persistor\ProviderPersistorInterface;
use App\Contract\Resolver\ProviderResolverInterface;
use App\Domain\DataObject\Provider;
use App\Domain\Enum\UserRole;
use App\Request\ProviderRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UpdateProviderController extends AbstractController
{
    public function __construct(
        private readonly ProviderResolverInterface $providerResolver,
        private readonly ProviderPersistorInterface $providerPersistor,
    ) {
    }

    #[Route(path: '/v1/provider/{providerId}', name: 'app_provider_update', methods: ['PUT'])]
    #[IsGranted(attribute: UserRole::ADMIN->value, message: 'Access Denied!')]
    public function __invoke(
        int $providerId,
        ProviderRequest $request,
    ): JsonResponse {
        $provider = $this->providerResolver->resolve(
            id: $providerId,
        );
        $provider = new Provider(
            name: $request->getName(),
            active: $provider->isActive(),
            userId: $provider->getUserId(),
            id: $provider->getId(),
        );
        $provider = $this->providerPersistor->persist(
            provider: $provider,
        );

        return $this->json(
            data: $provider->normalize(),
        );
    }
}
