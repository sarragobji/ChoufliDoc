<?php

namespace App\Entity;

use App\Repository\AppointmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;

#[ORM\Entity(repositoryClass: AppointmentRepository::class)]
class Appointment
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    #[ORM\Column(name: "idAppointment", type: "integer")]
    private ?int $idAppointment = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateAppointment = null;

    // Relations
    // Many Appointments can be booked by one User (as Patient)
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "appointmentsTaken")]
    #[ORM\JoinColumn(name: "patient_id", referencedColumnName: "id_user", nullable: false)]
    private ?User $patient = null;

    // Many Appointments can be assigned to one User (as Doctor)
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "appointmentsPassed")]
    #[ORM\JoinColumn(name: "doctor_id", referencedColumnName: "id_user", nullable: false)]
    private ?User $doctor = null;

    public const APPOINTMENT_STATUS_PENDING = 'pending';
    public const APPOINTMENT_STATUS_CONFIRMED = 'accepted';
    public const APPOINTMENT_STATUS_CANCELLED = 'rejected';

    #[ORM\Column(length: 20)]
    private string $status = self::APPOINTMENT_STATUS_PENDING; // Default status

    public function getStatus(): string
    {
        return $this->status;
    }
    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }
    public function getIdAppointment(): ?int
    {
        return $this->idAppointment;
    }

    public function getDateAppointment(): ?\DateTimeInterface
    {
        return $this->dateAppointment;
    }

    public function setDateAppointment(\DateTimeInterface $dateAppointment): static
    {
        $this->dateAppointment = $dateAppointment;
        return $this;
    }

    public function getPatient(): ?User
    {
        return $this->patient;
    }

    public function setPatient(?User $patient): static
    {
        $this->patient = $patient;
        return $this;
    }

    public function getDoctor(): ?User
    {
        return $this->doctor;
    }

    public function setDoctor(?User $doctor): static
    {
        $this->doctor = $doctor;
        return $this;
    }
}
