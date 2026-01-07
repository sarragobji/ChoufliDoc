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

    // Patient concerned by the record
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(
        name: 'patient_id',
        referencedColumnName: 'id_user',
        nullable: false
    )]
    private ?User $patient = null;

    // Who created it (Dalanda or Slimen)
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(
        name: 'created_by_id',
        referencedColumnName: 'id_user',
        nullable: false
    )]
    private ?User $createdBy = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    // BASIC INFO (secretary + doctor)
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reason = null;

    // MEDICAL INFO (doctor only)
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $diagnosis = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $analysis = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $prescription = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getIdMedRec(): ?int
    {
        return $this->idMedRec;
    }

    public function getPatient(): ?User
    {
        return $this->patient;
    }

    public function setPatient(User $patient): self
    {
        $this->patient = $patient;
        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(User $createdBy): self
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): self
    {
        $this->reason = $reason;
        return $this;
    }

    public function getDiagnosis(): ?string
    {
        return $this->diagnosis;
    }

    public function setDiagnosis(?string $diagnosis): self
    {
        $this->diagnosis = $diagnosis;
        return $this;
    }

    public function getAnalysis(): ?string
    {
        return $this->analysis;
    }

    public function setAnalysis(?string $analysis): self
    {
        $this->analysis = $analysis;
        return $this;
    }

    public function getPrescription(): ?string
    {
        return $this->prescription;
    }

    public function setPrescription(?string $prescription): self
    {
        $this->prescription = $prescription;
        return $this;
    }
}
