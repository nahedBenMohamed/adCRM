<?php

namespace App\Entity;

use App\Repository\CompanyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'string',nullable: true)]
    private ?string $infoFilename = "";

    #[ORM\Column(type: 'string',nullable: true)]
    private ?string $contactAdministratif = "";

    #[ORM\Column(type: 'string',nullable: true)]
    private ?string $emailContactAdministratif = "";

    #[ORM\Column(type: 'string',nullable: true)]
    private ?string $signatureConvention = "";

    #[ORM\Column(type: Types::TEXT,nullable: true)]
    private ?string $otherInformation= "";

    public function __construct()
    {
        $this->infoFilename = '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    public function getContactAdministratif(): string
    {
        return $this->contactAdministratif;
    }

    public function setContactAdministratif(string $contactAdministratif): self
    {
        $this->contactAdministratif = $contactAdministratif;

        return $this;
    }

    public function getEmailContactAdministratif(): string
    {
        return $this->emailContactAdministratif;
    }

    public function setEmailContactAdministratif(string $emailContactAdministratif): self
    {
        $this->emailContactAdministratif = $emailContactAdministratif;

        return $this;
    }

    public function getOtherInformation(): string
    {
        return $this->otherInformation;
    }

    public function setOtherInformation(string $otherInformation): self
    {
        $this->otherInformation = $otherInformation;

        return $this;
    }
}
