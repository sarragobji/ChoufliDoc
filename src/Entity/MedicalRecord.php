<?php

namespace App\Entity;

use App\Repository\MedicalRecordRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MedicalRecordRepository::class)]
class MedicalRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idMedRec = null;

    public function getIdMedRec(): ?int
    {
        return $this->idMedRec;
    }

}
