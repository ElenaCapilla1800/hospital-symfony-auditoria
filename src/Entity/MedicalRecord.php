<?php

namespace App\Entity;

use App\Repository\MedicalRecordRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups; // IMPORTANTE: Añade esta línea

#[ORM\Entity(repositoryClass: MedicalRecordRepository::class)]
class MedicalRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['record:read'])] // Etiqueta para que se vea en la API
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['record:read'])] // Etiqueta para que se vea en la API
    private ?string $patientName = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['record:read'])]
    private ?string $diagnosis = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['record:read'])]
    private ?string $treatment = null;

    #[ORM\ManyToOne(inversedBy: 'medicalRecords')]
    #[ORM\JoinColumn(nullable: false)]
    // No solemos poner Groups aquí para evitar bucles infinitos con el usuario
    private ?User $doctor = null;

    // --- MÉTODOS ---

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPatientName(): ?string
    {
        return $this->patientName;
    }

    public function setPatientName(string $patientName): static
    {
        $this->patientName = $patientName;
        return $this;
    }

    // He añadido este método "alias" para evitar que la API falle si busca "getPatient"
    public function getPatient(): ?string
    {
        return $this->getPatientName();
    }

    public function getDiagnosis(): ?string
    {
        return $this->diagnosis;
    }

    public function setDiagnosis(string $diagnosis): static
    {
        $this->diagnosis = $diagnosis;
        return $this;
    }

    public function getTreatment(): ?string
    {
        return $this->treatment;
    }

    public function setTreatment(string $treatment): static
    {
        $this->treatment = $treatment;
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