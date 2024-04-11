<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Set;

use App\Domain\DataObject\Rule;
use App\Domain\Exception\RuleNotFoundException;

/**
 * @method Rule|null first()
 * @method Rule|null last()
 * @method Rule[]    items()
 * @method add(Rule $item)
 * @method remove(Rule $item)
 */
class RuleSet extends AbstractSet
{
    /**
     * @throws RuleNotFoundException
     */
    public function find(
        int $id,
    ): Rule {
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
            $spaceId = $rule->getSpaceId();
            $result[$spaceId] = $spaceId;
        }

        return array_keys($result);
    }
}
