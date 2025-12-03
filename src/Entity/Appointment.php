<?php

namespace App\Entity;

use App\Repository\AppointmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

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
}
