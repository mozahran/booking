<?php

declare(strict_types=1);

namespace App\Contract\Service;

use App\Domain\DataObject\BookingRule;
use App\Domain\DataObject\Provider;
use App\Domain\DataObject\Space;
use App\Domain\DataObject\User;
use App\Domain\DataObject\Workspace;

/**
 * The phoenix is an immortal bird that cyclically regenerates or is otherwise born again.
 */
interface PhoenixInterface
{
    public function activateProvider(
        Provider $provider,
    ): void;

    public function deactivateProvider(
        Provider $provider,
    ): void;

    public function activateSpace(
        Space $space,
    ): void;

    public function deactivateSpace(
        Space $space,
    ): void;

    public function activateWorkspace(
        Workspace $workspace,
    ): void;

    public function deactivateWorkspace(
        Workspace $workspace,
    ): void;

    public function activateUser(
        User $user,
    ): void;

    public function deactivateUser(
        User $user,
    ): void;

    public function activateBookingRule(
        BookingRule $bookingRule,
    ): void;

    public function deactivateBookingRule(
        BookingRule $bookingRule,
    ): void;
}
