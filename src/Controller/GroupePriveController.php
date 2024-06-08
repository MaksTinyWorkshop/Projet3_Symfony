<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\GroupePrive;
use App\Entity\Sortie;

use App\Form\CreaSortiePrivateFormType;
use App\Form\GroupePriveType;
use App\Repository\GroupePriveRepository;
use App\Services\InscriptionsService;
use App\Services\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


//Todo: déporter les méthodes dans un service

#[Route('/groupe-prive', name: 'groupe_prive_')]
class GroupePriveController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(GroupePriveRepository $groupePriveRepository): Response
    {
        $user = $this->getUser();
        $groupes = $groupePriveRepository->findBy(['createur' => $user->getId()]);

        return $this->render('groupe_prive/index.html.twig', [
            'groupes' => $groupes,
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(GroupePrive $groupePrive): Response
    {
        return $this->render('groupe_prive/show.html.twig', [
            'groupe' => $groupePrive,
        ]);
    }

    #[Route('/{id}/creer-evenement', name: 'creer_evenement', requirements: ['id' => '\d+'])]
    public function creerEvenement(
        Request $request,
        GroupePrive $groupePrive,
        EntityManagerInterface $entityManager,
        InscriptionsService $inscriptionsService,
        SendMailService $sendMailService,

    ): Response {
        $sortie = new Sortie();
        $nbParticipants = $groupePrive->getParticipants()->count();
        $form = $this->createForm(CreaSortiePrivateFormType::class, $sortie, [
            'nbParticipants' => $nbParticipants,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sortie->setOrganisateur($this->getUser())
            ->setGroupePrive($groupePrive)
            ->setSite($this->getUser()->getSite())
            ->setEtat($entityManager->getRepository(Etat::class)->findOneBy(['id' => '3']));

            foreach ($groupePrive->getParticipants() as $participant) {
                if ($inscriptionsService->hasEventOnDate($participant, $sortie->getDateHeureDebut())) {
                    $this->addFlash('danger', "L'utilisateur {$participant->getPseudo()} est déjà inscrit à un autre événement ce jour-là.");
                    // suppression du return ici car si un seul est pris sur cette intervalle ça coupe toute la procédure
                }
            }

            $entityManager->persist($sortie);
            $entityManager->flush();

            ////// Fonctionnel mais désactivé pour éviter de surcharger pendant les phases de test
            /*
            foreach ($groupePrive->getParticipants() as $participant) {
                $sendMailService->sendMail(
                    'no-reply@sortir.com',
                    $participant->getEmail(),
                    'Nouvel Événement Créé',
                    'email/nouvel_evenement.html.twig',
                    [
                        'sortie' => $sortie,
                        'participant' => $participant,
                    ]
                );
            }
            */
            $this->addFlash('success', 'L\'evenement privé '. $sortie->getNom() .' a été créé avec succès.');
            return $this->redirectToRoute('groupe_prive_show', ['id' => $groupePrive->getId()]);
        }

        return $this->render('groupe_prive/evenement/creer.html.twig', [
            'form' => $form->createView(),
            'groupePrive' => $groupePrive,
        ]);
    }

    #[Route('/creer', name: 'new')]
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
