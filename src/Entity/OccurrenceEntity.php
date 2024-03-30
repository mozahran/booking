<?php

namespace App\Entity;

use App\Repository\BookingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookingRepository::class)]
#[ORM\Table(name: 'occurrence')]
class OccurrenceEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'occurrences')]
    #[ORM\JoinColumn(nullable: false)]
    private ?BookingEntity $booking = null;

    #[ORM\ManyToOne]
    private ?UserEntity $canceller = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $startsAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $endsAt = null;

    #[ORM\Column]
    private ?bool $cancelled = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBooking(): ?BookingEntity
    {
        return $this->booking;
    }

    public function setBooking(?BookingEntity $booking): static
    {
        $this->booking = $booking;

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

    public function isCancelled(): ?bool
    {
        return $this->cancelled;
    }

    public function setCancelled(bool $cancelled): static
    {
        $this->cancelled = $cancelled;

        return $this;
    }
}
