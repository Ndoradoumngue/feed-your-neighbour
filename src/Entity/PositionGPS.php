<?php

namespace App\Entity;

use App\Repository\PositionGPSRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PositionGPSRepository::class)]
class PositionGPS
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $long = null;

    #[ORM\Column(length: 255)]
    private ?string $lat = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\OneToOne(inversedBy: 'positionGPS', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Announcements $announcement = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLong(): ?string
    {
        return $this->long;
    }

    public function setLong(string $long): static
    {
        $this->long = $long;

        return $this;
    }

    public function getLat(): ?string
    {
        return $this->lat;
    }

    public function setLat(string $lat): static
    {
        $this->lat = $lat;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getAnnouncement(): ?Announcements
    {
        return $this->announcement;
    }

    public function setAnnouncement(Announcements $announcement): static
    {
        $this->announcement = $announcement;

        return $this;
    }
}
