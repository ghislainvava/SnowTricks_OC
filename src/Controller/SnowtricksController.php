<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SnowtricksController extends AbstractController
{
    #[Route('/snowtricks', name: 'app_snowtricks')]
    public function index(): Response
    {
        return $this->render('snowtricks/index.html.twig', [
            'controller_name' => 'SnowtricksController',
        ]);
    }
    #[Route('/', name: 'home')]
    public function home(): Response
    {
        return $this->render('snowtricks/home.html.twig', [
            'title' => "Bienvenue sur SnowStricks !"
        ]);
    }
    #[Route('/snowtricks/2', name: 'figure_show')]
    public function show(): Response
    {
        return $this->render('snowtricks/show.html.twig', [
            'title' => "Bienvenue sur SnowStricks !"
        ]);
    }
}
