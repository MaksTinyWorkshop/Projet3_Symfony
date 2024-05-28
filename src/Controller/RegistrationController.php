<?php

namespace App\Controller;

use App\Entity\Participants;
use App\Form\RegistrationFormType;
use App\Services\ParticipantsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    public function __construct(private ParticipantsService $partService){}


    #[Route('/register', name: 'app_register')]
    public function register(Request $request): Response
    {
       return $this->partService->register($request);
    }

    #[Route('/verif/{token}', name: 'verify')]
    public function verify($token): Response
    {
        $jwtSecret = $this->getParameter('app.jwtsecret');
        //TODO :
        $isVerified = $this->userVerificationService->verifyUser($token, $jwtSecret);

        if ($isVerified) {
            $this->addFlash('success', 'Utilisateur activé!');
        } else {
            $this->addFlash('danger', 'Le token est invalide ou a expiré');
        }

        return $this->redirectToRoute('main');
    }

}
