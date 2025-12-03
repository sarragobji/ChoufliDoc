<?php

namespace App\Entity;

use App\Repository\MedicalRecordRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MedicalRecordRepository::class)]
class MedicalRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    #[ORM\Column(name: "idMedRec", type: "integer")]
    private ?int $idMedRec = null;

    // Many MedicalRecords can be consulted by one User
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "medicalRecordsConsulted")]
    #[ORM\JoinColumn(name: "consulted_by_id", referencedColumnName: "id_user", nullable: false)]
    private ?User $consultedBy = null;

    public function getIdMedRec(): ?int
    {
        return $this->idMedRec;
    }

    public function getConsultedBy(): ?User
    {
        return $this->consultedBy;
    }

    public function setConsultedBy(User $user): static
    {
        $this->consultedBy = $user;
        return $this;
    }
}
