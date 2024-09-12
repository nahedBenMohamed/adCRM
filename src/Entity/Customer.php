<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'Il existe déjà un compte avec cette email')]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contactAdministrative = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $otherInfo= null;

    #[ORM\Column(type: 'string',nullable: true)]
    private ?string $infoFilename = "";

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getContactAdministrative(): ?string
    {
        return $this->contactAdministrative;
    }

    public function setContactAdministrative(?string $contactAdministrative): static
    {
        $this->contactAdministrative = $contactAdministrative;

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

    public function getOtherInfo(): ?string
    {
        return $this->otherInfo;
    }

    public function setOtherInfo(?string $otherInfo): self
    {
        $this->otherInfo = $otherInfo;
        return $this;
    }

    public function getInfoFilename(): string
    {
        return $this->infoFilename ? $this->infoFilename:'';
    }

    public function setInfoFilename(string $infoFilename): self
    {
        $this->infoFilename = $infoFilename;

        return $this;
    }
}
