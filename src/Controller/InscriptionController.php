<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/inscription', name: 'inscription_')]
class InscriptionController extends AbstractController
{
    #[Route('/add/{id}', name: 'add')]
    public function add(): Response
    {

        return $this->redirectToRoute('sortie_main');
    }
}
