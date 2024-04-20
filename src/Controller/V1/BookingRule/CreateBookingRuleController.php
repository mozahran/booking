<?php

declare(strict_types=1);

namespace App\Controller\V1\BookingRule;

use App\Contract\Persistor\BookingRulePersistorInterface;
use App\Contract\Service\BookingRule\RuleRequestValidatorInterface;
use App\Domain\DataObject\BookingRule;
use App\Request\BookingRuleRequest;
use App\Security\BookingRuleVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CreateBookingRuleController extends AbstractController
{
    public function __construct(
        private readonly RuleRequestValidatorInterface $ruleRequestValidator,
        private readonly BookingRulePersistorInterface $bookingRulePersistor,
    ) {
    }

    #[Route(path: '/v1/booking-rule', name: 'app_booking-rule_create', methods: ['POST'])]
    #[IsGranted(attribute: BookingRuleVoter::MANAGE, subject: 'request', message: 'Access Denied!')]
    public function __invoke(
        BookingRuleRequest $request,
    ): JsonResponse {
        $this->ruleRequestValidator->validate(
            rule: $request->getContent(),
            type: $request->getType(),
        );

        $bookingRule = new BookingRule(
            workspaceId: $request->getWorkspaceId(),
            name: $request->getName(),
            type: $request->getType(),
            content: $request->getContent(),
            active: true,
        );

        $bookingRule = $this->bookingRulePersistor->persist(
            rule: $bookingRule,
        );

        return $this->json(
            data: [
                'data' => $bookingRule->normalize(),
            ],
            status: Response::HTTP_CREATED,
        );
    }
}
