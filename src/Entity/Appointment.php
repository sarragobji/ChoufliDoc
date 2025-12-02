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
    #[ORM\Column(type: "integer")]
    private ?int $idAppointment = null;


    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateAppointment = null;

    public function getIdAppointment(): ?int
    {
        return $this->idAppointment;
    }

    public function setIdAppointment(int $idAppointment): static
    {
        $this->idAppointment = $idAppointment;

        return $this;
    }

    public function getDateAppointment(): ?\DateTime
    {
        return $this->dateAppointment;
    }

    public function setDateAppointment(\DateTime $dateAppointment): static
    {
        $this->dateAppointment = $dateAppointment;

        return $this;
    }
}
