<?php

namespace App\Entity;

use App\Repository\FormationRepository;
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
    private ?string $timesheet = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $linkType = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $lienFormation = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $lieuFormation = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $formationAddress = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $objective = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $infoTrainees = null;
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $infoCustomer = null;
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $infoFormateur = null;
    #[ORM\Column(length: 255,nullable: true)]
    private ?string $pdfFormation = null;

    #[ORM\ManyToOne(targetEntity: Link::class, inversedBy: "formations")]
    private ?Link $linkToProgram = null;

    #[ORM\ManyToOne(targetEntity: Link::class, inversedBy: "formations")]
    private ?Link $linkToLivretAccueil = null;

    #[ORM\ManyToOne(targetEntity: Link::class, inversedBy: "formations")]
    private ?Link $linkGuide = null;

    #[ORM\ManyToOne(targetEntity: Link::class, inversedBy: "formations")]
    private ?Link $linkFormulaire = null;


    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "formations")]
    private ?User $formateur;

    #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: "formations")]
    private ?Customer $customer;

    #[ORM\ManyToOne(targetEntity: Financier::class, inversedBy: "formations")]
    private ?Financier $financier;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateDebutFormation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateFinFormation = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $modaliteFormation = null;

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

    public function getLinkType(): ?string
    {
        return $this->linkType;
    }

    public function setLinkType(string $linkType): self
    {
        $this->linkType = $linkType;

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

    public function getLieuFormation(): ?string
    {
        return $this->lieuFormation;
    }

    public function setLieuFormation(string $lieuFormation): self
    {
        $this->lieuFormation = $lieuFormation;

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

    public function getFormateur(): ?User
    {
        return $this->formateur;
    }

    public function setFormateur(?User $formateur): self
    {
        $this->formateur = $formateur;

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

    public function getTimesheet(): ?string
    {
        return $this->timesheet;
    }
    public function setTimesheet(string $timesheet): self
    {
        $this->timesheet = $timesheet;

        return $this;
    }

    public function getFormationAddress(): ?string
    {
        return $this->formationAddress;
    }
    public function setFormationAddress(string $formationAddress): self
    {
        $this->formationAddress = $formationAddress;

        return $this;
    }

    public function getObjective(): ?string
    {
        return $this->objective;
    }
    public function setObjective(string $objective): self
    {
        $this->objective = $objective;

        return $this;
    }

    public function getInfoFormateur(): ?string
    {
        return $this->infoFormateur;
    }
    public function setInfoFormateur(string $infoFormateur): self
    {
        $this->infoFormateur = $infoFormateur;

        return $this;
    }

    public function getInfoCustomer(): ?string
    {
        return $this->infoCustomer;
    }
    public function setInfoCustomer(string $infoCustomer): self
    {
        $this->infoCustomer = $infoCustomer;

        return $this;
    }

    public function getInfoTrainees(): ?string
    {
        return $this->infoTrainees;
    }
    public function setInfoTrainees(string $infoTrainees): self
    {
        $this->infoTrainees = $infoTrainees;

        return $this;
    }

    public function getLinkToProgram(): ?Link
    {
        return $this->linkToProgram;
    }
    public function setLinkToProgram(Link $linkToProgram): self
    {
        $this->linkToProgram= $linkToProgram;

        return $this;
    }

    public function getLinkToLivretAccueil(): ?Link
    {
        return $this->linkToLivretAccueil;
    }

    public function setLinkToLivretAccueil(Link $linkToLivretAccueil): self
    {
        $this->linkToLivretAccueil = $linkToLivretAccueil;

        return $this;
    }

    public function getLinkGuide(): ?Link
    {
        return $this->linkGuide;
    }

    public function setLinkGuide(Link $linkGuide): self
    {
        $this->linkGuide = $linkGuide;

        return $this;
    }

    public function getLinkFormulaire(): ?Link
    {
        return $this->linkFormulaire;
    }

    public function setLinkFormulaire(Link $linkFormulaire): self
    {
        $this->linkFormulaire = $linkFormulaire;

        return $this;
    }


    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getFinancier(): ?Financier
    {
        return $this->financier;
    }

    public function setFinancier(?Financier $financier): self
    {
        $this->financier = $financier;

        return $this;
    }

}