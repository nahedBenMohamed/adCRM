<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\FormationUserRepository;

#[ORM\Entity(repositoryClass: FormationUserRepository::class)]
class FormationUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: "Formation")]
    #[ORM\JoinColumn(name: "formation_id", referencedColumnName: "id")]
    private $formation;

    #[ORM\ManyToOne(targetEntity: "User")]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id")]
    private $user;

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

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }
}
