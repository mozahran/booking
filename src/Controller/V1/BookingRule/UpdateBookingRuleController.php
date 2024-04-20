<?php

declare(strict_types=1);

namespace App\Controller\V1\BookingRule;

use App\Contract\Persistor\BookingRulePersistorInterface;
use App\Contract\Resolver\BookingRuleResolverInterface;
use App\Contract\Service\BookingRule\RuleRequestValidatorInterface;
use App\Domain\DataObject\BookingRule;
use App\Request\BookingRuleRequest;
use App\Security\BookingRuleVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UpdateBookingRuleController extends AbstractController
{
    public function __construct(
        private readonly RuleRequestValidatorInterface $requestValidator,
        private readonly BookingRuleResolverInterface $bookingRuleResolver,
        private readonly BookingRulePersistorInterface $bookingRulePersistor,
    ) {
    }

    #[Route(path: '/v1/booking-rule/{bookingRuleId}', name: 'app_booking-rule_update', methods: ['PUT'])]
    #[IsGranted(attribute: BookingRuleVoter::MANAGE, subject: 'request', message: 'Access Denied!')]
    public function __invoke(
        BookingRuleRequest $request,
        int $bookingRuleId,
    ): JsonResponse {
        $bookingRule = $this->bookingRuleResolver->resolve(
            id: $bookingRuleId,
        );

        $this->requestValidator->validate(
            rule: $request->getContent(),
            type: $request->getType(),
        );

        $bookingRule = new BookingRule(
            workspaceId: $bookingRule->getWorkspaceId(),
            name: $request->getName(),
            type: $request->getType(),
            content: $request->getContent(),
            active: $request->isActive(),
            id: $bookingRuleId,
        );

        $bookingRule = $this->bookingRulePersistor->persist(
            rule: $bookingRule,
        );

        return $this->json(
            data: [
                'data' => $bookingRule->normalize(),
            ],
        );
    }
}
