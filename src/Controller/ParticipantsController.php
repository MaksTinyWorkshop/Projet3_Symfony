<?php

namespace App\Controller;


use App\Services\ParticipantsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Classe de routage qui gère les visibilités des users
 * A modifier dans le turFu
 * /participants/ -> Renvoie la liste de tout les participants
 *
 * /participants/{pseudo} -> Renvoi les infos du participant correspondant au pseudo -> Si c'est le User en session,
 * renvoi sur la page d'update de son profil
 *
 * /participants/{pseudo}/desinscription, bon ben voilà hein, on va pas épiloguer
 */

#[Route('/participants', name: 'participants_')]
class ParticipantsController extends AbstractController
{
    //////////////////////////// Constructeur pour l'injection de dépendances
    public function __construct(
        private ParticipantsService $participantsService,
        private Security            $security,
        private TokenStorageInterface       $tokenStorage
    ){}

    //////////////////// Routage et appel au service
    #[Route('/', name: 'list')]
    public function index(): Response
    {
        $list = $this->participantsService->getAll();
        return $this->render('participants/list.html.twig', compact('list'));
    }

    #[Route('/{pseudo}', name: 'details')]
    public function details(Request $request, string $pseudo): Response
    {
        // On checke si le User connecté est celui qui correspond au pseudo
        $participant = $this->security->getUser();
        $participantPseudo = $participant->getPseudo();

        if ($participantPseudo === $pseudo) {
            // Si oui, méthode d'update par le service
           return $this->participantsService->updateProfil($request, $participant);
        } else
            // Si non, méthode de consultation par le service
           return $this->participantsService->consultationProfil($pseudo); // Si non, simple consultation
    }

    #[Route('/{pseudo}/desinscription', name: 'delete')]
    public function delete(Request $request, string $pseudo): Response
    {
        $participant = $this->security->getUser();
        $participantPseudo = $participant->getPseudo();

        if ($participantPseudo === $pseudo) {
            $this->participantsService->deleteProfil($pseudo, $this->tokenStorage);
            return $this->redirectToRoute('participants_list');
        } else
            return $this->participantsService->consultationProfil($pseudo);
    }



}

