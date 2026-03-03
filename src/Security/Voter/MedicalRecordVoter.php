<?php

namespace App\Security\Voter;

use App\Entity\MedicalRecord;
use App\Entity\User;
use App\Service\AuditService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MedicalRecordVoter extends Voter
{
    const VIEW = 'RECORD_VIEW';
    const EDIT = 'RECORD_EDIT';

    // Inyectamos el servicio para cumplir el Punto 17 (Auditoría)
    public function __construct(private AuditService $auditService) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT])
            && $subject instanceof MedicalRecord;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // Si no está logueado, acceso denegado fulminante
        if (!$user instanceof User) {
            return false;
        }

        /** @var MedicalRecord $record */
        $record = $subject;

        // 1. Lógica de decisión
        $isAllowed = match($attribute) {
            self::VIEW => $this->canView($record, $user),
            self::EDIT => $this->canEdit($record, $user),
            default => false,
        };

        // 2. AUDITORÍA PROACTIVA (Punto 17)
        // Registramos tanto los éxitos como los intentos de intrusión (cuando $isAllowed es false)
        $this->auditService->logAccess($user, $record, $attribute, $isAllowed);

        return $isAllowed;
    }

    /**
     * LÓGICA DE VISUALIZACIÓN
     * Un médico solo ve sus registros. Un admin ve todo.
     */
    private function canView(MedicalRecord $record, User $user): bool
    {
        // Si es Admin, tiene permiso total
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        // Si es Doctor, SOLO puede ver si él es el médico asignado
        return $user === $record->getDoctor();
    }

    /**
     * LÓGICA DE EDICIÓN
     * Solo el dueño del registro puede editarlo.
     */
    private function canEdit(MedicalRecord $record, User $user): bool
    {
        // Aplicamos la misma restricción estricta: solo el creador edita
        return $user === $record->getDoctor();
    }
}