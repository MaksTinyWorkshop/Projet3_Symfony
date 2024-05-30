<?php

namespace App\Controller;

use App\Services\ParticipantsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controleur qui gère le routage des Enregistrements User
 * /register -> S'enregistrer
 * /verif/{token} -> Validation du compte via envoi d'email après inscription (Lien envoyé par mail)
 * /renvoiVerif -> Renvoi du mail de confirmation
 */

class RegistrationController extends AbstractController
{
    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/register', name: 'app_register')]
    public function register(Request $request,ParticipantsService $partService): Response
    {
       return $partService->register($request);
    }

    #[Route('/verif/{token}', name: 'verify')]
    public function verify($token,ParticipantsService $partService): Response
    {
        $jwtSecret = $this->getParameter('app.jwtsecret');
        $isVerified = $partService->verify($token, $jwtSecret);

        if ($isVerified) {
            $this->addFlash('success', 'Utilisateur activé!');
        } else {
            $this->addFlash('danger', 'Le token est invalide ou a expiré');
        }

        return $this->redirectToRoute('main');
    }

    #[Route('/renvoiVerif', name: 'resend_verif')]
    public function resendVerif(ParticipantsService $partService): Response
    {
        $participant = $this->getUser();
        if(!$participant){
            $this->addFlash('danger', 'Vous devez être connecté pour accéder à cette page');
            return $this->redirectToRoute('app_login');
        }
        if ($participant->isActif()) {
            $this->addFlash('warning', 'Vous êtes déjà activé');
            return $this->redirectToRoute('app_login');
        }
        $jwtSecret = $this->getParameter('app.jwtsecret');

        $partService->resendVerif($participant, $jwtSecret);

        $this->addFlash('success', 'Email de vérification envoyé');
        return $this->redirectToRoute('main');
    }
}
