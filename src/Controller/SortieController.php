<?php

namespace App\Controller;

use App\Form\SortieFilterForm;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use App\Services\SiteService;
use App\Services\SortiesService;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sortie', name: 'sortie_')]
class SortieController extends AbstractController
{
    /*/////// route 1 : la page de listing des sorties
    #[Route('', name: 'main')]
    public function sortieMain(SortiesService $SoSe, SiteService $SiSe): Response
    {
        $sitesList = $SiSe->showAll();        //délégation de la recherche au SiteService
        $sortieList = $SoSe->showActive();       //délégation de la recherche au SortieService
        $dateActuelle = new DateTime();

        return $this->render('sortie/main.html.twig', [
            'sortiesList' => $sortieList,
            'sitesList' => $sitesList,
            'dateActuelle' => $dateActuelle,
        ]);
    }*/
    ///////// route 1-1 : la partie filtre de la page des sorties
    #[Route('', name: 'main')]
    public function list(SiteService $SiSe, SortieRepository $SoRe, Request $request)
    {
        // Create the form
        $form = $this->createForm(SortieFilterForm::class);
        $form->handleRequest($request);

        // Initialize query builder
        $queryBuilder = $SoRe->createQueryBuilder('s');

        $queryBuilder->andWhere($queryBuilder->expr()->in('s.etat', [2, 3, 4, 6]));

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if ($data['site']) {
                $queryBuilder->andWhere('s.site = :site')
                    ->setParameter('site', $data['site']);
            }

            if (!empty($data['name'])) {
                $queryBuilder->andWhere('s.nom LIKE :name')
                    ->setParameter('name', '%' . $data['name'] . '%');
            }

            if ($data['startDate']) {
                $queryBuilder->andWhere('s.dateHeureDebut >= :startDate')
                    ->setParameter('startDate', $data['startDate']);
            }

            if ($data['endDate']) {
                $queryBuilder->andWhere('s.dateLimiteInscription <= :endDate')
                    ->setParameter('endDate', $data['endDate']);
            }

            if ($data['checkbox1']) {
                $queryBuilder->andWhere('s.option1 = true');
            }

            if ($data['checkbox2']) {
                $queryBuilder->andWhere('s.option2 = true');
            }

            if ($data['checkbox3']) {
                $queryBuilder->andWhere('s.option3 = true');
            }

            if ($data['checkbox4']) {
                $queryBuilder->andWhere('s.option4 = true');
            }
        }

        $sortieList = $queryBuilder->getQuery()->getResult();
        $sitesList = $SiSe->showAll();        //délégation de la recherche au SiteService
        $dateActuelle = new DateTime();

        return $this->render('sortie/main.html.twig', [
            'form' => $form->createView(),
            'sortiesList' => $sortieList,
            'sitesList' => $sitesList,
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
    #[Route('/archives', name: 'archives')]
    public function sortieArchives(SortiesService $SoSe, SiteService $SiSe): Response
    {
        $sitesList = $SiSe->showAll();        //délégation de la recherche au SiteService
        $sortieList = $SoSe->showOld();       //délégation de la recherche au SortieService
        $dateActuelle = new DateTime();

        return $this->render('sortie/archives.html.twig', [
            'sortiesList' => $sortieList,
            'sitesList' => $sitesList,
            'dateActuelle' => $dateActuelle,
        ]);
    }
}
