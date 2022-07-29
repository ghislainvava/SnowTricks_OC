<?php

namespace App\Controller;

use App\Entity\User;

use App\Form\RegistrationType;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityController extends AbstractController
{
    #[Route('/administration', name:'administration')]
    public function administration(): Response
    {
        return $this->render('security/admin.html.twig');
    }

    #[Route('/inscription', name: 'security_registration')]
    public function registration(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $encoder, SendMailService $mail) : Response
    {
        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user=$form->getData();
            $password = $encoder->hashPassword($user, $user->getPassword());
            $user->setPassword($password);
            $manager->persist($user);
            $manager->flush();

            $mail->send(
                'no-replay@monsite.net',
                $user->getEmail(),
                'Activation du compte SnowStrick',
                'register',
                compact('user')
            );

            return $this->redirectToRoute('security_login');
        }

        return $this->render('security/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/connection', name:"security_login", methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastusername();
        if ($this->getUser()) {
            return $this->redirectToRoute(route: 'home');
        }

        return $this->render('security/login.html.twig', [
            'last_username'=> $lastUsername,
            'error'        => $error,
        ]);
    }

    #[Route('/deconnection', name:"security_logout")]
    public function logout(): void
    {
        throw new \LogicException(" erreur veuillez renouvellez l'opération");
    }
}
