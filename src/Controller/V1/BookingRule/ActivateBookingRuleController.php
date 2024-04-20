<?php

declare(strict_types=1);

namespace App\Controller\V1\BookingRule;

use App\Contract\Resolver\BookingRuleResolverInterface;
use App\Contract\Service\PhoenixInterface;
use App\Request\BookingRuleRequest;
use App\Security\BookingRuleVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ActivateBookingRuleController extends AbstractController
{
    public function __construct(
        private readonly BookingRuleResolverInterface $bookingRuleResolver,
        private readonly PhoenixInterface $phoenix,
    ) {
    }

    #[Route(path: '/v1/booking-rule/{bookingRuleId}/activate', name: 'app_booking-rule_activate', methods: ['PUT'])]
    #[IsGranted(attribute: BookingRuleVoter::MANAGE, subject: 'request', message: 'Access Denied!')]
    public function __invoke(
        int $bookingRuleId,
        BookingRuleRequest $request,
    ): JsonResponse {
        $bookingRule = $this->bookingRuleResolver->resolve(
            id: $bookingRuleId,
        );
        $this->phoenix->activateBookingRule(
            bookingRule: $bookingRule,
        );

        return $this->json(
            data: [],
        );
    }
}
