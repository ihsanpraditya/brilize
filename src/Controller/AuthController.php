<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Auth\LoginDTO;
use App\Service\AuthService;
use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use InvalidArgumentException;

final class AuthController extends AbstractController
{
    #[Route('/login', name: 'auth_login', methods: ['GET'])]
    public function login(Inertia $inertia): Response
    {
        return $inertia->render('Auth/Login', [
            'status' => $this->getParameter('kernel.environment') === 'dev' ? 'Mode Pengembangan (Dev)' : null,
        ]);
    }

    #[Route('/login', name: 'auth_login_submit', methods: ['POST'])]
    public function loginSubmit(Request $request, AuthService $authService, Inertia $inertia): Response
    {
        $dto = LoginDTO::fromRequest($request);

        try {
            $user = $authService->authenticate($dto);

            $session = $request->getSession();
            $session->set('user_logged_in', true);
            $session->set('user_id', $user->id);
            $session->set('user_name', $user->name);
            $session->set('user_identifier', $user->identifierNumber ?? $user->email ?? $user->username ?? $dto->identifier);
            $session->set('user_roles', $user->roles);

            $this->addFlash('success', 'Selamat datang kembali, ' . $user->name . '!');

            return $this->redirectToRoute('home');
        } catch (InvalidArgumentException $e) {
            return $inertia->render('Auth/Login', [
                'errors' => ['identifier' => $e->getMessage()],
                'identifier' => $dto->identifier,
            ]);
        }
    }

    #[Route('/logout', name: 'auth_logout', methods: ['POST', 'GET'])]
    public function logout(Request $request): Response
    {
        $session = $request->getSession();
        $session->invalidate();

        return $this->redirectToRoute('auth_login');
    }
}
