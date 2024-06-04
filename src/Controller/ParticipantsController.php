<?php

namespace App\Controller;


use App\Services\ParticipantsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/participants', name: 'participants_')]
class ParticipantsController extends AbstractController
{
    /////// Route 1 : renvoie la liste de tout les participants TODO : à voir si elle est conservée
    #[Route('/', name: 'list')]
    public function index(ParticipantsService $participantsService): Response
    {
        $list = $participantsService->getAll();
        return $this->render('participants/list.html.twig', compact('list'));
    }

    /////// Route 2 : renvoie les infos du participant correspondant au pseudo
    #[Route('/{pseudo}', name: 'details')]
    public function details(
        Request $request,
        string $pseudo,
        Security $security,
        ParticipantsService $participantsService
    ): Response
    {
        // On checke si le User connecté est celui qui correspond au pseudo
        $participant = $security->getUser();
        $participantPseudo = $participant->getPseudo();

        if ($participantPseudo === $pseudo) {
            // Si oui, méthode d'update par le service
           return $participantsService->updateProfil($request, $participant);
        } else
            // Si non, méthode de consultation par le service
           return $participantsService->consultationProfil($pseudo); // Si non, simple consultation
    }

    /////// Route 3 : suppression d'un participant
    #[Route('/{pseudo}/desinscription', name: 'delete')]
    public function delete(
        string $pseudo,
        Security $security,
        ParticipantsService $participantsService,
        TokenStorageInterface $tokenStorage
    ): Response
    {
        $participant = $security->getUser();
        $participantPseudo = $participant->getPseudo();

        if ($participantPseudo === $pseudo) {
            $participantsService->deleteProfil($pseudo, $tokenStorage);
            return $this->redirectToRoute('participants_list');
        } else
            return $participantsService->consultationProfil($pseudo);
    }
}

