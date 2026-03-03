<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;

class LoginFailureSubscriber implements EventSubscriberInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public static function getSubscribedEvents(): array
    {
        return [
            LoginFailureEvent::class => 'onLoginFailure',
        ];
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $passport = $event->getPassport();
        if (!$passport) return;

        $user = $passport->getUser();
        if (!$user instanceof User) return;

        // Incrementamos el contador de fallos
        $user->setFailedAttempts($user->getFailedAttempts() + 1);

        // Si llega a 5, bloqueamos (Punto 22)
        if ($user->getFailedAttempts() >= 5) {
            $user->setBlocked(true);
        }

        $this->em->flush();
    }
}