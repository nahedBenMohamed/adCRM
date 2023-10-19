<?php

namespace App\Entity;

use App\Repository\StagiaireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StagiaireRepository::class)]
class Stagiaire extends User
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'stagiaire_id', referencedColumnName: 'id')]
    private User $id;

    #[ORM\Column(length: 255)]
    private ?string $civilite = null;

    #[ORM\Column(length: 255)]
    private ?string $fonction = null;

    #[ORM\ManyToOne(inversedBy: 'stagiaires')]
    private ?Employeur $employeur = null;

    #[ORM\ManyToMany(targetEntity: Formation::class, mappedBy: 'stagiaires')]
    private Collection $formations;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $entreprise = null;

    public function __construct()
    {
        $this->formations = new ArrayCollection();
    }
    public function getCivilite(): ?string
    {
        return $this->civilite;
    }

    public function setCivilite(string $civilite): self
    {
        $this->civilite = $civilite;

        return $this;
    }

    public function getFonction(): ?string
    {
        return $this->fonction;
    }

    public function setFonction(string $fonction): self
    {
        $this->fonction = $fonction;

        return $this;
    }

    public function getEmployeur(): ?Employeur
    {
        return $this->employeur;
    }

    public function setEmployeur(?Employeur $employeur): self
    {
        $this->employeur = $employeur;

        return $this;
    }

    /**
     * @return Collection<int, Formation>
     */
    public function getFormations(): Collection
    {
        return $this->formations;
    }

    public function addFormation(Formation $formation): self
    {
        if (!$this->formations->contains($formation)) {
            $this->formations->add($formation);
            $formation->addStagiaire($this);
        }

        return $this;
    }

    public function removeFormation(Formation $formation): self
    {
        if ($this->formations->removeElement($formation)) {
            $formation->removeStagiaire($this);
        }

        return $this;
    }

    public function getEntreprise(): ?string
    {
        return $this->entreprise;
    }

    public function setEntreprise(?string $entreprise): self
    {
        $this->entreprise = $entreprise;

        return $this;
    }

    public function __toString()
    {
        return $this->getNom();
    }

}
