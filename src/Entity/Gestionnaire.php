<?php

namespace App\Entity;

use App\Repository\GestionnaireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GestionnaireRepository::class)]
class Gestionnaire extends User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'gestionnaire_id', referencedColumnName: 'id')]
    private User $id;
}
