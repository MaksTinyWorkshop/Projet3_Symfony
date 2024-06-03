<?php

namespace App\Controller;

use App\Form\SortieFilterForm;
use App\Repository\SortieRepository;
use App\Services\InscriptionsService;
use App\Services\SiteService;
use App\Services\SortiesService;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sortie', name: 'sortie_')]
class SortieController extends AbstractController
{

    ///////// route 1 : la partie filtre de la page des sorties
    #[Route('', name: 'main')]
    public function list(SiteService $SiSe, SortiesService $SoSe, InscriptionsService $insServ, Request $request)
    {
        //To Do : faire une vérification à l'ouverture de cette route : checker les dates et les status des events
        // faire les bascules en conséquence
        $form = $this->createForm(SortieFilterForm::class);
        $form->handleRequest($request);

        $sortieList = $SoSe->makeFilter($form);
        $sitesList = $SiSe->showAll();        //délégation de la recherche au SiteService
        $inscritsList = $insServ->showAll();  //délégation de la recherche au InscriptionService
        $dateActuelle = new DateTime();

        return $this->render('sortie/main.html.twig', [
            'form' => $form->createView(),
            'sortiesList' => $sortieList,
            'sitesList' => $sitesList,
            'inscriptionsList' => $inscritsList,
            'dateActuelle' => $dateActuelle,
        ]);
    }

    ///////// route 2 : la page détail d'une sortie
    #[Route('/detail/{id}', name: 'detail')]
    public function sortieDetail(int $id, SortieRepository $SoRep): Response
    {
       $sortie = $SoRep->find($id);         //affichage de la sortie selon le ID passé

       return $this->render('sortie/detail.html.twig', ['sortie' => $sortie ]);
    }

    ///////// route 3 : les archives de sorties
    #[Route('/public', name: 'public')]
    public function sortieArchives(SiteService $SiSe, SortiesService $SoSe, InscriptionsService $insServ, Request $request): Response
    {
        $form = $this->createForm(SortieFilterForm::class);
        $form->handleRequest($request);

        $sortieList = $SoSe->makeFilter($form);
        $sitesList = $SiSe->showAll();        //délégation de la recherche au SiteService
        $inscritsList = $insServ->showAll();  //délégation de la recherche au InscriptionService
        $dateActuelle = new DateTime();


        return $this->render('sortie/public.html.twig', [
            'form' => $form->createView(),
            'sortiesList' => $sortieList,
            'sitesList' => $sitesList,
            'inscriptionsList' => $inscritsList,
            'dateActuelle' => $dateActuelle,
        ]);
    }

    ///////// route 4 : le changement d'état
    #[Route('/state/{sortieId}/{state}', name: 'state')]
    public function changeState(int $sortieId, int $state, SortiesService $SoSe): Response
    {
        $SoSe->changeState($sortieId, $state);         //affichage de la sortie selon le ID passé

        return $this->redirectToRoute('sortie_main');
    }

    ///////// route 5 : la suppression
    #[Route('/delete/{sortieId}', name: 'delete')]
    public function delete(int $sortieId, SortiesService $SoSe): Response
    {
        $SoSe->delete($sortieId);         //affichage de la sortie selon le ID passé

        return $this->redirectToRoute('sortie_main');
    }


    /////// route 4 : création d'une sortie
    #[Route('/creer', name: 'creer')]
    public function creerUneSortie(Request $request, SortiesService $sortiesService): Response
    {
        return $sortiesService->creerSortie($request);
    }

    /////// route 5 : sorties du user en session
    #[Route('/{pseudo}', name: 'mes_sorties')]
    public function mesSortiesList(string $pseudo, SortieRepository $sortieRepository): Response
    {
        $user = $this->getUser();
        if ($user && $user->getPseudo() === $pseudo) {
            $sortiesList = $sortieRepository->findBy(['organisateur'=> $user]);
            return $this->render('sortie/mes_sorties.html.twig', [
                'sortiesList' => $sortiesList,
            ]);
        }
        return $this->redirectToRoute('sortie_main');
    }

    /////// route 6 :modifier une sortie
    #[Route('/{pseudo}/modifier/{id}', name: 'modifier')]
    public function modifierUneSortie(Request $request,string $pseudo, string $id, SortiesService $sortiesService): Response
    {
        $user = $this->getUser();
        if ($user && $user->getPseudo() === $pseudo) {
            return $sortiesService->modifierUneSortie($request, $id);
        }
        return $this->redirectToRoute('sortie_main');
    }


}
