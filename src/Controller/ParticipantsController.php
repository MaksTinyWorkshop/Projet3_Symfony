<?php

namespace App\Controller;

use App\Entity\Participants;
use App\Services\ParticipantsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/participants', name: 'participants_')]
class ParticipantsController extends AbstractController
{
    public function __construct(private ParticipantsService $participantsService) {}

    #[Route('/', name: 'list', methods: ['GET'])]
    public function index(){
        $list = $this->participantsService->getAll();
        return $this->render('participants/list.html.twig', compact('list'));
    }


    #[Route('/{id}', name: 'details')]
    public function details(Participants $participant): Response
    {
        return $this->render('participants/details.html.twig', compact('participant'));
    }
}
