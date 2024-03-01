<?php

namespace App\Entity;

use App\Repository\ReservationsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationsRepository::class)]
class Reservations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $benef = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Announcements $announcement = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $creneau_start = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $creneau_end = null;

    #[ORM\Column]
    private ?int $status = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBenef(): ?User
    {
        return $this->benef;
    }

    public function setBenef(?User $benef): static
    {
        $this->benef = $benef;

        return $this;
    }

    public function getAnnouncement(): ?Announcements
    {
        return $this->announcement;
    }

    public function setAnnouncement(?Announcements $announcement): static
    {
        $this->announcement = $announcement;

        return $this;
    }

    public function getCreneauStart(): ?\DateTimeInterface
    {
        return $this->creneau_start;
    }

    public function setCreneauStart(\DateTimeInterface $creneau_start): static
    {
        $this->creneau_start = $creneau_start;

        return $this;
    }

    public function getCreneauEnd(): ?\DateTimeInterface
    {
        return $this->creneau_end;
    }

    public function setCreneauEnd(\DateTimeInterface $creneau_end): static
    {
        $this->creneau_end = $creneau_end;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }
}
