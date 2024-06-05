<?php

namespace App\Controller;

use App\Form\SortieFilterForm;
use App\Services\InscriptionsService;
use App\Services\SiteService;
use App\Services\SortiesService;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{

    #[Route('/', name: 'main')]
    public function mainIndex(SiteService $SiSe, SortiesService $SoSe, InscriptionsService $insServ, Request $request): Response
    {
        $dateTime = new DateTime();
        $SoSe->checkStatus($dateTime);

        $form = $this->createForm(SortieFilterForm::class);
        $form->handleRequest($request);

        $sortieList = $SoSe->makeFilter($form);
        $sitesList = $SiSe->showAll();        //délégation de la recherche au SiteService
        $inscritsList = $insServ->showAll();  //délégation de la recherche au InscriptionService


        return $this->render('main/accueil.html.twig', [
            'form' => $form->createView(),
            'sortiesList' => $sortieList,
            'sitesList' => $sitesList,
            'inscriptionsList' => $inscritsList,
            'dateActuelle' => $dateTime,
        ]);
    }
}
