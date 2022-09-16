<?php

namespace App\Controller;

use App\Entity\Video;
use App\Entity\Figure;
use App\Entity\Comment;
use App\Entity\Pictures;
use App\Form\CommentType;
use App\Form\VideoFormType;
use App\Form\FigureFormType;
use App\Service\FigureService;
use App\Service\CommentService;
use App\Repository\VideoRepository;
use App\Repository\FigureRepository;
use App\Repository\CategoryRepository;
use App\Repository\PicturesRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface; //remplace ObjectManager
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SnowtricksController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(FigureService $figureService, CategoryRepository $catRepo): Response   //getDoctrine deprecied use repository in parametre
    {
        $limit = 10;

        return $this->render('snowtricks/index.html.twig', [
            'controller_name' => 'SnowtricksController',
            'figures' => $figureService->getPaginatedFigures($limit),
            'category' => $catRepo->findAll()
        ]);
    }



    #[Route('/snowtricks/addfigure', name: 'add_figure')]
    #[Route('/snowtricks/{id}/edit', name: 'edit_figure')]
    public function addFigure(Figure $figure = null, FigureRepository $repo, EntityManagerInterface $manager, Request $request): Response
    {
        if (!$figure) {
            $figure = new Figure();
        }

        $video = new Video();
        $user = $this->getUser();
        $form = $this->createForm(FigureFormType::class, $figure);
        $formvideo = $this->createForm(VideoFormType::class, $video);//ajout
        $form->handleRequest($request);
        $formvideo->handleRequest($request);//recuperation des valeurs saisies
        if ($form->isSubmitted() && $form->isValid()) {
            if ($figure->getDateCreate() == null) {
                $figure->setDateCreate(new \DateTimeImmutable());
            } else {
                $figure->setDateEdit(new \DateTimeImmutable());
            }
            $figure = $form->getData();
            $pictures = $form->get('pictures')->getData();
            foreach ($pictures as $repo) {
                $file = md5(uniqid()).'.'.$repo->guessExtension();
                $repo->move( //copie image
                    $this->getParameter('images_directory'),
                    $file
                );
                $img = new Pictures();
                $img->setName($file);
                $figure->addPicture($img);
                $manager->persist($img);
            }

            $videoframe = $formvideo->get('frame')->getData();
            $video->setframe($videoframe);
            $figure->setUser($user);
            $figure->addVideo($video);
            $manager->persist($figure);
            $manager->persist($video);
            $manager->flush();
            if ($figure->getId() != null) {
                $this->addFlash('success', 'Figure modifiée');
                return $this->redirectToRoute('home');
            } else {
                $this->addFlash('success', 'Figure enregistrée');
                return $this->redirectToRoute('add_figure');
            }
        }

        if ($figure->getId() == null) {
            return $this->render('snowtricks/createFigure.html.twig', [
            'formCreateFigure' => $form->createView(),
            'formCreateVideo' => $formvideo->createView()
             ]);
        }
        return $this->render('snowtricks/editer.html.twig', [
                    'figure' => $figure,
                    'formFigure' => $form->createView(),
                    'formCreateVideo' => $formvideo->createView()
                     ]);
    }



    #[Route('/snowtricks/{id}-{slug}', name: 'figure_show')]
    public function show($id, $slug, FigureRepository $repo, Request $request, EntityManagerInterface $manager, CommentService $commentService): Response
    {
        $user = $this->getUser();
        $limit = 10;

        $comment = new Comment();

        $commentForm = $this->createForm(CommentType::class, $comment);
        $figure = $repo->find($id);
        // $comment = $commentService->getPaginatedComments($limit);

        /** @var Figure $figure */
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $comment = $commentForm->getData();
            $comment->setUser($user);
            $comment->setCreateAt(new \DateTimeImmutable());
            $figure->addComment($comment);
            $manager->persist($comment);
            $manager->flush();
            $this->addFlash('success', 'Votre commentaire a été enregistré');
            return $this->redirectToRoute('figure_show', [
                'id'=> $id,
                'slug'=> $slug
            ]);
        }
        return $this->render('snowtricks/show.html.twig', [
            'figure' => $figure,
            'user' => $user,
            'commentForm' => $commentForm->createView(),
            'comments' => $commentService->getPaginatedComments($limit, $figure)



        ]);
    }

    #[Route('/delete/{id}', name: 'snowtrick_delete')]
    public function deletePost(Figure $figure, Request $request, FigureRepository $figureRepository, EntityManagerInterface $manager): Response  //RedirectResponse
    {
        $this->getUser();
        $figure = $figureRepository->find($request->get('id'));
        $manager->remove($figure);
        $manager->flush();

        $this->addFlash('success', 'la figure a bien été supprimée');

        return $this->redirectToRoute("home");
    }

    #[Route('/supprime/image/{id}', name: 'figure_delete_picture')]
    public function deletePicture($id, PicturesRepository $repo, EntityManagerInterface $em)
    {
        $picture = $repo->find($id);
        if ($picture == null) {
            $this->addFlash('danger', "l'image n'a pas été supprimée");
            return $this->redirectToRoute("home");
        }
        $nom = $picture->getName();

        if ($nom) {
            unlink($this->getParameter('images_directory').'/'.$nom);
        }
        $em->remove($picture);
        $em->flush();
        $this->addFlash('success', "l'image a été supprimée");
        return $this->redirectToRoute("home");
    }

    #[Route('/supprime/video/{id}', name: 'figure_delete_video')]
    public function deletevideo($id, Video $videoname, VideoRepository $video, EntityManagerInterface $em)
    {
        $nom = $videoname->getFrame();

        if ($nom) {
            $supVideo = $video->find($id);
            $em->remove($supVideo);
            $em->flush();
            $this->addFlash('dnager', "la vidéo a été supprimée");
            return $this->redirectToRoute("home");
        }
        $this->addFlash('sucess', "l'image n'a pas été supprimée");
        return $this->redirectToRoute("home");
    }
}
