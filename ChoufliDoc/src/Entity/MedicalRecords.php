<?php

namespace App\Entity;

use App\Repository\MedicalRecordsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MedicalRecordsRepository::class)]
class MedicalRecords
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $RecordId = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $history = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $notes = null;

    // --- Getters & Setters ---

    public function getRecordId(): ?int
    {
        return $this->RecordId;
    }

    public function getHistory(): ?string
    {
        return $this->history;
    }

    public function setHistory(?string $history): static
    {
        $this->history = $history;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }
}
