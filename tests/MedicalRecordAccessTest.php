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

        // 1. Buscamos a la Dra. García usando el email exacto de tus Fixtures
        $doctor = $userRepository->findOneBy(['email' => 'garcia@test.com']);

        if (!$doctor) {
            $this->markTestSkipped('No se encontró a garcia@test.com. Revisa si cargaste los fixtures en --env=test');
        }

        // 2. Buscamos un historial que NO sea de la Dra. García
        // (Por ejemplo, uno que pertenezca al Dr. Simi)
        $otherRecord = $recordRepository->createQueryBuilder('r')
            ->where('r.doctor != :doctor')
            ->setParameter('doctor', $doctor)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$otherRecord) {
            $this->markTestSkipped('No hay registros de otros doctores para probar.');
        }

        // 3. Logueamos a la Dra. García e intentamos EDITAR el registro ajeno
        $client->loginUser($doctor);

        // La URL debe coincidir con tu ruta (normalmente /medical/record/{id}/edit)
        $client->request('GET', '/medical/record/' . $otherRecord->getId() . '/edit');

        // 4. ASSERT: Si el Voter funciona, debe devolver un 403 (Forbidden)
        $this->assertResponseStatusCodeSame(403);
    }
}