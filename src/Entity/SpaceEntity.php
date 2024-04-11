<?php

namespace App\Entity;

use App\Repository\SpaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SpaceRepository::class)]
#[ORM\Table(name: 'space')]
class SpaceEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'spaces')]
    #[ORM\JoinColumn(nullable: false)]
    private ?WorkspaceEntity $workspace = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(targetEntity: BookingEntity::class, mappedBy: 'space', orphanRemoval: true)]
    private Collection $bookings;

    #[ORM\Column]
    private ?bool $active = true;

    #[ORM\OneToMany(targetEntity: RuleEntity::class, mappedBy: 'space', orphanRemoval: true)]
    private Collection $rules;

    public function __construct()
    {
        $this->bookings = new ArrayCollection();
        $this->rules = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWorkspace(): ?WorkspaceEntity
    {
        return $this->workspace;
    }

    public function setWorkspace(?WorkspaceEntity $workspace): static
    {
        $this->workspace = $workspace;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, BookingEntity>
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(BookingEntity $booking): static
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings->add($booking);
            $booking->setSpace($this);
        }

        return $this;
    }

    public function removeBooking(BookingEntity $booking): static
    {
        if ($this->bookings->removeElement($booking)) {
            // set the owning side to null (unless already changed)
            if ($booking->getSpace() === $this) {
                $booking->setSpace(null);
            }
        }

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Collection<int, RuleEntity>
     */
    public function getRules(): Collection
    {
        return $this->rules;
    }
}
