<?php

namespace App\Entity;

use App\Repository\InscriptionsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InscriptionsRepository::class)]
class Inscriptions
{

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateInscription = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Sortie::class)]
    #[ORM\JoinColumn(name: 'sorties_id_sortie', referencedColumnName: 'id', nullable: false)]
    private ?Sortie $sortie = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Participants::class)]
    #[ORM\JoinColumn(name: 'participants_id_participant', referencedColumnName: 'id', nullable: false)]
    private ?Participants $participant = null;

    public function __construct()
    {
        $this->dateInscription = new \DateTime();
    }

    public function getDateInscription(): ?\DateTimeInterface
    {
        return $this->dateInscription;
    }

    public function setDateInscription(\DateTimeInterface $dateInscription): static
    {
        $this->dateInscription = $dateInscription;

        return $this;
    }

    public function getSortie(): ?Sortie
    {
        return $this->sortie;
    }

    public function setSortie(?Sortie $sortie): static
    {
        $this->sortie = $sortie;

        return $this;
    }

    public function getParticipant(): ?Participants
    {
        return $this->participant;
    }

    public function setParticipant(?Participants $participant): static
    {
        $this->participant = $participant;

        return $this;
    }
}