<?php

namespace App\Tests;

use App\Repository\UserRepository;
use App\Repository\MedicalRecordRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MedicalRecordAccessTest extends WebTestCase
{
    public function testDoctorCannotEditOtherDoctorsRecord(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $userRepository = $container->get(UserRepository::class);
        $recordRepository = $container->get(MedicalRecordRepository::class);

        $doctor = $userRepository->findOneBy(['email' => 'garcia@test.com']);
        if (!$doctor) {
            $this->markTestSkipped('No se encontró a garcia@test.com. Revisa si cargaste los fixtures en --env=test');
        }

        $otherRecord = $recordRepository->createQueryBuilder('r')
            ->where('r.doctor != :doctor')
            ->setParameter('doctor', $doctor)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$otherRecord) {
            $this->markTestSkipped('No hay registros de otros doctores para probar.');
        }

        $client->loginUser($doctor);
        $client->request('GET', '/medical/record/' . $otherRecord->getId() . '/edit');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testDoctorCanEditOwnRecord(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $userRepository = $container->get(UserRepository::class);
        $recordRepository = $container->get(MedicalRecordRepository::class);

        $doctor = $userRepository->findOneBy(['email' => 'garcia@test.com']);
        if (!$doctor) {
            $this->markTestSkipped('No se encontró a garcia@test.com.');
        }

        $ownRecord = $recordRepository->findOneBy(['doctor' => $doctor]);
        if (!$ownRecord) {
            $this->markTestSkipped('La Dra. García no tiene ningún historial propio en las fixtures.');
        }

        $client->loginUser($doctor);
        $client->request('GET', '/medical/record/' . $ownRecord->getId() . '/edit');

        $this->assertResponseIsSuccessful();
    }

    public function testAdminCanViewAnyRecord(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $userRepository = $container->get(UserRepository::class);
        $recordRepository = $container->get(MedicalRecordRepository::class);

        $admin = $userRepository->findOneBy(['email' => 'admin@hospital.com']);
        if (!$admin) {
            $this->markTestSkipped('No se encontró a admin@hospital.com.');
        }

        $anyRecord = $recordRepository->findOneBy([]);
        if (!$anyRecord) {
            $this->markTestSkipped('No hay ningún historial en las fixtures.');
        }

        $client->loginUser($admin);
        $client->request('GET', '/medical/record/' . $anyRecord->getId());

        $this->assertResponseIsSuccessful();
    }

    /**
     * El Voter da permiso de VIEW total al admin, pero NO de EDIT sobre
     * historiales ajenos (canEdit no tiene excepción para ROLE_ADMIN).
     * Este test documenta esa asimetría intencionada del diseño.
     */
    public function testAdminCannotEditOtherDoctorsRecord(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $userRepository = $container->get(UserRepository::class);
        $recordRepository = $container->get(MedicalRecordRepository::class);

        $admin = $userRepository->findOneBy(['email' => 'admin@hospital.com']);
        $doctor = $userRepository->findOneBy(['email' => 'doctor@test.com']);
        if (!$admin || !$doctor) {
            $this->markTestSkipped('Faltan usuarios admin o doctor en las fixtures.');
        }

        $record = $recordRepository->findOneBy(['doctor' => $doctor]);
        if (!$record) {
            $this->markTestSkipped('El Dr. Simi no tiene ningún historial en las fixtures.');
        }

        $client->loginUser($admin);
        $client->request('GET', '/medical/record/' . $record->getId() . '/edit');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testUnauthenticatedUserCannotAccessRecord(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $recordRepository = $container->get(MedicalRecordRepository::class);
        $anyRecord = $recordRepository->findOneBy([]);
        if (!$anyRecord) {
            $this->markTestSkipped('No hay ningún historial en las fixtures.');
        }

        // Sin login: el firewall debe redirigir a /login, no dar 200.
        $client->request('GET', '/medical/record/' . $anyRecord->getId());

        $this->assertResponseRedirects();
    }
}