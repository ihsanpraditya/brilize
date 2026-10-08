<?php

declare(strict_types=1);

namespace App\Controller;

use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
    public function loginSubmit(Request $request, Inertia $inertia): Response
    {
        $identifier = trim((string) $request->request->get('identifier', ''));
        $password = (string) $request->request->get('password', '');
        $remember = (bool) $request->request->get('remember', false);

        $errors = [];

        if ($identifier === '') {
            $errors['identifier'] = 'NISN, NIP, atau Email wajib diisi.';
        }

        if ($password === '') {
            $errors['password'] = 'Kata sandi wajib diisi.';
        }

        if (!empty($errors)) {
            return $inertia->render('Auth/Login', [
                'errors' => $errors,
                'identifier' => $identifier,
            ]);
        }

        // Simulasi validasi / login logic
        // Dalam implementasi full, ini akan memverifikasi password hash dengan User entity
        $session = $request->getSession();
        $session->set('user_logged_in', true);
        $session->set('user_identifier', $identifier);

        $this->addFlash('success', 'Selamat datang kembali di Brilize School ERP!');

        return $this->redirectToRoute('home');
    }

    #[Route('/logout', name: 'auth_logout', methods: ['POST', 'GET'])]
    public function logout(Request $request): Response
    {
        $session = $request->getSession();
        $session->invalidate();

        return $this->redirectToRoute('auth_login');
    }
}
