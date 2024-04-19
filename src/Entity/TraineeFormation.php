<?php
namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\TraineeFormationRepository;

#[ORM\Entity(repositoryClass: TraineeFormationRepository::class)]
class TraineeFormation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: "Formation")]
    #[ORM\JoinColumn(name: "formation_id", referencedColumnName: "id")]
    private $formation;

    #[ORM\ManyToOne(targetEntity: "Trainee")]
    #[ORM\JoinColumn(name: "trainee_id", referencedColumnName: "id")]
    private $trainee;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $sendConvocation = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateAffectationFormation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateConvocation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFormation()
    {
        return $this->formation;
    }

    public function setFormation($formation)
    {
        $this->formation = $formation;
        return $this;
    }

    public function getTrainee()
    {
        return $this->trainee;
    }

    public function setTrainee($trainee)
    {
        $this->trainee = $trainee;
        return $this;
    }

    public function getSendConvocation(): ?string
    {
        return $this->sendConvocation;
    }

    public function setSendConvocation(?string $sendConvocation): self
    {
        $this->sendConvocation = $sendConvocation;
        return $this;
    }

    public function getDateAffectationFormation(): ?\DateTimeInterface
    {
        return $this->dateAffectationFormation;
    }

    public function setDateAffectationFormation(?\DateTimeInterface $dateAffectationFormation): self
    {
        $this->dateAffectationFormation = $dateAffectationFormation;

        return $this;
    }

    public function getDateConvocation(): ?\DateTimeInterface
    {
        return $this->dateConvocation;
    }

    public function setDateConvocation(?\DateTimeInterface $dateConvocation): self
    {
        $this->dateConvocation = $dateConvocation;

        return $this;
    }
}
