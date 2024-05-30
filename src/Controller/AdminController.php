<?php

namespace App\Controller;

use App\Services\AdminService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
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
    )
    {
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
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
}
