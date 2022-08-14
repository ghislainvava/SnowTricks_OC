<?php

namespace App\Controller;

use App\Entity\Figure;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Form\FigureFormType;
use App\Repository\FigureRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface; //remplace ObjectManager
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

    #[Route('/snowtricks/addfigure', name: 'add_figure')]
    public function addFigure(Figure $figure = null, EntityManagerInterface $manager, Request $request): Response
    {
        $figure = new Figure();
        $user = $this->getUser();
        $figure->setUser($user);
        $form = $this->createForm(FigureFormType::class, $figure);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $image =$form->get('image')->getData();
            $fichier = md5(uniqid()).'.'.$image->guessExtension();
            $image->move(
                $this->getParameter('images_directory'),
                $fichier
            );
            $figure->setImage($fichier);
            $figure = $form->getData();
            $manager->persist($figure);
            $manager->flush();
            $this->addFlash('success', 'Figure enregistrée');
            return $this->redirectToRoute('add_figure');
        }

        return $this->render('snowtricks/createFigure.html.twig', [
            'formCreateFigure' => $form->createView()
             ]);
    }

    #[Route('/snowtricks/{id}/edit', name: 'edit_figure')]
    public function editFigure($id, Figure $figure = null, FigureRepository $repo, EntityManagerInterface $manager, Request $request): Response
    {
        $figure = $repo->find($id);
        $fichier = $figure->getImage();
        $user = $this->getUser();
        $figure->setUser($user);
        $form = $this->createForm(FigureFormType::class, $figure);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('image')->getData() !== null) {
                $image =$form->get('image')->getData();
                $fichier = md5(uniqid()).'.'.$image->guessExtension();
                $image->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );
            }
            $figure->setImage($fichier);
            $figure = $form->getData();

            $manager->persist($figure);
            $manager->flush();
            $this->addFlash('success', 'Figure enregistrée');
            return $this->redirectToRoute('add_figure');
        }

        return $this->render('snowtricks/createFigure.html.twig', [
            'formCreateFigure' => $form->createView()
             ]);
    }


    #[Route('/snowtricks/{id}', name: 'figure_show')]
    public function show($id, FigureRepository $repo, Request $request, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();
        //dd($user);
        $comment = new Comment();
        $comment->setCreateAt(new \DateTimeImmutable());
        //$userid = $user->get('id')->getData();
        $comment->setUser($user);
        dd($comment);
        $commentForm = $this->createForm(CommentType::class, $comment);
        $figure = $repo->find($id);
        /** @var Figure $figure */
        $figure->addComment($comment);
        $commentForm->handleRequest($request);
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $comment = $commentForm->getData();
            $manager->persist($figure);
            $manager->persist($comment);
            $manager->flush();
        }
        return $this->render('snowtricks/show.html.twig', [
            'figure' => $figure,
            'commentForm' => $commentForm->createView()
        ]);
    }

    #[Route('/delete/{id}', name: 'snowtrick_delete')]
    public function deletePost(Figure $figure, Request $request, FigureRepository $figureRepository, EntityManagerInterface $manager): Response  //RedirectResponse
    {
        $this->getUser();
        $figure = $figureRepository->find($request->get('id'));
        $manager->remove($figure);
        $manager->flush();

        $this->addFlash('sucess', 'la figure a bien été supprimée');

        return $this->redirectToRoute("home");
    }
}
