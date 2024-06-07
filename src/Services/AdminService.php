<?php

namespace App\Services;

use App\Entity\Participants;
use App\Entity\Site;
use App\Form\AdminImportFormType;
use App\Form\CSVImportFormType;
use App\Repository\ParticipantsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Service d'administration qui permet :
 * - d'ajouter un profil utilisateur en tant qu'admin via formulaire avec envoi de
 * lien de changement de mot de passe au nouvel utilisateur
 * - d'importer des utilisateurs via un fichier CSV (envoi de mail aussi)
 * - changer le statut d'un utilisateur (actif/inactif)
 */
class AdminService extends AbstractController
{


    public function __construct(
        private ParticipantsRepository      $participantsRepository,
        private EntityManagerInterface      $entityManager,
        private ParticipantsService         $participantsService,
        private UserPasswordHasherInterface $passwordHasher,
    )
    {
    }


    public function addSingleUser(Request $request): Response
    {
        //Nouvelle instance de participant
        $participant = new Participants();
        $participant
            ->setActif(true)
            ->setPassword($this->passwordHasher->hashPassword($participant, 'Pa$$w0rd'));
        $form = $this->createForm(AdminImportFormType::class, $participant);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userEnBase = $this->participantsRepository->findOneBy(['email' => $participant->getEmail()]);
            if (!$userEnBase) {
                $this->entityManager->persist($participant);
                $this->entityManager->flush();

                $this->addFlash('success', 'Profil correctement importé');
                $this->participantsService->forgotPassword($participant->getEmail(), 'email/import_by_admin.html.twig');

                return $this->redirectToRoute('admin_index', []);
            } else {
                $this->addFlash('warning', 'Un utilisateur avec cet email est déjà enregistré');
            }
        }
        return $this->render('admin/importSingle.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function addUsersByCSV(Request $request): Response
    {
        $form = $this->createForm(CSVImportFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $csvFile = $form->get('csvFile')->getData();
            if ($csvFile) {
                // Déplacez le fichier temporaire vers l'emplacement défini
                $csvFileName = pathinfo($csvFile->getClientOriginalName(), PATHINFO_FILENAME) . '.' . $csvFile->guessExtension();
                $csvFilePath = $this->getParameter('csv_directory') . '/' . $csvFileName;
                $csvFile->move($this->getParameter('csv_directory'), $csvFileName);

                // Traitement du fichier CSV ...
                // Création d'un compteur de participants ajoutés
                $newParticipants = [];
                $normalizers = [new ObjectNormalizer()];
                $encoders = [new CsvEncoder([CsvEncoder::DELIMITER_KEY => ';'])];
                $serializer = new Serializer($normalizers, $encoders);
                $fileString = file_get_contents($csvFilePath);
                $data = $serializer->decode($fileString, 'csv');
                // Extraction des données
                foreach ($data as $userData) {
                    // Recherche du user en base
                    $email = $userData['email'];
                    $userEnBase = $this->participantsRepository->findOneBy(['email' => $email]);
                    // S'il n'est pas trouvé, on le crée
                    if (!$userEnBase) {
                        $participant = new Participants();
                        $participant->setEmail($email)
                            ->setPseudo($userData['pseudo'])
                            ->setNom($userData['nom'])
                            ->setPrenom($userData['prenom'])
                            ->setTelephone($userData['telephone'])
                            ->setActif(true)
                            ->setPassword($this->passwordHasher->hashPassword($participant, 'Pa$$w0rd'));

                        // Recherche de l'entité Site correspondante dans la base de données
                        $siteRepository = $this->entityManager->getRepository(Site::class);
                        $site = $siteRepository->findOneBy(['nom' => $userData['site']]);

                        // Si le site est trouvé en base de données, l'associer à l'utilisateur
                        if ($site) {
                            $participant->setSite($site);
                        } else {
                            // Sinon on lui met le premier en base par défaut
                            $siteDefault = $siteRepository->findOneBy([], ['id' => 'ASC']);
                            $this->addFlash('warning', 'Le site ' . $userData['site'] . ' n\'existe pas en base de données.
                    Utilisateur ' . $participant->getEmail() . ' inscrit à ' . $siteDefault->getNom() . ' à la place.');
                        }

                        $this->entityManager->persist($participant);
                        // On ajoute le participant à la liste
                        $newParticipants[] = $participant;
                    } else {
                        $this->addFlash('warning', 'L\'email ' . $email . ' existe déjà en base.');
                    }
                }
                $this->entityManager->flush();

                // Envoi de lien de reset du MDP à chaque user nouvellement crée, en se servant de la liste des nouveaux participants
                foreach ($newParticipants as $participant) {
                    $this->participantsService->forgotPassword($participant->getEmail(), 'email/import_by_admin.html.twig');
                }

                $this->addFlash('success', count($newParticipants) . ' utilisateurs importés avec succès.');

                return $this->redirectToRoute('admin_index');

            } else {
                $this->addFlash('warning', 'Veuillez sélectionner un fichier CSV.');
            }
        }

        return $this->render('admin/importMultiple.html.twig', [
            'registrationForm' => $form->createView(),
        ]);

    }

    public function toggleActiveUser(string $pseudo):void
    {
        $user = $this->participantsRepository->findOneBy(['pseudo' => $pseudo]);
        if ($user->isActif()){
            $user->setActif(false);
            $this->addFlash('success', 'Utilisateur '. $user->getPseudo() . ' désactivé');
        } else {
            $user->setActif(true);
            $this->addFlash('success', 'Utilisateur '. $user->getPseudo() . ' activé');
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

}