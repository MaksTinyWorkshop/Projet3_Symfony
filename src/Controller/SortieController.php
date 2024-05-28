<?php

namespace App\Controller;

use App\Services\SiteService;
use App\Services\SortiesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sortie', name: 'sortie_')]
class SortieController extends AbstractController
{
    #[Route('', name: 'main')]
    public function sortieMain(SortiesService $SoSe, SiteService $SiSe): Response
    {
        $sitesList = $SiSe->showAll();        //délégation de la recherche au SiteService
        $sortieList = $SoSe->showAll();       //délégation de la recherche au SortieService

        return $this->render('sortie/main.html.twig', [
            'sortieList' => $sortieList,
            'sitesList' => $sitesList
        ]);
    }
}
