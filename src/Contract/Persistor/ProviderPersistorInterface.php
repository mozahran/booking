<?php

namespace App\Contract\Persistor;

use App\Domain\DataObject\Provider;
use App\Domain\Exception\ProviderNotFoundException;
use App\Domain\Exception\UserNotFoundException;

interface ProviderPersistorInterface
{
    /**
     * @throws ProviderNotFoundException
     * @throws UserNotFoundException
     */
    public function persist(
        Provider $provider,
    ): Provider;
}
