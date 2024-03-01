<?php

namespace App\Entity;

use App\Repository\AnnouncementsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnnouncementsRepository::class)]
class Announcements
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $contenu = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $listeCreneaux = null;

    #[ORM\Column(length: 255)]
    private ?string $categorie = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $limit_date = null;

    #[ORM\Column]
    private ?bool $status = null;

    #[ORM\Column(length: 255)]
    private ?string $ville = null;

    #[ORM\Column(length: 255)]
    private ?string $numero_rue = null;

    #[ORM\Column(length: 255)]
    private ?string $rue = null;

    #[ORM\Column(length: 255)]
    private ?string $code_postal = null;

    #[ORM\Column(length: 255)]
    private ?string $complement = null;

    #[ORM\ManyToOne(inversedBy: 'announcements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column(nullable: true)]
    private ?bool $allergenes = null;

    #[ORM\OneToMany(mappedBy: 'announcement', targetEntity: Reservations::class)]
    private Collection $reservations;

    #[ORM\OneToOne(mappedBy: 'announcement', cascade: ['persist', 'remove'])]
    private ?PositionGPS $positionGPS = null;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
        $this->contenu = [];
        $this->listeCreneaux = [];
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(string $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getLimitDate(): ?\DateTimeInterface
    {
        return $this->limit_date;
    }

    public function setLimitDate(\DateTimeInterface $limit_date): static
    {
        $this->limit_date = $limit_date;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): static
    {
        $this->ville = $ville;

        return $this;
    }


    public function getNumeroRue(): ?string
    {
        return $this->numero_rue;
    }

    public function setNumeroRue(string $numero_rue): static
    {
        $this->numero_rue = $numero_rue;

        return $this;
    }

    public function getRue(): ?string
    {
        return $this->rue;
    }

    public function setRue(string $rue): static
    {
        $this->rue = $rue;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->code_postal;
    }

    public function setCodePostal(string $code_postal): static
    {
        $this->code_postal = $code_postal;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function isAllergenes(): ?bool
    {
        return $this->allergenes;
    }

    public function setAllergenes(?bool $allergenes): static
    {
        $this->allergenes = $allergenes;

        return $this;
    }

    public function getComplement(): ?string
    {
        return $this->complement;
    }
    public function setComplement(?string $complement): static
    {
        $this->complement = $complement;
        return $this;
    }

    /**
     * @return Collection<int, Reservations>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservations $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setAnnouncement($this);
        }

        return $this;
    }

    public function removeReservation(Reservations $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getAnnouncement() === $this) {
                $reservation->setAnnouncement(null);
            }
        }

        return $this;
    }
    public function getContenu(): ?array
    {
        return $this->contenu;
    }

    public function setContenu(?array $contenu): static
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function addContenuItem(string $item, int $quantite, $codeEAN): static
    {
        $this->contenu[] = ['item' => $item, 'quantite' => $quantite, 'codeEAN' => $codeEAN];

        return $this;
    }

    public function removeContenuItem(int $index): static
    {
        if (isset($this->contenu[$index])) {
            unset($this->contenu[$index]);
            // Réorganiser les clés après la suppression
            $this->contenu = array_values($this->contenu);
        }

        return $this;
    }

    public function getPositionGPS(): ?PositionGPS
    {
        return $this->positionGPS;
    }

    public function setPositionGPS(PositionGPS $positionGPS): static
    {
        // set the owning side of the relation if necessary
        if ($positionGPS->getAnnouncement() !== $this) {
            $positionGPS->setAnnouncement($this);
        }

        $this->positionGPS = $positionGPS;

        return $this;
    }

    public function getListeCreneaux(): ?array
    {
        return $this->listeCreneaux;
    }

    public function setListeCreneaux(?array $listeCreneaux): static
    {
        $this->listeCreneaux = $listeCreneaux;

        return $this;
    }

    public function addCreneau(\DateTimeImmutable  $day, \DateTimeImmutable  $dateDebut, \DateTimeImmutable  $dateEnd): static
    {
        $this->listeCreneaux[] = [
            'day' => $day->format('Y-m-d'),
            'slot' => [
                'dateDebut' => $dateDebut->format('H:i:s'),
                'dateEnd' => $dateEnd->format('H:i:s'),
            ],
        ];

        return $this;
    }

    public function removeCreneau(int $index): static
    {
        if (isset($this->listeCreneaux[$index])) {
            unset($this->listeCreneaux[$index]);
            // Réorganiser les clés après la suppression
            $this->listeCreneaux = array_values($this->listeCreneaux);
        }

        return $this;
    }
}
