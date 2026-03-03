<?php

namespace App\Controller\Api;

use App\Repository\MedicalRecordRepository;
use App\Service\AuditService; // 1. USAMOS EL SERVICIO CORRECTO
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[Route('/api')]
class MedicalRecordApiController extends AbstractController
{
    /**
     * LISTAR HISTORIALES (API)
     * Este endpoint devuelve los registros en formato JSON filtrados por médico.
     */
    #[Route('/records', name: 'api_records_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/records',
        summary: 'Obtener historiales médicos (JSON)',
        description: 'Devuelve un listado filtrado por el médico autenticado. Requiere token JWT.'
    )]
    #[OA\Response(response: 200, description: 'Lista devuelta correctamente')]
    #[OA\Response(response: 401, description: 'Token no válido')]

    public function list(
        MedicalRecordRepository $repo,
        AuditService $auditService // 2. INYECTAMOS EL SERVICIO DE AUDITORÍA
    ): JsonResponse
    {
        $user = $this->getUser();

        // 3. FILTRADO POR PRIVACIDAD (Igual que en la web)
        if ($this->isGranted('ROLE_ADMIN')) {
            $records = $repo->findAll();
        } else {
            $records = $repo->findBy(['doctor' => $user]);
        }

        // 4. BUCLE DE SERIALIZACIÓN Y AUDITORÍA POR REGISTRO
        $data = [];
        foreach ($records as $r) {
            // Registramos el acceso a cada registro individual en la auditoría (Punto 17)
            // Usamos logAccess(Usuario, Registro, Acción, Concedido)
            $auditService->logAccess($user, $r, 'API_VIEW', true);

            $data[] = [
                'id' => $r->getId(),
                'paciente' => $r->getPatientName(),
                'doctor' => $r->getDoctor() ? $r->getDoctor()->getUserIdentifier() : 'Sin médico',
                'diagnostico' => $r->getDiagnosis(),
                'tratamiento' => $r->getTreatment(),
            ];
        }

        // 5. RESPUESTA FINAL
        return $this->json($data);
    }
}