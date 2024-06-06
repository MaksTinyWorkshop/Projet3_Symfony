<?php

namespace App\Controller;

use App\Repository\InscriptionsRepository;
use App\Services\InscriptionsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/inscription', name: 'inscription_')]
class InscriptionController extends AbstractController
{
    #[Route('/remove/{sortieId}', name: 'remove')]
    public function remove(int $sortieId, InscriptionsService $inscServ): Response
    {
        $inscServ->deleteOne($sortieId);

        return $this->redirectToRoute('sortie_main');
    }
    #[Route('/add/{sortieId}', name: 'add')]
    public function add(int $sortieId, InscriptionsService $inscServ): Response
    {
        //To Do ajouter le service de vérification
        $inscServ->addOne($sortieId);

        return $this->redirectToRoute('sortie_main');
    }
}
