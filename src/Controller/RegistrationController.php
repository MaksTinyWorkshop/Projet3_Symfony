<?php

namespace App\Controller;

use App\Services\ParticipantsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;


class RegistrationController extends AbstractController
{
    /////// Route 1 : S'enregistrer
    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/register', name: 'app_register')]
    public function register(Request $request,ParticipantsService $partService): Response
    {
       return $partService->register($request);
    }

    /* Routes désactivées (User automatiquement actif)

    /////// Route 2 : vérifiaction du token d'activation de compte
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

    /////// Route 3 : Renvoyer un email de verification
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
    */
}
