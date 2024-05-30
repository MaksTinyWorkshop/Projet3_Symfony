<?php

namespace App\Controller;

use App\Repository\ParticipantsRepository;
use App\Services\AdminService;
use App\Services\ParticipantsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Classe de routage d'administration
 * todo: ajouter la possibilité d'upload un CSV
 * /admin -> index page d'administration
 * /admin/addUser -> ajoute un user
 */
#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    public function __construct(
        private AdminService $adminService,
        private ParticipantsRepository $participantsRepository,
        private ParticipantsService  $participantsService
    )
    {
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $participants = $this->participantsRepository->findAll();
        return $this->render('admin/index.html.twig', compact('participants'));
    }

    #[Route('/addUser', name: 'addSingleUser')]
    public function addSingleUser(Request $request): Response
    {
        return $this->adminService->addSingleUser($request);
    }


    #[Route('/addUsersFile', name: 'addUsersByFile')]
    public function addUsersByFile(Request $request): Response
    {
        return $this->adminService->addUsersByCSV($request);
    }

    #[Route('/toggleUser/{pseudo}', name: 'toggleActif')]
    public function toggleActif($pseudo): Response
    {
        $this->adminService->toggleActiveUser($pseudo);
        return $this->redirectToRoute('admin_index');
    }

    #[Route('/deleteUser/{pseudo}', name: 'deleteUser')]
    public function deleteUser($pseudo): Response
    {
            $this->participantsService->deleteProfil($pseudo);
            return $this->redirectToRoute('admin_index');
    }

}
