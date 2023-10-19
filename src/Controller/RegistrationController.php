<?php

namespace App\Controller;

use App\Entity\Employeur;
use App\Entity\Formateur;
use App\Entity\Gestionnaire;
use App\Entity\Stagiaire;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register/gestionnaire', name: 'app_register_gestionnaire')]
    public function registerGestionnaire(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, AppAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        $user = new Gestionnaire();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
             // Créer une instance de l'entité User avec les données du formulaire
            $user = $form->getData();

            // Définir le rôle de l'utilisateur en tant qu'utilisateur
            $user->setRoles(['ROLE_SUPER_ADMIN']);
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email
            return $this->redirectToRoute('vue_gestionnaire');
        }
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
    #[Route('/register/formateur', name: 'app_register_formateur')]
    public function registerFormateur(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, AppAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        $user = new Formateur();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
             // Créer une instance de l'entité User avec les données du formulaire
            $user = $form->getData();

            // Définir le rôle de l'utilisateur en tant qu'utilisateur
            $user->setRoles(['ROLE_ALLOWED_TO_SWITCH']);
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email
            return $this->redirectToRoute('vue_formateur');
        }
        return $this->render('registration/app_register_formateur.html.twig', [
            'registrationFormFormateur' => $form->createView(),
        ]);
    }
    #[Route('/register/employeur', name: 'app_register_employeur')]
    public function registerEmployeur(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, AppAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        $user = new Employeur();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
             // Créer une instance de l'entité User avec les données du formulaire
            $user = $form->getData();

            // Définir le rôle de l'utilisateur en tant qu'utilisateur
            $user->setRoles(['ROLE_ADMIN']);
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email
            return $this->redirectToRoute('app_home');
        }
        return $this->render('registration/app_register_employeur.html.twig', [
            'registrationFormEmployeur' => $form->createView(),
        ]);
    }
    #[Route('/register/stagiaire', name: 'app_register_stagiaire')]
    public function registerStagiaire(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, AppAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        $user = new Stagiaire();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
             // Créer une instance de l'entité User avec les données du formulaire
            $user = $form->getData();

            // Définir le rôle de l'utilisateur en tant qu'utilisateur
            $user->setRoles(['ROLE_USER']);
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email
            return $this->redirectToRoute('app_home');
        }
        return $this->render('registration/app_register_stagiaire.html.twig', [
            'registrationFormStagiaire' => $form->createView(),
        ]);
    }
}
