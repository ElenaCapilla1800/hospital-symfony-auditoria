<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    /**
     * Este método se ejecuta ANTES de que Symfony compruebe la contraseña.
     * Es ideal para bloquear usuarios que el Administrador ha desactivado manualmente.
     */
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        // REGLA 1 (Original): Si el Administrador ha marcado isActive como falso.
        if (!$user->isActive()) {
            throw new CustomUserMessageAccountStatusException(
                'Tu cuenta está desactivada por el administrador. Contacta con RR.HH.'
            );
        }
    }

    /**
     * Este método se ejecuta DESPUÉS de que Symfony verifique que la contraseña es correcta.
     * Aquí comprobamos el bloqueo automático por seguridad (Punto 22).
     */
    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        // REGLA 2 (Nueva - Punto 22): Bloqueo por 5 intentos fallidos.
        // Aunque el usuario ponga la contraseña BIEN ahora, si su campo 'isBlocked' 
        // es true (porque falló 5 veces antes), no le dejamos pasar.
        if ($user->isBlocked()) {
            throw new CustomUserMessageAccountStatusException(
                'Cuenta bloqueada por seguridad tras 5 intentos fallidos. Contacta con Soporte Técnico.'
            );
        }
    }
}