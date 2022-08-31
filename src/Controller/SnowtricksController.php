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
    // #[Route('/', name: 'home')]
    // public function index(FigureRepository $figure, CategoryRepository $cat, Request $request, PaginatorInterface $paginator): Response   //getDoctrine deprecied use repository in parametre
    // {
    //     $articles = $figure->findAll();

    //     $figures = $paginator->paginate(
    //         $articles,
    //         $request->query->getInt('page, 1'),
    //         4
    //     );

    //     $category = $cat->findAll();
    //     return $this->render('snowtricks/index.html.twig', [
    //         'controller_name' => 'SnowtricksController',
    //         'figures' => $figures,
    //         'category' => $category
    //     ]);
    // }
    #[Route('/', name: 'home')]
    public function index(FigureService $figureService, CategoryRepository $catRepo, Request $request, PaginatorInterface $paginator): Response   //getDoctrine deprecied use repository in parametre
    {
        return $this->render('snowtricks/index.html.twig', [
            'controller_name' => 'SnowtricksController',
            'figures' => $figureService->getPaginatedFigures(),
            'category' => $catRepo->findAll()
        ]);
    }

    #[Route('/snowtricks/addfigure', name: 'add_figure')]
    public function addFigure(Figure $figure = null, EntityManagerInterface $manager, Request $request): Response
    {
        $figure = new Figure();
        $video = new Video();
        $user = $this->getUser();
        $form = $this->createForm(FigureFormType::class, $figure);
        $formvideo = $this->createForm(VideoFormType::class, $video);//ajout
        $form->handleRequest($request);
        $formvideo->handleRequest($request);//ajout
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

            $videoframe = $formvideo->get('frame')->getData();
            $video->getframe($videoframe);
            $figure->setUser($user);
            $figure = $form->getData();
            $manager->persist($figure);
            $manager->flush();
            $video->setFigure($figure);
            $manager->persist($video);
            $manager->flush();
            $this->addFlash('success', 'Figure enregistrée');
            return $this->redirectToRoute('add_figure');
        }

        return $this->render('snowtricks/createFigure.html.twig', [
            'formCreateFigure' => $form->createView(),
            'formCreateVideo' => $formvideo->createView()
             ]);
    }

    #[Route('/snowtricks/{id}/edit', name: 'edit_figure')]
    public function editFigure($id, Figure $figure = null, FigureRepository $repo, EntityManagerInterface $manager, Request $request): Response
    {
        $figure = $repo->find($id);
        $video = new Video();
        $user = $this->getUser();
        $figure->setUser($user);
        $form = $this->createForm(FigureFormType::class, $figure);
        $formvideo = $this->createForm(VideoFormType::class, $video);//ajout
        $form->handleRequest($request);
        $formvideo->handleRequest($request);//ajout
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
            $videoframe = $formvideo->get('frame')->getData();
            $video->getframe($videoframe);
            $figure = $form->getData();
            $manager->persist($figure);
            $manager->flush();
            $video->setFigure($figure);
            $manager->persist($video);
            $manager->flush();

            $this->addFlash('success', 'Figure enregistrée');
            return $this->redirectToRoute('add_figure');
        }

        return $this->render('snowtricks/edit.html.twig', [
            'figure' => $figure,
            'formFigure' => $form->createView(),
            'formCreateVideo' => $formvideo->createView()
             ]);
    }

    #[Route('/snowtricks/{id}-{slug}', name: 'figure_show')]
    public function show($id, FigureRepository $repo, Request $request, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();
        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment);
        $figure = $repo->find($id);
        /** @var Figure $figure */
        $commentForm->handleRequest($request);
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $comment = $commentForm->getData();
            $comment->setUser($user);
            $comment->setCreateAt(new \DateTimeImmutable());
            $figure->addComment($comment);
            $manager->persist($figure);
            $manager->persist($comment);
            $manager->flush();
            $this->addFlash('success', 'Votre commentaire a été enregistré');
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
    #[Route('/supprime/video/{id}', name: 'figure_delete_video')]
    public function deletevideo(Video $videoname, VideoRepository $video, Request $request, EntityManagerInterface $em)
    {
        $nom = $videoname->getFrame();
        if ($nom) {
            $supVideo = $video->find($request->get('id'));
            $em->remove($supVideo);
            $em->flush();
            $this->addFlash('sucess', "la vidéo a été supprimée");
            return $this->redirectToRoute("home");
        }
    }
}
