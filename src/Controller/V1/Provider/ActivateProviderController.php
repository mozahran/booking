<?php

declare(strict_types=1);

namespace App\Controller\V1\Provider;

use App\Contract\Resolver\ProviderResolverInterface;
use App\Contract\Service\PhoenixInterface;
use App\Domain\Enum\UserRole;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ActivateProviderController extends AbstractController
{
    public function __construct(
        private readonly ProviderResolverInterface $providerResolver,
        private readonly PhoenixInterface $phoenix,
    ) {
    }

    #[Route(path: '/v1/provider/{providerId}/activate', name: 'app_provider_activate', methods: ['PUT'])]
    // #[IsGranted(attribute: UserRole::ADMIN->value, message: 'Access Denied!')]
    public function __invoke(
        int $providerId,
    ): JsonResponse {
        $provider = $this->providerResolver->resolve(
            id: $providerId,
        );
        $this->phoenix->activateProvider(
            provider: $provider,
        );

        return $this->json(
            data: [],
        );
    }
}
