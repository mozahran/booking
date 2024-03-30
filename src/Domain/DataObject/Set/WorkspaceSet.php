<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Set;

use App\Domain\DataObject\Workspace;

/**
 * @method Workspace|null first()
 * @method Workspace|null last()
 * @method Workspace[]    items()
 * @method add(Workspace $item)
 * @method remove(Workspace $item)
 */
class WorkspaceSet extends AbstractSet
{
    /**
     * @return int[]
     */
    public function providerIds(): array
    {
        $result = [];
        $items = $this->items();
        foreach ($items as $item) {
            $providerId = $item->getProviderId();
            $result[$providerId] = $providerId;
        }

        return array_keys($result);
    }
}
