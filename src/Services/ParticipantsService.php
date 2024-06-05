<?php

namespace App\Services;

use App\Entity\Etat;
use App\Entity\Inscriptions;
use App\Entity\Participants;
use App\Form\RegistrationFormType;
use App\Form\ResetPasswordFormType;
use App\Repository\InscriptionsRepository;
use App\Repository\ParticipantsRepository;
use App\Repository\SortieRepository;
use App\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\String\Slugger\SluggerInterface;


/**
 * Service des participants, permettant de :
 * - ajouter/modifier/supprimer son profil en tant qu'utilisateur
 * - consulter le profil d'un autre participant (données restreintes)
 * - reinitialiser son mot de passe
 */
class ParticipantsService extends AbstractController
{
    ///////////////////////////////////////// Constructeur pour injection de dépendances nécessaires au service
    public function __construct(
        private ParticipantsRepository      $participantsRepository,
        private SortieRepository            $sortieRepository,
        private InscriptionsRepository      $inscriptionsRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface      $entityManager,
        private Security                    $security,
        //private JWTService                  $jwt,
        private SendMailService             $mail,
        private TokenGeneratorInterface     $tokenGenerator,
        private FormFactoryInterface        $formFactory,
        private SluggerInterface            $slugger,
        private string                      $photosDirectory
    )
    {
    }


    ///////////////////////////////////////////// Méthodes d'enregistrement du User

    /**
     * @throws TransportExceptionInterface
     */
    public function register(Request $request): Response
    {
        // Nouvelle instance de Participant:
        $participant = new Participants();
        // Création du formulaire de création de compte et handleRequest
        $form = $this->createForm(RegistrationFormType::class, $participant);
        $form->handleRequest($request);

        // Validation du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            //Encodage du mot de passe
            $participant->setPassword(
                $this->passwordHasher->hashPassword(
                    $participant,
                    $form->get('plainPassword')->getData()
                ));

            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();

                try {
                    $photoFile->move(
                        $this->photosDirectory,
                        $newFilename
                    );
                } catch (FileException $e) {
                    // gère les exceptions si quequechose se passe pendant l'upload
                }

                $participant->setPhoto($newFilename);
            }

            $this->entityManager->persist($participant);
            $this->entityManager->flush();

            /* Feature désactivée
            // On génère le JWT du nouveau Participant
            // On crée le Header
            $header = [
                'typ' => 'JWT',
                'alg' => 'HS256'
            ];
            // On crée le payload
            $payload = [
                'user_id' => $participant->getId(),
            ];
            // On génère le token
            $token = $this->jwt->generate($header, $payload, $_ENV['JWT_SECRET']);
            */

            // Envoi de mail de confirmation

