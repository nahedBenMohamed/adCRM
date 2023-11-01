<?php

namespace App\Entity;

use App\Repository\FormationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormationRepository::class)]
class Formation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nomFormation = null;

    #[ORM\Column(length: 255)]
    private ?string $dureeFormation = null;

    #[ORM\Column(length: 255)]
    private ?string $domaineFormation = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $lienFormation = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $adresseFormation = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $pdfFormation = null;

    #[ORM\ManyToOne(inversedBy: 'formations')]
    private ?Formateur $formateur = null;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'formation')]
    private Collection $users;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateDebutFormation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateFinFormation = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $modaliteFormation = null;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }
    public function __toString()
    {
        return $this->getNomFormation();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomFormation(): ?string
    {
        return $this->nomFormation;
    }

    public function setNomFormation(string $nomFormation): self
    {
        $this->nomFormation = $nomFormation;

        return $this;
    }

    public function getDureeFormation(): ?string
    {
        return $this->dureeFormation;
    }

    public function setDureeFormation(string $dureeFormation): self
    {
        $this->dureeFormation = $dureeFormation;

        return $this;
    }

    public function getDomaineFormation(): ?string
    {
        return $this->domaineFormation;
    }

    public function setDomaineFormation(string $domaineFormation): self
    {
        $this->domaineFormation = $domaineFormation;

        return $this;
    }

    public function getLienFormation(): ?string
    {
        return $this->lienFormation;
    }

    public function setLienFormation(string $lienFormation): self
    {
        $this->lienFormation = $lienFormation;

        return $this;
    }

    public function getAdresseFormation(): ?string
    {
        return $this->adresseFormation;
    }

    public function setAdresseFormation(string $adresseFormation): self
    {
        $this->adresseFormation = $adresseFormation;

        return $this;
    }

    public function getPdfFormation(): ?string
    {
        return $this->pdfFormation;
    }

    public function setPdfFormation(string $pdfFormation): self
    {
        $this->pdfFormation = $pdfFormation;

        return $this;
    }

    public function getFormateur(): ?Formateur
    {
        return $this->formateur;
    }

    public function setFormateur(?Formateur $formateur): self
    {
        $this->formateur = $formateur;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addFormation($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeFormation($this);
        }

        return $this;
    }

    public function getDateDebutFormation(): ?\DateTimeInterface
    {
        return $this->dateDebutFormation;
    }

    public function setDateDebutFormation(?\DateTimeInterface $dateDebutFormation): self
    {
        $this->dateDebutFormation = $dateDebutFormation;

        return $this;
    }

    public function getDateFinFormation(): ?\DateTimeInterface
    {
        return $this->dateFinFormation;
    }

    public function setDateFinFormation(?\DateTimeInterface $dateFinFormation): self
    {
        $this->dateFinFormation = $dateFinFormation;

        return $this;
    }

    public function getModaliteFormation(): ?string
    {
        return $this->modaliteFormation;
    }

    public function setModaliteFormation(?string $modaliteFormation): self
    {
        $this->modaliteFormation = $modaliteFormation;

        return $this;
    }

}