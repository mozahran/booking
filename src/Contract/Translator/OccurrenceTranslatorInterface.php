<?php

declare(strict_types=1);

namespace App\Contract\Translator;

use App\Domain\DataObject\Booking\Occurrence;
use App\Domain\DataObject\Set\OccurrenceSet;
use App\Domain\Exception\BookingNotFoundException;
use App\Domain\Exception\OccurrenceNotFoundException;
use App\Domain\Exception\UserNotFoundException;
use App\Entity\OccurrenceEntity;

interface OccurrenceTranslatorInterface
{
    public function toOccurrence(
        OccurrenceEntity $entity,
    ): Occurrence;

    /**
     * @param OccurrenceEntity[] $entities
     */
    public function toOccurrenceSet(
        array $entities,
    ): OccurrenceSet;

    /**
     * @throws BookingNotFoundException
     * @throws OccurrenceNotFoundException
     * @throws UserNotFoundException
     */
    public function toOccurrenceEntity(
        Occurrence $occurrence,
    ): OccurrenceEntity;
}
