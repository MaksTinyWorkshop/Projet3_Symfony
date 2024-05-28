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
    #[ORM\OneToOne(targetEntity: Sortie::class)]
    #[ORM\JoinColumn(name: 'sorties_id_sortie', referencedColumnName: 'id', nullable: false)]
    private ?int $sortiesIdSortie = null;

    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: Participants::class)]
    #[ORM\JoinColumn(name: 'participants_id_participants', referencedColumnName: 'id', nullable: false)]
    private ?int $participantsIdParticipant = null;


    public function getDateInscription(): ?\DateTimeInterface
    {
        return $this->dateInscription;
    }

    public function setDateInscription(\DateTimeInterface $dateInscription): static
    {
        $this->dateInscription = $dateInscription;

        return $this;
    }

    public function getSortiesIdSortie(): ?int
    {
        return $this->sortiesIdSortie;
    }

    public function setSortiesIdSortie(int $sortiesIdSortie): static
    {
        $this->sortiesIdSortie = $sortiesIdSortie;

        return $this;
    }

    public function getParticipantsIdParticipant(): ?int
    {
        return $this->participantsIdParticipant;
    }

    public function setParticipantsIdParticipant(int $participantsIdParticipant): static
    {
        $this->participantsIdParticipant = $participantsIdParticipant;

        return $this;
    }

}
