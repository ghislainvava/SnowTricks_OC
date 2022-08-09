<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ResetFormType;
use App\Service\JWTService;
use App\Form\RegistrationType;
use App\Service\SendMailService;
use App\Repository\UserRepository;
use App\Form\ResetPasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class SecurityController extends AbstractController
{
    #[Route('/administration', name:'administration')]
    public function administration(): Response
    {
        return $this->render('security/admin.html.twig');
    }

    #[Route('/inscription', name: 'security_registration')]
    public function registration(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $encoder, SendMailService $mail, JWTService $jwt): Response
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
            //On génére le JWT à partir de JWTService
            $header = [
                'typ' => 'JWT',
                'alg' => 'HS256'
            ];
            $payload = [
                'id' => $user->getId()
            ];
            //app.jwtsecret est dans config services.yaml
            $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));
            $mail->send(
                'no-reply@monsite.net',
                $user->getEmail(),
                'Activation du compte SnowTrick',
                'register',
                compact('user', 'token')
                //[ 'user => $user] alternative d'écriture
            );

            return $this->redirectToRoute('security_login');
        }
        return $this->render('security/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/verif/{token}', name:"verify_user")]
    public function verifyUser($token, JWTService $jwt, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        if ($jwt->isValidToken($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret'))) {
            $payload = $jwt->getPayload($token);
            $user = $userRepository->find($payload['id']);
            if ($user && !$user->getIsVerify()) {
                $user->setIsVerify(true);
                //$user->setRoles(["ROLE_USER"]);
                $em->flush($user);
                $this->addFlash('sucess', 'Utilisateur activé');

                return $this->redirectToRoute('home');
            }
        }
        $this->addFlash('danger', 'Le token est invalide ou a expiré');

        return $this->redirectToRoute('security_login');
    }

    #[Route('/renvoiverif', name: 'resend_verif')]
    public function resendVerif(JWTService $jwt, SendMailService $mail, UserRepository $userRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour accéder à cette page');
            return $this->redirectToRoute('security_login');
        }
        if ($user->getIsVerify()) {
            $this->addFlash('warning', 'Cet utilisateur est déjà activé');
            return $this->redirectToRoute('home');
        }

        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];
        //dd($user, $user->getId());
        $payload = [
            'id' => $user->getId()
        ];
        $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));
        $mail->send(
            'no-reply@monsite.net',
            $user->getEmail(),
            'Activation du compte SnowTrick',
            'register',
            compact('user', 'token')
            //[ 'user => $user] alternative d'écriture
        );
        $this->addFlash('success', 'Email de vérification envoyé');
        return $this->redirectToRoute('home');
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

    #[Route('/oubli-pass', name:'forgotten_password')]
    public function forgottenPassword(
        Request $request,
        UserRepository $userRepository,
        TokenGeneratorInterface $tokenGeneratorInterface,
        EntityManagerInterface $entityManager,
        SendMailService $mail
    ): Response {
        $form = $this->createForm(ResetPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {//methode données par handleRequest
            $user = $userRepository->findOneByEmail($form->get('email')->getData());
            if ($user) {
                //génération token de réinitialisation
                $token = $tokenGeneratorInterface->generateToken();
                $user->setResetToken($token);
                $entityManager->persist($user);
                $entityManager->flush();
                //génération lien réinitialisation
                $url = $this->generateUrl('reset_pass', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
                //pour envoi de mail
                $context = compact('url', 'user');
                $mail->send( //appel service mail
                    'no-reply@monsite.net',
                    $user->getEmail(),
                    'Réinitialisation du mot de passe',
                    'reset_password',
                    $context
                );
                $this->addFlash('sucess', 'Email envoyé');
                return $this->redirectToRoute('security_login');
            }
            $this->addFlash('secondary', 'Un problème est survenu');
            return $this->redirectToRoute('security_login');
        }

        return $this->render('security/reset_password_request.html.twig', [
            'requestPassForm' => $form->createView()
        ]);
    }

    #[Route('/oubli-pass/{token}', name: 'reset_pass')]
    public function resetPass(
        string $token,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $userRepository->findOneByResetToken($token);

        if ($user) {
            $form = $this->createForm(ResetFormType::class);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $user->setResetToken('');
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
                $em->persist($user);
                $em->flush();

                $this->addFlash('primary', 'Mot de passe changé avec succès');
                return $this->redirectToRoute('security_login');
            }

            return $this->render('security/reset_password.html.twig', [
                'passForm' => $form->createView()
            ]);
        }
        $this->addFlash('primary', 'Jeton invalide');
        return $this->redirectToRoute('security_login');
    }
}
