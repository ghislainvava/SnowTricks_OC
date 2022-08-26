<?php

namespace App\Controller;

use App\Entity\Figure;
use App\Entity\Comment;
use App\Entity\Pictures;
use App\Form\CommentType;
use App\Form\FigureFormType;
use PhpParser\Builder\Method;
use App\Repository\FigureRepository;
use App\Repository\CategoryRepository;
use App\Repository\PicturesRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $form = $this->createForm(FigureFormType::class, $figure);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $pictures = $form->get('pictures')->getData();
            foreach ($pictures as $picture) {
                $file = md5(uniqid()).'.'.$picture->guessExtension();
                $picture->move( //copie image
                    $this->getParameter('images_directory'),
                    $file
                );
                $img = new Pictures();
                $img->setName($file);
                $figure->addPicture($img);
            }
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
            $pictures = $form->get('pictures')->getData();
            foreach ($pictures as $picture) {
                $file = md5(uniqid()).'.'.$picture->guessExtension();
                $picture->move( //copie image
                    $this->getParameter('images_directory'),
                    $file
                );
                $img = new Pictures();
                $img->setName($file);
                $figure->addPicture($img);
            }

            $figure->setImage($fichier);
            $figure = $form->getData();
            $manager->persist($figure);
            $manager->flush();
            $this->addFlash('success', 'Figure enregistrée');
            return $this->redirectToRoute('add_figure');
        }

        return $this->render('snowtricks/edit.html.twig', [
            'figure' => $figure,
            'formFigure' => $form->createView()
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
        /** @var Figure $figure */
        $figure->addComment($comment);
        $commentForm->handleRequest($request);
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $comment = $commentForm->getData();
            $comment->setUser($user);
            $manager->persist($figure);
            $manager->persist($comment);
            $manager->flush();
        }
        return $this->render('snowtricks/show.html.twig', [
            'figure' => $figure,
            'comment' => $comment,
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

    #[Route('/supprime/image/{id}', name: 'figure_delete_picture')]
    public function deletePicture(Pictures $picturename, PicturesRepository $picture, Request $request, EntityManagerInterface $em)
    {
        $nom = $picturename->getName();
        if ($nom) {
            unlink($this->getParameter('images_directory').'/'.$nom);
            $picture = $picture->find($request->get('id'));
            $em->remove($picture);
            $em->flush();
            $this->addFlash('sucess', "l'image a été supprimée");
            return $this->redirectToRoute("home");
        }
    }
}
