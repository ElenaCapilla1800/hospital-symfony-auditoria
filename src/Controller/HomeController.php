<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route; // Usamos Attribute en Symfony 6/7
use Symfony\Component\Security\Http\Attribute\IsGranted; // Para proteger la página

final class HomeController extends AbstractController
{
    // Cambiamos '/home' por '/' para que sea lo primero que se ve al entrar
    #[Route('/', name: 'app_home')]
    // Esta línea obliga a que el usuario esté logueado. Si no, lo manda al login.
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            // Aquí puedes poner un nombre más bonito para el hospital
            'controller_name' => 'Panel de Control Médico',
        ]);
    }
}