            $this->mail->sendMail(
                'no-reply@sortir.com',
                $participant->getEmail(),
                'Bienvenue',
                'email/register.html.twig',
                compact(
                    'participant',
                // 'token')
                ));
            $this->addFlash('success', 'Bienvenue ' . $participant->getPseudo() . ' sur Sortir.com ');
            return $this->security->login($participant, AppAuthenticator::class, 'main');
        }
        return $this->render('security/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    /* Features désactivées
    public function verify(string $token, string $jwtSecret): bool
    {
        // On vérifie la validité et intégrité du token
        if ($this->jwt->isValid($token) && !$this->jwt->isExpired($token) && $this->jwt->checkSignature($token, $jwtSecret)) {
            // On récupère le payload
            $payload = $this->jwt->getPayload($token);
            // On récupère le user du token
            $participant = $this->participantsRepository->find($payload['user_id']);
            // On vérifie que le User existe et pas déjà activé
            if ($participant && !$participant->isActif()) {
                $participant->setActif(true);
                $this->entityManager->flush();
                return true;
            }
        }
        return false;
    }

    public function reSendVerif($participant, string $jwtSecret): void
    {
        // On génère le JWT du user
        // On crée le Header
        $header = ['typ' => 'JWT', 'alg' => 'HS256'];
        // On crée le payload
        $payload = ['user_id' => $participant->getId()];
        // On génère le token
        $token = $this->jwt->generate($header, $payload, $jwtSecret);
        // Envoi du mail
        $this->mail->sendMail(
            'no-reply@sortir.com',
            $participant->getEmail(),
            'Activation de votre compte',
            'email/register.html.twig',
            compact('participant', 'token')
        );
    }
    */

    ///////////////////////////////////////////// Méthodes de gestion de l'oubli de mot de passe

    /**
     * @throws TransportExceptionInterface
     */
    public function forgotPassword($email, $template = 'email/password_reset.html.twig')
    {
        // On récupère le User par son mail
        $participant = $this->participantsRepository->findOneBy(['email' => $email]);
        // On contrôle son existence
        if ($participant) {
            // On génère un token de réinitialisation
            $token = $this->tokenGenerator->generateToken();
            $participant->setResetToken($token);
            $this->entityManager->persist($participant);
            $this->entityManager->flush();
            // On génère le lien de reset du MDP
            $url = $this->generateUrl('reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
            // On crée les données du mail
            $context = compact('url', 'participant');
            // On envoie le mail
            $this->mail->sendMail(
                'no-reply-passwordreset@sortir.com',
                $participant->getEmail(),
                'Réinitialisation de votre mot de passe',
                $template,
                $context
            );
            return $participant;
        }
        return null;
    }

    public function resetPassword($token, Request $request): array
    {
        // On vérifie si on a ce token dans la BDD
        $participant = $this->participantsRepository->findOneBy(['resetToken' => $token]);
        if ($participant) {
            $form = $this->formFactory->create(ResetPasswordFormType::class, $participant);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                // On efface le token
                $participant->setResetToken('');
                // On encode son password
                $participant->setPassword(
                    $this->passwordHasher->hashPassword(
                        $participant,
                        $form->get('password')->getData()));
                $this->entityManager->persist($participant);
                $this->entityManager->flush();
                return ['success' => true, 'form' => $form];
            }
            return ['success' => false, 'form' => $form];
        }
        return ['success' => false, 'form' => null];
    }


    ///////////////////////////////////////////// Méthodes de gestion du profil
    public function updateProfil(Request $request, $participant): Response
    {
        // Création du formulaire de création de compte et handleRequest
        $form = $this->createForm(RegistrationFormType::class, $participant, ['is_edit' => true]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //Encodage du mot de passe
            $participant->setPassword(
                $this->passwordHasher->hashPassword(
                    $participant,
                    $form->get('plainPassword')->getData()
                ));
            //Gestion de l'image
            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();

                try {
                    $photoFile->move(
                        $this->photosDirectory,
                        $newFilename
                    );
                } catch (FileException $e) {
                    // gère les exceptions si quequechose se passe pendant l'upload
                }

                $participant->setPhoto($newFilename);
            }

            $this->entityManager->persist($participant);
            $this->entityManager->flush();

            $this->addFlash('success', 'Profil correctement mis à jour');

            return $this->redirectToRoute('participants_details', ['pseudo' => $participant->getPseudo()]);
        }
        if ($form->isSubmitted() && !$form->isValid()) {
            dd($form->getErrors(true));
        }

        return $this->render('participants/details.html.twig', compact('participant', 'form'));
    }

    public function consultationProfil(string $pseudo): Response
    {
        $participant = $this->participantsRepository->findOneBy(['pseudo' => $pseudo]);
        return $this->render('participants/details.html.twig', compact('participant'));
    }

    public function deleteProfil(string $pseudo, TokenStorageInterface $tokenStorage = null): void
    {
        $tokenStorage?->setToken(null);
        $participant = $this->participantsRepository->findOneBy(['pseudo' => $pseudo]);
        if ($participant) {
            // Annuler les sorties organisées par le participant
            $sortiesOrgaParParticipant = $this->sortieRepository->sortiesOrganiseesParUserId($participant->getId());
            if ($sortiesOrgaParParticipant) {
                foreach ($sortiesOrgaParParticipant as $sortie) {
                    $etat = $sortie->getEtat()->getId();
                    // Traitement des cas en fonction des sorties
                    switch ($etat) {
                        // La sortie est crée mais non publiée
                        case 1:
                            $this->entityManager->remove($sortie);          // inscription dans la base
                            $this->entityManager->flush();
                            break;
                        // La sortie est crée et publiée
                        case 2:
                            $sortieId = $sortie->getId();
                            $motifAnnulation = 'L\'organisateur est désinscrit de la plateforme';
                            $etatAnnuleId = 6;
                            $etatAnnule = $this->entityManager->getRepository(Etat::class)->find($etatAnnuleId);
                            $sortie->setInfosSortie($motifAnnulation)
                                ->setEtat($etatAnnule)
                                ->setOrganisateur(null);
                            // Annuler les inscriptions eventuelles et prévenir les participants
                            $inscriptions = $this->entityManager->getRepository(Inscriptions::class)->findBy(['sortie' => $sortieId]);

                            ////////////////////////////////////////////////////////////////////////////
                            ///////////// Feature d'envoi de mail désactivée temporairement ////////////
                            ///////////// (trop d'envoi de mail à la fois pour la version   ////////////
                            ///////////// gratuite de MailTrap)                             ////////////
                            /// ////////////////////////////////////////////////////////////////////////
                            /*
                            $participants = $this->entityManager->getRepository(Inscriptions::class)->getParticipantsBySortieId($sortieId);
                            foreach ($participants as $participant) {
                                $context = compact('sortie', 'participant');
                                $this->sendMailService->sendMail(
                                    'cancelledSortie@sortir.com',
                                    $participant['email'],
                                    'Annulation de sortie',
                                    'email/cancelled_sortie.html.twig',
                                    $context
                                );
                            }
                            */
                            foreach ($inscriptions as $inscription) {
                                $this->entityManager->remove($inscription);
                            };
                            $this->entityManager->persist($sortie);
                            $this->entityManager->flush();
                            break;
                        default:
                            $sortie->setOrganisateur(null);
                            $this->entityManager->persist($sortie);
                            $this->entityManager->flush();
                            break;
                    }
                }
            }
            //  Supprimer les inscriptions aux sorties du participant
            $inscriptionsParticipant = $this->inscriptionsRepository->findBy(['participant' => $participant->getId()]);
            if ($inscriptionsParticipant) {
                foreach ($inscriptionsParticipant as $inscription) {
                    $this->entityManager->remove($inscription);
                }
            }
            // Supprimer le participant
            $this->entityManager->remove($participant);
            $this->entityManager->flush();

            $this->addFlash('success', 'Profil correctement supprimé');
        } else {
            $this->addFlash('danger', 'Une erreur s\'est produite, veuillez recommencer');
        }
    }


///////////////////////////////////////////// Méthodes de recherche en base (utile ??)
    public
    function getAll(): array
    {
        return $this->participantsRepository->findAll();
    }
}