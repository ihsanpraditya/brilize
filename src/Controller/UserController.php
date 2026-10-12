<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\User\CreateUserDTO;
use App\DTO\User\UpdateUserDTO;
use App\Enum\UserRole;
use App\Enum\UserStatus;
use App\Service\UserService;
use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use InvalidArgumentException;

#[Route('/users', name: 'users_')]
final class UserController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, UserService $userService, Inertia $inertia): Response
    {
        $query = $request->query->get('q');
        $statusParam = $request->query->get('status');
        $status = $statusParam ? UserStatus::tryFrom((string) $statusParam) : null;

        $users = $userService->getAllUsers($query !== null ? (string) $query : null, $status);

        return $inertia->render('Users/Index', [
            'users' => array_map(fn($u) => $u->toArray(), $users),
            'filters' => [
                'q' => $query,
                'status' => $statusParam,
            ],
            'options' => [
                'roles' => array_map(fn(UserRole $r) => [
                    'value' => $r->value,
                    'label' => $r->label(),
                ], UserRole::cases()),
                'statuses' => array_map(fn(UserStatus $s) => [
                    'value' => $s->value,
                    'label' => $s->label(),
                    'badge' => $s->badgeClass(),
                ], UserStatus::cases()),
            ],
        ]);
    }

    #[Route('', name: 'store', methods: ['POST'])]
    public function store(Request $request, UserService $userService): Response
    {
        $dto = CreateUserDTO::fromRequest($request);

        try {
            $userService->createUser($dto);
            $this->addFlash('success', 'Pengguna baru berhasil ditambahkan.');
        } catch (InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('users_index');
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request, UserService $userService): Response
    {
        $dto = UpdateUserDTO::fromRequest($request);

        try {
            $userService->updateUser($id, $dto);
            $this->addFlash('success', 'Data pengguna berhasil diperbarui.');
        } catch (InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('users_index');
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, UserService $userService): Response
    {
        $userService->deleteUser($id);
        $this->addFlash('success', 'Pengguna berhasil dihapus.');

        return $this->redirectToRoute('users_index');
    }
}
