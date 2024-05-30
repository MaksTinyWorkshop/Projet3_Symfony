<?php

namespace App\Controller;

use App\Form\ResetPasswordRequestFormType;
use App\Services\ParticipantsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Classe de Routage qui gère la connexion/déconnexion et l'oubli du mot de passe
 * /login -> connexion
 * /logout -> déconnexion
 * /oubli-pass -> envoi de mail avec lien de réinitialisation
 * /oubli-pass/{token} -> check du token et réinitialisation mot de passe
 */
class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('oubli-pass', name: 'forgot_password')]
    public function forgotPassword(Request $request, ParticipantsService $participantsService): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $participant = $participantsService->forgotPassword($email);

            if ($participant) {
                $this->addFlash('success', 'Email envoyé avec succès');
            } else {
                $this->addFlash('danger', 'Un problème est survenu');
            }
            return $this->redirectToRoute('app_login');
        }
        return $this->render('security/reset_password_request.html.twig', ['requestPassForm' => $form->createView()]);
    }

    #[Route('/oubli-pass/{token}', name: 'reset_password')]
    public function resetPassword(string $token, Request $request, ParticipantsService $participantsService): Response
    {
        $result = $participantsService->resetPassword($token, $request);

        if ($result['success']) {
            $this->addFlash('success', 'Mot de passe modifié avec succès');
            return $this->redirectToRoute('app_login');
        }

        if ($result['form']) {
            return $this->render('security/reset_password.html.twig', [
                'passForm' => $result['form']->createView(),
            ]);
        }
        $this->addFlash('danger', 'Jeton invalide ou formulaire non valide');
        return $this->redirectToRoute('app_login');
    }
}
