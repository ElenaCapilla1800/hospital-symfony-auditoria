<?php

namespace App\Controller\Admin;

use App\Entity\AccessLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractController
{
    /**
     * TAREA 4 - PUNTO 18 y 19: Registro de Auditoría General
     * Permite ver TODOS los movimientos y filtrar por email, acción o fecha.
     */
    #[Route('/admin/logs', name: 'app_admin_logs')]
    public function viewLogs(Request $request, EntityManagerInterface $em): Response
    {
        // 1. Capturamos los parámetros de búsqueda que vienen por la URL (GET)
        $email = $request->query->get('email');
        $action = $request->query->get('action');
        $date = $request->query->get('date');

        // 2. Iniciamos el QueryBuilder sobre la entidad AccessLog
        $repo = $em->getRepository(AccessLog::class);
        $queryBuilder = $repo->createQueryBuilder('l')
            ->leftJoin('l.user', 'u') // Unimos con la tabla de usuarios
            ->addSelect('u')          // Cargamos los datos del usuario para evitar 50 consultas extra
            ->orderBy('l.createdAt', 'DESC');

        // 3. Filtro por Email (si el usuario ha escrito algo en el buscador)
        if ($email) {
            $queryBuilder->andWhere('u.email LIKE :email')
                        ->setParameter('email', '%' . $email . '%');
        }

        // 4. Filtro por Acción (RECORD_VIEW, RECORD_EDIT, etc.)
        if ($action) {
            $queryBuilder->andWhere('l.action = :action')
                        ->setParameter('action', $action);
        }

        // 5. Filtro por Fecha (Punto 19: Búsqueda cronológica)
        if ($date) {
            try {
                // Creamos el rango de inicio (00:00:00) y fin (23:59:59) del día seleccionado
                $startDate = new \DateTime($date . ' 00:00:00');
                $endDate = new \DateTime($date . ' 23:59:59');
                
                $queryBuilder->andWhere('l.createdAt BETWEEN :start AND :end')
                            ->setParameter('start', $startDate)
                            ->setParameter('end', $endDate);
            } catch (\Exception $e) {
                // Si la fecha es inválida, simplemente ignoramos el filtro
            }
        }

        // 6. Renderizamos la plantilla de Auditoría General
        return $this->render('admin/logs.html.twig', [
            'logs' => $queryBuilder->getQuery()->getResult(),
            'currentEmail' => $email,
            'currentAction' => $action,
            'currentDate' => $date
        ]);
    }

    /**
     * TAREA 5 - PUNTO 24: Informe de accesos sospechosos
     * Muestra solo intentos DENEGADOS en las últimas 24 horas.
     */
    #[Route('/admin/suspicious', name: 'app_admin_suspicious')]
    public function suspiciousReport(EntityManagerInterface $em): Response
    {
        // 1. Calculamos el tiempo de hace 24 horas
        $since = new \DateTime('-24 hours');

        // 2. Ejecutamos la consulta filtrada
        $logs = $em->getRepository(AccessLog::class)->createQueryBuilder('l')
            // IMPORTANTE: Usamos 'l.granted' (la propiedad) y NO 'l.isGranted' (el método)
            ->where('l.granted = :granted') 
            ->andWhere('l.createdAt >= :since')
            ->setParameter('granted', false) // Solo queremos los que NO tienen permiso (denegados)
            ->setParameter('since', $since)
            ->orderBy('l.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        // 3. Renderizamos la plantilla específica de Sospechosos
        return $this->render('admin/suspicious.html.twig', [
            'logs' => $logs
        ]);
    }
}