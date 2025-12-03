<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    #[ORM\Column(name: "id_user", type: "integer")] //désactiver l'auto naming par défaut
    private ?int $idUser = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;
    
    #[ORM\Column(unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 8)]
    private ?string $phoneNumber = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $birthDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;
    
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    // ---- CONSTRUCTOR ----
    public function __construct()
    {
        $this->userRoles = new ArrayCollection();
        $this->appointmentsTaken = new ArrayCollection();
        $this->appointmentsPassed = new ArrayCollection();
        $this->medicalRecordsConsulted = new ArrayCollection();
    }

    // ---- GETTERS / SETTERS ----
    public function getIdUser(): ?int
    {
        return $this->idUser;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTimeInterface $birthDate): static
    {
        $this->birthDate = $birthDate;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        return array_unique([...$this->roles, 'ROLE_USER']);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    // ---- RELATIONS ----
    
    // Many Users can have many Roles
    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: "users")]
    #[ORM\JoinTable(name: "user_roles")]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id_user")]
    #[ORM\InverseJoinColumn(name: "role_id", referencedColumnName: "idRole")]
    private Collection $userRoles;

    // One User can book many Appointments (as Patient)
    #[ORM\OneToMany(mappedBy: "patient", targetEntity: Appointment::class)]
    private Collection $appointmentsTaken;

    // One User can have many Appointments (as Doctor)
    #[ORM\OneToMany(mappedBy: "doctor", targetEntity: Appointment::class)]
    private Collection $appointmentsPassed;

    // One User can consult many MedicalRecords
    #[ORM\OneToMany(mappedBy: "consultedBy", targetEntity: MedicalRecord::class)]
    private Collection $medicalRecordsConsulted;
}
