<?php

namespace App\Controller;

use App\Entity\Figure;
use App\Entity\Comment;
use App\Entity\Category;
use App\Form\CommentType;
use App\Form\FigureType;
use App\Repository\FigureRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\ORM\EntityManagerInterface; //remplace ObjectManager
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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


    // #[Route('/snowtricks/newfigure', name: 'snowstricks_create')] //important! order this route before {id}
    // #[Route('/snowtricks/{id}/edit', name: 'snowstricks_edit')]
    // public function form(Figure $figure = null, Request $request, EntityManagerInterface $manager): Response
    // {
    //     if (!$figure) {
    //         $figure = new Figure();
    //     }

    //     $form = $this->createFormBuilder($figure)
    //                 ->add('groupe', Category::class)
    //                 ->add('name')
    //                 ->add('image')
    //                 ->add('content')
    //                 ->getForm();
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $manager->persist($figure);
    //         $manager->flush();
    //         return $this->redirectToRoute('figure_show', ['id' => $figure->getId()]);
    //     }
    //     return $this->render('snowtricks/create.html.twig', [
    //         'formFigure' => $form->createView()
    //          ]);
    // }

    #[Route('/snowstricks/addfigure', name: 'add_figure')]
    public function addFigure(EntityManagerInterface $manager, Request $request): Response
    {
        $user = $this->getUser();
        $figure = new Figure();
        $figure->getUser($user);

        $form = $this->createForm(FigureType::class, $figure);
        $form->handleRequest($request);
        $figure = $form->getData();
        $manager->persist($figure);
        $manager->flush();



        return $this->render('snowtricks/createFigure.html.twig', [
            'formCreateFigure' => $form->createView()
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

    #[Route('/delete/{id}', name: 'snowtrick_delete')]
    public function deletePost(Figure $figure, Request $request, FigureRepository $figureRepository, EntityManagerInterface $manager): Response  //RedirectResponse
    {
        $user = $this->getUser();
        $figure = $figureRepository->find($request->get('id'));
        $manager->remove($figure);
        $manager->flush();

        $this->addFlash('sucess', 'la figure a bien été supprimée');

        return $this->redirectToRoute("home");
    }
}
