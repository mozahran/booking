<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Set;

use App\Domain\DataObject\User;

/**
 * @method User|null first()
 * @method User|null last()
 * @method User[]    items()
 * @method add(User $item)
 * @method remove(User $item)
 */
class UserSet extends AbstractSet
{
}
