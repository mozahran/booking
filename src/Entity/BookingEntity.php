<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\BookingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookingRepository::class)]
#[ORM\Table(name: 'booking')]
class BookingEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?UserEntity $user = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SpaceEntity $space = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $startsAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $endsAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $recurrenceRule = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    private ?array $excludedDates = null;

    #[ORM\Column]
    private ?bool $cancelled = false;

    #[ORM\OneToMany(
        targetEntity: OccurrenceEntity::class,
        mappedBy: 'booking',
        cascade: [
            'persist',
            'remove',
        ],
        orphanRemoval: true,
    )]
    private Collection $occurrences;

    #[ORM\ManyToOne(inversedBy: 'cancelledByBookings')]
    private ?UserEntity $canceller = null;

    public function __construct()
    {
        $this->occurrences = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?UserEntity
    {
        return $this->user;
    }

    public function setUser(?UserEntity $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getSpace(): ?SpaceEntity
    {
        return $this->space;
    }

    public function setSpace(?SpaceEntity $space): static
    {
        $this->space = $space;

        return $this;
    }

    public function getCanceller(): ?UserEntity
    {
        return $this->canceller;
    }

    public function setCanceller(?UserEntity $canceller): static
    {
        $this->canceller = $canceller;

        return $this;
    }

    public function getStartsAt(): ?\DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function setStartsAt(\DateTimeImmutable $startsAt): static
    {
        $this->startsAt = $startsAt;

        return $this;
    }

    public function getEndsAt(): ?\DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function setEndsAt(\DateTimeImmutable $endsAt): static
    {
        $this->endsAt = $endsAt;

        return $this;
    }

    public function getRecurrenceRule(): ?string
    {
        return $this->recurrenceRule;
    }

    public function setRecurrenceRule(?string $recurrenceRule): static
    {
        $this->recurrenceRule = $recurrenceRule;

        return $this;
    }

    public function getExcludedDates(): ?array
    {
        return $this->excludedDates;
    }

    public function setExcludedDates(?array $excludedDates): static
    {
        $this->excludedDates = $excludedDates;

        return $this;
    }

    public function isCancelled(): ?bool
    {
        return $this->cancelled;
    }

    public function setCancelled(bool $cancelled): static
    {
        $this->cancelled = $cancelled;

        return $this;
    }

    /**
     * @return Collection<int, OccurrenceEntity>
     */
    public function getOccurrences(): Collection
    {
        return $this->occurrences;
    }

    public function addOccurrence(OccurrenceEntity $occurrence): static
    {
        if (!$this->occurrences->contains($occurrence)) {
            $this->occurrences->add($occurrence);
            $occurrence->setBooking($this);
        }

        return $this;
    }

    public function removeOccurrence(OccurrenceEntity $booking): static
    {
        if ($this->occurrences->removeElement($booking)) {
            // set the owning side to null (unless already changed)
            if ($booking->getBooking() === $this) {
                $booking->setBooking(null);
            }
        }

        return $this;
    }
}
