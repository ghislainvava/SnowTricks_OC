<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use APP\Entity\Figure;
use App\Repository\FigureRepository;

class SnowtricksController extends AbstractController
{
    #[Route('/snowtricks', name: 'app_snowtricks')]
    public function index(FigureRepository $repo): Response   //getDoctrine deprecied use repository in parametre
    {
        $figures = $repo->findAll();
        return $this->render('snowtricks/index.html.twig', [
            'controller_name' => 'SnowtricksController',
            'figures' => $figures
        ]);
    }
    #[Route('/', name: 'home')]
    public function home(): Response
    {
        return $this->render('snowtricks/home.html.twig', [
            'title' => "Bienvenue sur SnowStricks !"
        ]);
    }
    #[Route('/snowtricks/{id}', name: 'figure_show')]
    public function show($id, FigureRepository $repo): Response
    {
        $figure = $repo->find($id);
        return $this->render('snowtricks/show.html.twig', [
            'figure' => $figure
        ]);
    }
}
