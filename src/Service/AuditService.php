<?php

namespace App\Service;

use App\Entity\AccessLog;
use App\Entity\MedicalRecord;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * SERVICIO DE AUDITORÍA (Punto 17)
 * Este servicio se encarga de dejar constancia de cada acceso a datos sensibles.
 */
class AuditService
{
    /**
     * Usamos el constructor para inyectar:
     * 1. EntityManager: Para guardar en la base de datos.
     * 2. RequestStack: Para obtener metadatos de la petición (como la IP).
     */
    public function __construct(
        private EntityManagerInterface $em,
        private RequestStack $requestStack
    ) {}

    /**
     * REGISTRO DE ACCESO
     * * @param User $user           El usuario (médico) que realiza la acción.
     * @param MedicalRecord $record El historial médico consultado/editado.
     * @param string $action       La etiqueta de la acción (VIEW, CREATE, EDIT, DELETE).
     * @param bool $granted        Si el acceso fue permitido (true) o denegado (false).
     */
    public function logAccess(User $user, MedicalRecord $record, string $action, bool $granted): void
    {
        // 1. Instanciamos la entidad de Log
        $log = new AccessLog();

        // 2. Vinculamos quién accedió y a qué registro accedió
        // Esto crea las relaciones Foreign Key en la base de datos
        $log->setUser($user);
        $log->setMedicalRecord($record);

        // 3. Guardamos el tipo de operación
        $log->setAction($action);

        // 4. Guardamos si el sistema le dio permiso o no (importante para detectar intrusos)
        $log->setGranted($granted);

        // 5. Capturamos la IP del cliente
        // Usamos el operador de navegación segura (?->) por si no hay una petición activa
        $clientIp = $this->requestStack->getCurrentRequest()?->getClientIp() ?? '127.0.0.1';
        $log->setIpAddress($clientIp);

        // 6. Seteamos la fecha y hora exacta del evento
        $log->setCreatedAt(new \DateTimeImmutable());

        // 7. Persistimos los cambios en la tabla 'access_log'
        try {
            $this->em->persist($log);
            $this->em->flush();
        } catch (\Exception $e) {
            // En producción podrías loguear el error de base de datos aquí
        }
    }
}