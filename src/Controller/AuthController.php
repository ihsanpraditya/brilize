<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Auth\LoginDTO;
use App\Repository\UserRepository;
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
    public function loginSubmit(Request $request, Inertia $inertia, UserRepository $userRepository): Response
    {
        $dto = LoginDTO::fromRequest($request);
        $errors = $dto->validate();

        if (!empty($errors)) {
            return $inertia->render('Auth/Login', [
                'errors' => $errors,
                'identifier' => $dto->identifier,
            ]);
        }

        // Cek user di database jika sudah ada
        $user = $userRepository->findByIdentifier($dto->identifier);

        // Jika dalam masa development awal (belum ada seeder / akun di DB), sediakan fallback login
        if ($user !== null) {
            // Verifikasi password hash (atau fallback verifikasi jika plain/dev)
            if (!password_verify($dto->password, $user->getPassword()) && $dto->password !== $user->getPassword()) {
                return $inertia->render('Auth/Login', [
                    'errors' => ['password' => 'Kata sandi yang Anda masukkan salah.'],
                    'identifier' => $dto->identifier,
                ]);
            }

            // Update waktu login terakhir
            $user->setLastLoginAt(new \DateTimeImmutable());
            $userRepository->save($user, true);

            $userName = $user->getName();
            $userRoles = $user->getRoles();
        } else {
            // Fallback demo login untuk kenyamanan proses dev
            $userName = $dto->identifier;
            $userRoles = ['ROLE_USER'];
        }

        $session = $request->getSession();
        $session->set('user_logged_in', true);
        $session->set('user_name', $userName);
        $session->set('user_identifier', $dto->identifier);
        $session->set('user_roles', $userRoles);

        $this->addFlash('success', 'Selamat datang kembali, ' . $userName . '!');

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
