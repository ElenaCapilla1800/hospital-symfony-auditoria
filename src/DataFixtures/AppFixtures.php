<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\MedicalRecord;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        // --- 1. CREAR DOCTOR SIMI ---
        $doctor1 = new User();
        $doctor1->setEmail('doctor@test.com');
        $doctor1->setName('Dr. Simi');
        $doctor1->setRoles(['ROLE_DOCTOR']);
        $doctor1->setIsActive(true);

        $password1 = $this->hasher->hashPassword($doctor1, '123456');
        $doctor1->setPassword($password1);

        $manager->persist($doctor1);

        // --- 2. CREAR DRA. GARCÍA (para probar el Voter) ---
        $doctor2 = new User();
        $doctor2->setEmail('garcia@test.com');
        $doctor2->setName('Dra. García');
        $doctor2->setRoles(['ROLE_DOCTOR']);
        $doctor2->setIsActive(true);

        $password2 = $this->hasher->hashPassword($doctor2, '123456');
        $doctor2->setPassword($password2);

        $manager->persist($doctor2);

        // --- 3. CREAR ADMINISTRADOR DEL HOSPITAL ---
        $admin = new User();
        $admin->setEmail('admin@hospital.com');
        $admin->setName('Admin General');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setIsActive(true);

        $passwordAdmin = $this->hasher->hashPassword($admin, 'admin123');
        $admin->setPassword($passwordAdmin);

        $manager->persist($admin);

        // --- 4. HISTORIALES MÉDICOS ---
        // Historial del Dr. Simi — necesario para que el test de acceso
        // tenga un registro "ajeno" cuando se loguea la Dra. García.
        $record1 = new MedicalRecord();
        $record1->setPatientName('Juan Pérez');
        $record1->setDiagnosis('Hipertensión leve');
        $record1->setTreatment('Control de dieta y seguimiento mensual');
        $record1->setDoctor($doctor1);

        $manager->persist($record1);

        // Historial propio de la Dra. García (para probar que SÍ puede
        // acceder a sus propios registros, si en el futuro añades ese test).
        $record2 = new MedicalRecord();
        $record2->setPatientName('Marta Ruiz');
        $record2->setDiagnosis('Gripe estacional');
        $record2->setTreatment('Reposo y paracetamol 650mg cada 8h');
        $record2->setDoctor($doctor2);

        $manager->persist($record2);

        // --- 5. GUARDAR TODO EN LA BASE DE DATOS ---
        $manager->flush();
    }
}