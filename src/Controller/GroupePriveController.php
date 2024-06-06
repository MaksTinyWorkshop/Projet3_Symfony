<?php

namespace App\Controller;

use App\Entity\GroupePrive;
use App\Entity\Sortie;
use App\Form\CreaSortieFormType;
use App\Form\GroupePriveType;
use App\Repository\GroupePriveRepository;
use App\Services\InscriptionsService;
use App\Services\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GroupePriveController extends AbstractController
{
    #[Route('/groupes-prives', name: 'groupe_prive_index')]
    public function index(GroupePriveRepository $groupePriveRepository): Response
    {
        $groupes = $groupePriveRepository->findAll();

        return $this->render('groupe_prive/index.html.twig', [
            'groupes' => $groupes,
        ]);
    }

    #[Route('/groupe-prive/{id}', name: 'groupe_prive_show', requirements: ['id' => '\d+'])]
    public function show(GroupePrive $groupePrive): Response
    {
        return $this->render('groupe_prive/show.html.twig', [
            'groupe' => $groupePrive,
        ]);
    }

    #[Route('/groupe-prive/{id}/creer-evenement', name: 'groupe_prive_creer_evenement', requirements: ['id' => '\d+'])]
    public function creerEvenement(
        Request $request,
        GroupePrive $groupePrive,
        EntityManagerInterface $entityManager,
        InscriptionsService $inscriptionsService,
        SendMailService $sendMailService
    ): Response {
        $sortie = new Sortie();
        $form = $this->createForm(CreaSortieFormType::class, $sortie, [
            'is_private' => true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sortie->setOrganisateur($this->getUser());
            $sortie->setGroupePrive($groupePrive);

            foreach ($groupePrive->getParticipants() as $participant) {
                if ($inscriptionsService->hasEventOnDate($participant, $sortie->getDateHeureDebut())) {
                    $this->addFlash('danger', "L'utilisateur {$participant->getPseudo()} est déjà inscrit à un autre événement ce jour-là.");
                    return $this->redirectToRoute('groupe_prive_show', ['id' => $groupePrive->getId()]);
                }
            }

            $entityManager->persist($sortie);
            $entityManager->flush();

            foreach ($groupePrive->getParticipants() as $participant) {
                $sendMailService->sendMail(
                    'no-reply@sortir.com',
                    $participant->getEmail(),
                    'Nouvel Événement Créé',
                    'emails/nouvel_evenement.html.twig',
                    [
                        'sortie' => $sortie,
                        'participant' => $participant,
                    ]
                );
            }

            return $this->redirectToRoute('groupe_prive_show', ['id' => $groupePrive->getId()]);
        }

        return $this->render('groupe_prive/evenement/creer.html.twig', [
            'form' => $form->createView(),
            'groupePrive' => $groupePrive,
        ]);
    }

    #[Route('/groupes-prives/creer', name: 'groupe_prive_new')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $groupePrive = new GroupePrive();
        $form = $this->createForm(GroupePriveType::class, $groupePrive);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $groupePrive->setCreateur($this->getUser());

            $entityManager->persist($groupePrive);
            $entityManager->flush();

            $this->addFlash('success', 'Le groupe privé a été créé avec succès.');

            return $this->redirectToRoute('groupe_prive_index');
        }

        return $this->render('groupe_prive/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
