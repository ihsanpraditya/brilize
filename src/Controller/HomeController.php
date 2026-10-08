<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\DashboardService;
use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(Request $request, DashboardService $dashboardService, Inertia $inertia): Response
    {
        $session = $request->getSession();
        
        $isLoggedIn = (bool) $session->get('user_logged_in', false);
        if (!$isLoggedIn) {
            return $this->redirectToRoute('auth_login');
        }

        $userName = (string) $session->get('user_name', 'Administrator');
        $userIdentifier = (string) $session->get('user_identifier', 'admin');
        $userRoles = (array) $session->get('user_roles', ['ROLE_USER']);

        $dashboardData = $dashboardService->getDashboardSummary($userIdentifier);

        return $inertia->render('Home/Index', [
            'auth' => [
                'user' => [
                    'name' => $userName,
                    'identifier' => $userIdentifier,
                    'roles' => $userRoles,
                ],
            ],
            'dashboard' => $dashboardData,
        ]);
    }
}
