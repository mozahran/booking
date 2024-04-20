<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Set;

use App\Domain\DataObject\BookingRule;
use App\Domain\Exception\RuleNotFoundException;

/**
 * @method BookingRule|null first()
 * @method BookingRule|null last()
 * @method BookingRule[]    items()
 * @method add(BookingRule $item)
 * @method remove(BookingRule $item)
 */
class BookingRuleSet extends AbstractSet
{
    /**
     * @throws RuleNotFoundException
     */
    public function find(
        int $id,
    ): BookingRule {
        $items = $this->items();

        foreach ($items as $item) {
            if ($item->getId() === $id) {
                return $item;
            }
        }

        throw new RuleNotFoundException(id: $id);
    }

    /**
     * @return int[]
     */
    public function spaceIds(): array
    {
        $result = [];
        $rules = $this->items();
        foreach ($rules as $rule) {
            $spaceId = $rule->getWorkspaceId();
            $result[$spaceId] = $spaceId;
        }

        return array_keys($result);
    }
}
