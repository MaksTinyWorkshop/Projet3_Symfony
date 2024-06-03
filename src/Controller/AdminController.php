<?php

namespace App\Controller;

use App\Repository\ParticipantsRepository;
use App\Repository\SortieRepository;
use App\Services\AdminService;
use App\Services\ParticipantsService;
use App\Services\SortiesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Classe de routage d'administration
 * /admin -> index page d'administration
 * /admin/addUser -> ajoute un user
 * /admin/addUsersFile -> ajoute un user par fichier
 * /admin/toggleUser/{pseudo} -> toggle un user Actif/inactif
 */
#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{

    #[Route('/', name: 'index')]
    public function index(
        ParticipantsRepository $participantsRepository
    ): Response
    {
        $participants = $participantsRepository->findAll();
        return $this->render('admin/index.html.twig', compact('participants'));
    }

    #[Route('/addUser', name: 'addSingleUser')]
    public function addSingleUser(Request $request, AdminService $adminService): Response
    {
        return $adminService->addSingleUser($request);
    }


    #[Route('/addUsersFile', name: 'addUsersByFile')]
    public function addUsersByFile(
        Request      $request,
        AdminService $adminService
    ): Response
    {
        return $adminService->addUsersByCSV($request);
    }

    #[Route('/toggleUser/{pseudo}', name: 'toggleActif')]
    public function toggleActif($pseudo, AdminService $adminService): Response
    {
        $adminService->toggleActiveUser($pseudo);
        return $this->redirectToRoute('admin_index');
    }

    #[Route('/deleteUser/{pseudo}', name: 'deleteUser')]
    public function deleteUser($pseudo, ParticipantsService $participantsService): Response
    {
        $participantsService->deleteProfil($pseudo);
        return $this->redirectToRoute('admin_index');
    }

    #[Route('/listeSorties', name: 'listeSorties')]
    public function listSortiesOuvertes( SortieRepository $sortieRepository)
    {
        $sortiesOuvertes = $sortieRepository->sortiesOuvertes();

        return $this->render('admin/listeSorties.html.twig', compact('sortiesOuvertes'));
    }


    #[Route('/cancelSortie/{sortieId}', name: 'cancelSortie')]
    public function cancelSortie(Request $request, $sortieId, SortiesService $sortiesService): Response
    {
        $sortiesService->delete($request, $sortieId);
        return $this->redirectToRoute('admin_index');
    }
}
