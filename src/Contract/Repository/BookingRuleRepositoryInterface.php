<?php

namespace App\Contract\Repository;

use App\Domain\DataObject\BookingRule;
use App\Domain\DataObject\Set\BookingRuleSet;
use App\Domain\Exception\BookingRuleNotFoundException;

interface BookingRuleRepositoryInterface
{
    /**
     * @throws BookingRuleNotFoundException
     */
    public function findOne(
        int $id,
    ): BookingRule;

    public function findManyByWorkspace(
        int $workspaceId,
    ): BookingRuleSet;

    public function activate(
        int $id,
    ): void;

    public function deactivate(
        int $id,
    ): void;
}
