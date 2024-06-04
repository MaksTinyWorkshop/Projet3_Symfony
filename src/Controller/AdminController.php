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

/////// Route accessible uniquement par l'admin
#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    /////// Route 1 : panneau de commande administrateur
    #[Route('/', name: 'index')]
    public function index(ParticipantsRepository $participantsRepository): Response
    {
        $participants = $participantsRepository->findAll();
        return $this->render('admin/index.html.twig', compact('participants'));
    }

    /////// Route 2 : ajout d'un utilisateur par l'admin
    #[Route('/addUser', name: 'addSingleUser')]
    public function addSingleUser(Request $request, AdminService $adminService): Response
    {
        return $adminService->addSingleUser($request);
    }

    /////// Route 3 : ajout de plusieurs utilisateurs par l'admin via fichier CSV
    #[Route('/addUsersFile', name: 'addUsersByFile')]
    public function addUsersByFile(
        Request      $request,
        AdminService $adminService
    ): Response
    {
        return $adminService->addUsersByCSV($request);
    }

    /////// Route 4 : Toggle statut d'un utilisateur (actif/inactif)
    #[Route('/toggleUser/{pseudo}', name: 'toggleActif')]
    public function toggleActif($pseudo, AdminService $adminService): Response
    {
        $adminService->toggleActiveUser($pseudo);
        return $this->redirectToRoute('admin_index');
    }

    /////// Route 5 : suppression d'un utilisateur
    #[Route('/deleteUser/{pseudo}', name: 'deleteUser')]
    public function deleteUser($pseudo, ParticipantsService $participantsService): Response
    {
        $participantsService->deleteProfil($pseudo);
        return $this->redirectToRoute('admin_index');
    }

    /////// Route 6 : liste des sorties ouvertes par tous
    #[Route('/listeSorties', name: 'listeSorties')]
    public function listSortiesOuvertes( SortieRepository $sortieRepository)
    {
        $sortiesOuvertes = $sortieRepository->sortiesOuvertes();

        return $this->render('admin/listeSorties.html.twig', compact('sortiesOuvertes'));
    }

    /////// Route 7 : annulation d'une sortie par l'admin
    #[Route('/cancelSortie/{sortieId}', name: 'cancelSortie')]
    public function cancelSortie(Request $request, $sortieId, SortiesService $sortiesService): Response
    {
        $sortiesService->delete($request, $sortieId);
        return $this->redirectToRoute('admin_index');
    }
}
