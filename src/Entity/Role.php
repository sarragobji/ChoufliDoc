<?php

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    #[ORM\Column(name: "idRole", type: "integer")]
    private ?int $idRole = null;

    #[ORM\Column(length: 255)]
    private ?string $roleName = null;

    // Many Roles can be assigned to many Users
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: "userRoles")]
    private Collection $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getIdRole(): ?int
    {
        return $this->idRole;
    }

    public function getRoleName(): ?string
    {
        return $this->roleName;
    }

    public function setRoleName(string $roleName): static
    {
        $this->roleName = $roleName;
        return $this;
    }
}
