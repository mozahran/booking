<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Set;

use App\Domain\DataObject\Space;
use App\Domain\Exception\SpaceNotFoundException;

/**
 * @method Space|null first()
 * @method Space|null last()
 * @method Space[]    items()
 * @method add(Space $item)
 * @method remove(Space $item)
 */
class SpaceSet extends AbstractSet
{
    /**
     * @throws SpaceNotFoundException
     */
    public function find(
        int $id,
    ): Space {
        $items = $this->items();

        foreach ($items as $item) {
            if ($item->getId() === $id) {
                return $item;
            }
        }

        throw new SpaceNotFoundException(id: $id);
    }

    /**
     * @return int[]
     */
    public function workspaceIds(): array
    {
        $result = [];
        $spaces = $this->items();
        foreach ($spaces as $space) {
            $workspaceId = $space->getWorkspaceId();
            $result[$workspaceId] = $workspaceId;
        }

        return array_keys($result);
    }
}
