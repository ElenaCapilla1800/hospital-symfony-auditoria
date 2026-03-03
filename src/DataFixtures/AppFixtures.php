<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    // El constructor recibe el servicio de cifrado de contraseñas de Symfony
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        // --- 1. CREAR DOCTOR SIMI ---
        $doctor1 = new User();
        $doctor1->setEmail('doctor@test.com');
        $doctor1->setName('Dr. Simi');
        $doctor1->setRoles(['ROLE_DOCTOR']);
        $doctor1->setIsActive(true); // Cuenta activa

        // Ciframos la contraseña '123456' para este usuario
        $password1 = $this->hasher->hashPassword($doctor1, '123456');
        $doctor1->setPassword($password1);

        // Le decimos a Doctrine que "prepare" este objeto
        $manager->persist($doctor1);


        // --- 2. CREAR DRA. GARCÍA (Para probar el Voter) ---
        $doctor2 = new User();
        $doctor2->setEmail('garcia@test.com');
        $doctor2->setName('Dra. Garcia');
        $doctor2->setRoles(['ROLE_DOCTOR']);
        $doctor2->setIsActive(true);

        // Usamos la misma contraseña para no liarnos: '123456'
        $password2 = $this->hasher->hashPassword($doctor2, '123456');
        $doctor2->setPassword($password2);

        $manager->persist($doctor2);


        // --- 3. CREAR ADMINISTRADOR DEL HOSPITAL ---
        $admin = new User();
        $admin->setEmail('admin@hospital.com');
        $admin->setName('Admin General');
        $admin->setRoles(['ROLE_ADMIN']); // Rol con más poder
        $admin->setIsActive(true);

        $passwordAdmin = $this->hasher->hashPassword($admin, 'admin123');
        $admin->setPassword($passwordAdmin);

        $manager->persist($admin);


        // --- 4. GUARDAR TODO EN LA BASE DE DATOS ---
        // Flush ejecuta todas las consultas SQL pendientes de una sola vez
        $manager->flush();
    }
}