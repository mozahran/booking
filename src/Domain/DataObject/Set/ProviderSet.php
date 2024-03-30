<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Set;

use App\Domain\DataObject\Provider;

/**
 * @method Provider|null first()
 * @method Provider|null last()
 * @method Provider[]    items()
 * @method add(Provider $item)
 * @method remove(Provider $item)
 */
class ProviderSet extends AbstractSet
{
}
