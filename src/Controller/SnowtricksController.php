<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Figure;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CategoryRepository;
use App\Repository\FigureRepository;
use Doctrine\ORM\EntityManagerInterface; //remplace ObjectManager

class SnowtricksController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(FigureRepository $figure, CategoryRepository $cat): Response   //getDoctrine deprecied use repository in parametre
    {
        $figures = $figure->findAll();
        $category = $cat->findAll();
        return $this->render('snowtricks/index.html.twig', [
            'controller_name' => 'SnowtricksController',
            'figures' => $figures,
            'category' => $category
        ]);
    }

    #[Route('/snowtricks/newfigure', name: 'snowstricks_create')] //important! order this route before {id}
    #[Route('/snowtricks/{id}/edit', name: 'snowstricks_edit')]
    public function form(Figure $figure = null, Request $request, EntityManagerInterface $manager): Response
    {
        if (!$figure) {
        }
        
        $form = $this->createFormBuilder($figure)
                    ->add('name')
                    ->add('image')
                    ->add('content')
                    ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($figure);
            $manager->flush();
            return $this->redirectToRoute('figure_show', ['id' => $figure->getId()]);
        }
        return $this->render('snowtricks/create.html.twig', [
            'formFigure' => $form->createView()
             ]);
    }

    #[Route('/snowtricks/{id}', name: 'figure_show')]
    public function show($id, FigureRepository $repo): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $figure = $repo->find($id);
        return $this->render('snowtricks/show.html.twig', [
            'figure' => $figure,
            'commentForm' => $form->createView()
        ]);
    }
}
