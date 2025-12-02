<?php

namespace App\Entity;

use App\Repository\MedicalRecordRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MedicalRecordRepository::class)]
class MedicalRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    #[ORM\Column(type: "integer")]
    private ?int $idMedRec = null;


    public function getIdMedRec(): ?int
    {
        return $this->idMedRec;
    }

}
