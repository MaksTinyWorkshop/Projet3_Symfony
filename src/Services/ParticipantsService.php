<?php

namespace App\Services;

use App\Entity\Participants;
use App\Form\RegistrationFormType;
use App\Repository\ParticipantsRepository;
use App\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;



class ParticipantsService extends AbstractController
{

    public function __construct(
        private ParticipantsRepository $participantsRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager,
        private Security $security,
        private JWTService $jwt,
        private SendMailService  $mail,
        private LoggerInterface $logger
    ) {}

    public function getAll():array
    {
        return $this->participantsRepository->findAll();
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function register(Request $request):Response
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
            $this->entityManager->persist($participant);
            $this->entityManager->flush();

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

            // Envoi de mail de confirmation
            try {
                $this->mail->sendMail(
                    'no-reply@sortir.com',
                    $participant->getEmail(),
                    'Activation de votre compte',
                    'email/register.html.twig',
                    compact('participant', 'token')
                );
                $this->logger->info('Registration email sent to ' . $participant->getEmail());
            } catch (TransportExceptionInterface $e) {
                $this->logger->error('Failed to send registration email: ' . $e->getMessage());
            }

            return $this->security->login($participant, AppAuthenticator::class, 'main');
        }
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}