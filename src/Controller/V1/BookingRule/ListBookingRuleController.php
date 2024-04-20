<?php

declare(strict_types=1);

namespace App\Controller\V1\BookingRule;

use App\Contract\Resolver\BookingRuleResolverInterface;
use App\Request\BookingRuleRequest;
use App\Security\BookingRuleVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ListBookingRuleController extends AbstractController
{
    public function __construct(
        private readonly BookingRuleResolverInterface $bookingRuleResolver,
    ) {
    }

    #[Route(path: '/v1/booking-rule', name: 'app_booking-rule_list', methods: ['GET'])]
    #[IsGranted(attribute: BookingRuleVoter::MANAGE, subject: 'request', message: 'Access Denied!')]
    public function __invoke(
        BookingRuleRequest $request,
    ): JsonResponse {
        $bookingRules = $this->bookingRuleResolver->resolveManyForWorkspace(
            workspaceId: $request->getWorkspaceId(),
        );

        return $this->json(
            data: [
                'data' => $bookingRules->normalize(),
            ],
            status: Response::HTTP_OK,
        );
    }
}
