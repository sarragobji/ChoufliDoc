<?php

namespace App\Entity;

use App\Repository\AppointmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AppointmentRepository::class)]
class Appointment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $AppointmentId = null;

    #[ORM\Column(type: "date")]
    private ?\DateTimeInterface $AppointmentDate = null;

    #[ORM\Column(type: "time")]
    private ?\DateTimeInterface $AppointmentTime = null;

    // --- Getters & Setters ---

    public function getAppointmentId(): ?int
    {
        return $this->AppointmentId;
    }

    public function getAppointmentDate(): ?\DateTimeInterface
    {
        return $this->AppointmentDate;
    }

    public function setAppointmentDate(\DateTimeInterface $AppointmentDate): static
    {
        $this->AppointmentDate = $AppointmentDate;
        return $this;
    }

    public function getAppointmentTime(): ?\DateTimeInterface
    {
        return $this->AppointmentTime;
    }

    public function setAppointmentTime(\DateTimeInterface $AppointmentTime): static
    {
        $this->AppointmentTime = $AppointmentTime;
        return $this;
    }
}
