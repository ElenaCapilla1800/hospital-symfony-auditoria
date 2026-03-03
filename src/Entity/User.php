<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $speciality = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    /**
     * NUEVOS CAMPOS PARA SEGURIDAD AVANZADA (Punto 22)
     */
    
    // Contador de intentos de login fallidos (empieza en 0)
    #[ORM\Column(options: ["default" => 0])]
    private ?int $failedAttempts = 0;

    // Estado de bloqueo de la cuenta
    #[ORM\Column(options: ["default" => false])]
    private ?bool $isBlocked = false;

    /**
     * @var Collection<int, MedicalRecord>
     */
    #[ORM\OneToMany(targetEntity: MedicalRecord::class, mappedBy: 'doctor')]
    private Collection $medicalRecords;

    public function __construct()
    {
        $this->medicalRecords = new ArrayCollection();
        // Inicializamos los valores por defecto en el constructor también
        $this->failedAttempts = 0;
        $this->isBlocked = false;
    }

    // ... Getters y Setters originales ...

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
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

    public function eraseCredentials(): void
    {
        // ...
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getSpeciality(): ?string
    {
        return $this->speciality;
    }

    public function setSpeciality(?string $speciality): static
    {
        $this->speciality = $speciality;
        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * MÉTODOS PARA EL CONTROL DE BLOQUEO (Punto 22)
     */

    public function getFailedAttempts(): int
    {
        return $this->failedAttempts ?? 0;
    }

    public function setFailedAttempts(int $failedAttempts): static
    {
        $this->failedAttempts = $failedAttempts;
        return $this;
    }

    public function isBlocked(): bool
    {
        return $this->isBlocked ?? false;
    }

    public function setIsBlocked(bool $isBlocked): static
    {
        $this->isBlocked = $isBlocked;
        return $this;
    }

    /**
     * @return Collection<int, MedicalRecord>
     */
    public function getMedicalRecords(): Collection
    {
        return $this->medicalRecords;
    }

    public function addMedicalRecord(MedicalRecord $medicalRecord): static
    {
        if (!$this->medicalRecords->contains($medicalRecord)) {
            $this->medicalRecords->add($medicalRecord);
            $medicalRecord->setDoctor($this);
        }
        return $this;
    }

    public function removeMedicalRecord(MedicalRecord $medicalRecord): static
    {
        if ($this->medicalRecords->removeElement($medicalRecord)) {
            if ($medicalRecord->getDoctor() === $this) {
                $medicalRecord->setDoctor(null);
            }
        }
        return $this;
    }
}