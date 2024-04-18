<?php

namespace App\Persistor;

use App\Contract\Persistor\BookingPersistorInterface;
use App\Contract\Resolver\BookingResolverInterface;
use App\Contract\Translator\BookingTranslatorInterface;
use App\Contract\Translator\OccurrenceTranslatorInterface;
use App\Domain\DataObject\Booking\Booking;
use Doctrine\ORM\EntityManagerInterface;

final readonly class BookingPersistor implements BookingPersistorInterface
{
    public function __construct(
        private BookingResolverInterface $bookingResolver,
        private BookingTranslatorInterface $bookingTranslator,
        private OccurrenceTranslatorInterface $occurrenceTranslator,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function persist(
        Booking $booking,
    ): Booking {
        $bookingEntity = $this->bookingTranslator->toBookingEntity($booking);
        if (!$bookingEntity->getId()) {
            $this->entityManager->persist($bookingEntity);
        }
        $this->entityManager->flush();

        $occurrences = $booking->getOccurrences()->items();
        foreach ($occurrences as $occurrence) {
            $occurrenceEntity = $this->occurrenceTranslator->toOccurrenceEntity(
                occurrence: $occurrence,
            );
            $occurrenceEntity->setBooking($bookingEntity);
            if (!$occurrence->getId()) {
                $this->entityManager->persist($occurrenceEntity);
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        return $this->bookingResolver->resolve(id: $bookingEntity->getId());
    }
}
