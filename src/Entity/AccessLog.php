<?php

namespace App\Entity;

use App\Repository\AccessLogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccessLogRepository::class)]
class AccessLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Relación con el usuario que intenta el acceso
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    // Relación con el historial médico al que se intenta acceder
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?MedicalRecord $medicalRecord = null;

    // Acción realizada (ej: RECORD_VIEW o RECORD_EDIT)
    #[ORM\Column(length: 50)]
    private ?string $action = null;

    // Dirección IP desde la que se conecta el usuario
    #[ORM\Column(length: 45)]
    private ?string $ipAddress = null;

    // Fecha y hora exacta del intento
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * CAMPO NUEVO (Punto 14):
     * Indica si el acceso fue permitido (true) o denegado (false).
     */
    #[ORM\Column]
    private ?bool $granted = null;

    // --- GETTERS Y SETTERS ---

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getMedicalRecord(): ?MedicalRecord
    {
        return $this->medicalRecord;
    }

    public function setMedicalRecord(?MedicalRecord $medicalRecord): static
    {
        $this->medicalRecord = $medicalRecord;
        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): static
    {
        $this->action = $action;
        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Métodos para el nuevo campo 'granted'
     */
    public function isGranted(): ?bool
    {
        return $this->granted;
    }

    public function setGranted(bool $granted): static
    {
        $this->granted = $granted;
        return $this;
    }
}