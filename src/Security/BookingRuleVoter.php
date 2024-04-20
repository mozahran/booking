<?php

declare(strict_types=1);

namespace App\Security;

use App\Contract\Resolver\BookingRuleResolverInterface;
use App\Contract\Resolver\WorkspaceResolverInterface;
use App\Contract\Service\NexusInterface;
use App\Request\BookingRuleRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class BookingRuleVoter extends Voter
{
    public const MANAGE = 'MANAGE_BOOKING_RULE';

    public function __construct(
        private readonly NexusInterface $nexus,
        private readonly WorkspaceResolverInterface $workspaceResolver,
        private readonly BookingRuleResolverInterface $bookingRuleResolver,
    ) {
    }

    protected function supports(
        string $attribute,
        mixed $subject,
    ): bool {
        return self::MANAGE === $attribute;
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token,
    ): bool {
        if (!$subject instanceof BookingRuleRequest) {
            return false;
        }

        $user = $token->getUser();
        if ($this->nexus->isAdmin($user)) {
            return true;
        }

        if (Request::METHOD_PUT === $subject->request()->getMethod()) {
            $bookingRule = $this->bookingRuleResolver->resolve(id: $subject->getBookingRuleId());

            return $this->nexus->isBookingRuleOwner(
                bookingRule: $bookingRule,
                user: $user,
            );
        }

        $workspace = $this->workspaceResolver->resolve(id: $subject->getWorkspaceId());

        return $this->nexus->isWorkspaceOwner(
            workspace: $workspace,
            user: $user,
        );
    }
}
