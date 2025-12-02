<?php

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $RoleId = null;

    #[ORM\Column(length: 50)]
    private ?string $RoleName = null;

    // --- Getters & Setters ---

    public function getRoleId(): ?int
    {
        return $this->RoleId;
    }

    public function getRoleName(): ?string
    {
        return $this->RoleName;
    }

    public function setRoleName(string $RoleName): static
    {
        $this->RoleName = $RoleName;
        return $this;
    }
}
