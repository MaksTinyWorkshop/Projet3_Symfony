<?php

namespace App\EventListener;

use App\Entity\Participants;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;


class CheckActiveUserListener {
    public function onSecurityAuthenticationSuccess(AuthenticationEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();

        if ($user instanceof Participants && !$user->isActif()) {
            // L'utilisateur n'est pas actif, rejeter l'authentification
            throw new CustomUserMessageAuthenticationException('Votre compte est désactivé, veuillez contacter un administrateur');
        }
    }
}