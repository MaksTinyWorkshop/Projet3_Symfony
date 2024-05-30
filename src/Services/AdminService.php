<?php

namespace App\Services;

use App\Entity\Participants;
use App\Form\AdminImportFormType;
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
 * Service d'administration qui permet d'ajouter un profil via un CSV en tant qu'admin avec envoi de
 * lien de changement de mot de passe au nouvel utilisateur
 */
class AdminService extends AbstractController
{

    public function __construct(
        private string                      $dataDirectory,
        private ParticipantsRepository      $participantsRepository,
        private EntityManagerInterface      $entityManager,
        private ParticipantsService         $participantsService,
        private UserPasswordHasherInterface $passwordHasher,
    )
    {
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function addUserByCSV(Request $request): Response
    {
        $filepath = $this->dataDirectory . 'RandomSolo.csv';
        $normalizers = [new ObjectNormalizer()];
        $encoders = [new CsvEncoder()];

        $serializer = new Serializer($normalizers, $encoders);
        $fileString = file_get_contents($filepath);
        $data = $serializer->decode($fileString, 'csv');
        $data = $data[0];
        $email = $data['email'];
        $userEnBase = $this->participantsRepository->findOneBy(['email' => $email]);
        if (!$userEnBase) {
            $participant = new Participants();
            $participant->setEmail($email)
                ->setPseudo($data['login']['username'])
                ->setNom($data['name']['last'])
                ->setPrenom($data['name']['first'])
                ->setTelephone($data['cell'])
                ->setActif(true)
                ->setPassword($this->passwordHasher->hashPassword($participant, 'Pa$$w0rd'));
        } else {
            $this->addFlash('warning', 'cet email existe en base');
            return $this->redirectToRoute('admin_index');
        }
        $form = $this->createForm(AdminImportFormType::class, $participant);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($participant);
            $this->entityManager->flush();

            $this->addFlash('success', 'Profil correctement importé');
            $this->participantsService->forgotPassword($participant->getEmail(), 'email/import_by_admin.html.twig');

            return $this->redirectToRoute('main');
        }
        return $this->render('security/registerByAdmin.html.twig', [
            'registrationForm' => $form,
        ]);
    }

}