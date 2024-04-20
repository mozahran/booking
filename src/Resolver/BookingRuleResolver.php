<?php

declare(strict_types=1);

namespace App\Resolver;

use App\Contract\Resolver\BookingRuleResolverInterface;
use App\Domain\DataObject\BookingRule;
use App\Domain\DataObject\Set\BookingRuleSet;
use App\Repository\BookingRuleRepository;

final readonly class BookingRuleResolver implements BookingRuleResolverInterface
{
    public function __construct(
        private BookingRuleRepository $bookingRuleEntity,
    ) {
    }

    public function resolveManyForWorkspace(
        int $workspaceId,
    ): BookingRuleSet {
        return $this->bookingRuleEntity->findManyByWorkspace(
            workspaceId: $workspaceId,
        );
    }

    public function resolve(
        int $id,
    ): BookingRule {
        return $this->bookingRuleEntity->findOne(id: $id);
    }
}
