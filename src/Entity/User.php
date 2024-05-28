<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email', 'pseudo'], message: 'Il existe déjà un compte associé à cet email/pseudo')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9_]{4,20}$/',
        message: 'Le pseudo doit contenir entre 4 et 20 caractères et ne peut contenir que des lettres, des chiffres et des underscores.')]
    private ?string $pseudo = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'Ce champ ne peut pas être vide')]
    private ?string $nom = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'Ce champ ne peut pas être vide')]
    private ?string $prenom = null;

    #[ORM\Column(length: 10)]
    #[Assert\Regex(
        pattern: '/^\d{10}$/',
        message: 'Votre numéro de téléphone n\'est pas valide'
    )]
    private ?string $telephone = null;


    #[ORM\Column]
    private ?bool $isAdmin = null;

    #[ORM\Column]
    private ?bool $isActif = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 8, max: 20)]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_])[a-zA-Z\d\W_]{8,20}$/',
        message: 'Le mot de passe doit contenir entre 8 et 20 caractères, avec au moins une majuscule, une minuscule, un chiffre et un caractère spécial.'
    )]
    private ?string $password = null;

    /**
     * @var Collection<int, Sortie>
     */
    #[ORM\OneToMany(targetEntity: Sortie::class, mappedBy: 'organisateur')]
    private Collection $sorties;

    #[ORM\ManyToOne(inversedBy: 'participant')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Site $site = null;

    public function __construct()
    {
        $this->sorties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(?string $pseudo): void
    {
        $this->pseudo = $pseudo;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function isAdmin(): ?bool
    {
        return $this->isAdmin;
    }

    public function setAdmin(bool $isAdmin): static
    {
        $this->isAdmin = $isAdmin;

        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->isActif;
    }

    public function setActif(bool $isActif): static
    {
        $this->isActif = $isActif;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSorties(): Collection
    {
        return $this->sorties;
    }

    public function addSortie(Sortie $sortie): static
    {
        if (!$this->sorties->contains($sortie)) {
            $this->sorties->add($sortie);
            $sortie->setOrganisateur($this);
        }

        return $this;
    }

    public function removeSortie(Sortie $sortie): static
    {
        if ($this->sorties->removeElement($sortie)) {
            // set the owning side to null (unless already changed)
            if ($sortie->getOrganisateur() === $this) {
                $sortie->setOrganisateur(null);
            }
        }

        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): static
    {
        $this->site = $site;

        return $this;
    }
}
