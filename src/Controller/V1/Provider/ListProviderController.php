<?php

declare(strict_types=1);

namespace App\Controller\V1\Provider;

use App\Contract\Resolver\ProviderResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ListProviderController extends AbstractController
{
    public function __construct(
        private readonly ProviderResolverInterface $providerResolver,
    ) {
    }

    #[Route(path: '/v1/provider', name: 'app_provider_list', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $providerSet = $this->providerResolver->resolveAll();

        return $this->json([
            'data' => $providerSet->normalize(),
        ]);
    }
}
