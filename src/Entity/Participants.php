<?php

namespace App\Entity;

use App\Repository\ParticipantsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ParticipantsRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['pseudo'], message: 'Il existe déjà un compte avec ce pseudo')]
class Participants implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 30)]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9_]{4,20}$/',
        message: 'Le pseudo doit contenir entre 4 et 20 caractères et ne peut contenir que des lettres, des chiffres et des underscores.')]
    private ?string $pseudo = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Ce champ ne peut pas être vide')]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Ce champ ne peut pas être vide')]
    private ?string $prenom = null;

    #[ORM\Column(length: 30)]
    private ?string $telephone = null;

    #[ORM\Column]
    private ?bool $isActif = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Site $site = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $resetToken = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\OneToMany(mappedBy: 'createur', targetEntity: GroupePrive::class)]
    private Collection $groupesPrivesCree;

    #[ORM\ManyToMany(targetEntity: GroupePrive::class, mappedBy: 'participants')]
    private Collection $groupesPrives;

    public function __construct()
    {
        $this->isActif = true;
        $this->roles = ['ROLE_USER'];
        $this->groupesPrivesCree = new ArrayCollection();
        $this->groupesPrives = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // Clear temporary sensitive data
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;
        return $this;
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

    public function isActif(): ?bool
    {
        return $this->isActif;
    }

    public function setActif(bool $isActif): static
    {
        $this->isActif = $isActif;
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

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): static
    {
        $this->resetToken = $resetToken;
        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;
        return $this;
    }

    /**
     * @return Collection<int, GroupePrive>
     */
    public function getGroupesPrivesCree(): Collection
    {
        return $this->groupesPrivesCree;
    }

    public function addGroupePriveCree(GroupePrive $groupePrive): static
    {
        if (!$this->groupesPrivesCree->contains($groupePrive)) {
            $this->groupesPrivesCree->add($groupePrive);
            $groupePrive->setCreateur($this);
        }
        return $this;
    }

    public function removeGroupePriveCree(GroupePrive $groupePrive): static
    {
        if ($this->groupesPrivesCree->removeElement($groupePrive)) {
            // Set the owning side to null (unless already changed)
            if ($groupePrive->getCreateur() === $this) {
                $groupePrive->setCreateur(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, GroupePrive>
     */
    public function getGroupesPrives(): Collection
    {
        return $this->groupesPrives;
    }

    public function addGroupePrive(GroupePrive $groupePrive): static
    {
        if (!$this->groupesPrives->contains($groupePrive)) {
            $this->groupesPrives->add($groupePrive);
            $groupePrive->addParticipant($this);
        }
        return $this;
    }

    public function removeGroupePrive(GroupePrive $groupePrive): static
    {
        if ($this->groupesPrives->removeElement($groupePrive)) {
            $groupePrive->removeParticipant($this);
        }
        return $this;
    }
}
