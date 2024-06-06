<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuType;
use App\Services\SortiesService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LieuController extends AbstractController
{


    #[Route('/add', name: 'lieu_add')]
    public function add( Request $request, EntityManagerInterface $entityManager): Response
    {
        $lieu = new Lieu();
        $form = $this->createForm(LieuType::class, $lieu);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($lieu);
            $entityManager->flush();

            $this->addFlash('success', 'Lieu ajouté avec succès!');


            return $this->redirectToRoute('sortie_creer');
        }

        return $this->render('lieu/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    //route en cas de modification
    #[Route('/addToModif/{sortieId}/{pseudo}', name: 'lieu_addToModif')]
    public function addToModif(int $sortieId, string $pseudo, Request $request, EntityManagerInterface $entityManager, SortiesService $sortiesService, Security $secu): Response
    {
        $user = $secu->getUser()->getId();

        $lieu = new Lieu();
        $form = $this->createForm(LieuType::class, $lieu);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($lieu);
            $entityManager->flush();

            $this->addFlash('success', 'Lieu ajouté avec succès!');

            return $this->redirectToRoute('sortie_modifier', ['pseudo' => $pseudo, 'sortieId' => $sortieId]);
        }
        return $this->render('lieu/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}