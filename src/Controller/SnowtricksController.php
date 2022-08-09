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
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Doctrine\ORM\EntityManagerInterface; //remplace ObjectManager
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Constraints\File;

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


    #[Route('snowtricks/newfigure', name: 'snowtrick_create')] //important! order this route before {id}
    #[Route('/snowtricks/{id}/edit', name: 'snowtrick_edit')]
    public function form(Figure $figure = null, Request $request, EntityManagerInterface $manager): Response
    {
        if (!$figure) {
            $figure = new Figure();
        }
        $user = $this->getUser();
        $figure->setUser($user);
        $form = $this->createFormBuilder($figure)
                    ->add('groupe', NumberType::class)
                    ->add('name')
                    ->add('image', FileType::class, [
                        'label' => 'fichier au format jpg, jpeg, gif',
                        'mapped' => false,
                        'required' => false,
                        'constraints' => [
                            new File([
                                'maxSize' => '1024k',
                                'mimeTypes' => [
                                    //'application/pdf',
                                    'image/jpg',
                                    'image/jpeg',
                                    'image/gif',

                                    ],

                                ])
                            ],
                    ])
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

    #[Route('/snowtricks/addfigure', name: 'add_figure')]
    public function addFigure(EntityManagerInterface $manager, Request $request): Response
    {
        $user = $this->getUser();
        $figure = new Figure();
        $figure->setUser($user);
        $form = $this->createForm(FigureFormType::class, $figure);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //$figure->getUser($user);
            $figure = $form->getData();
            $manager->persist($figure);
            $manager->flush();
        }

        return $this->render('snowtricks/createFigure.html.twig', [
            'formCreateFigure' => $form->createView()
             ]);
    }


    #[Route('/snowtricks/{id}', name: 'figure_show')]
    public function show($id, FigureRepository $repo, Request $request, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();
        $comment = new Comment();
        $comment->setCreateAt(new \DateTimeImmutable());
        $commentForm = $this->createForm(CommentType::class, $comment);
        $figure = $repo->find($id);

        $commentForm->handleRequest($request);
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $comment = $commentForm->getData();
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
        $user = $this->getUser();
        $figure = $figureRepository->find($request->get('id'));
        $manager->remove($figure);
        $manager->flush();

        $this->addFlash('sucess', 'la figure a bien été supprimée');

        return $this->redirectToRoute("home");
    }
}
