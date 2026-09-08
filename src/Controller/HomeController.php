<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Nytodev\InertiaBundle\Service\Inertia;

class HomeController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(Inertia $inertia): Response
    {
        return $inertia->render('Home/Index');
    }

    #[Route('/public', name: 'public', methods: ['GET'])]
    public function public(): Response
    {
        return new Response('public');
    }
}
