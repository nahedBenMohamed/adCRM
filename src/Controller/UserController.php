<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\Trainee;
use App\Entity\TraineeFormation;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\TraineeFormType;
use App\Form\UpdateUserFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Doctrine\ORM\EntityManagerInterface;

class UserController extends AbstractController
{
    /** User CRUD */
    #[Route('/user', name: 'app_user')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager->getRepository(User::class)->findUsers("ROLE_SUPER_ADMIN");
        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/user/add', name: 'app_add_user')]
    public function addUser(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
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
                    $form->get('password')->getData()
                )
            );
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email
            return $this->redirectToRoute('app_user');
        }
        return $this->render('user/new.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/user/profile/{id}', name: 'app_edit_user')]
    public function updateUserProfile(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        if($id) {
            $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $id]);
        } else {
            $user = $this->getUser();
        }
        $form = $this->createForm(UpdateUserFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email
            return $this->redirectToRoute('app_user');
        }
        return $this->render('user/update.html.twig', [
            'setUserForm' => $form->createView(),
        ]);
    }
    /** Trainees CRUD */
    #[Route('/user/trainees', name: 'app_trainees')]
    public function listOfTrainees(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager->getRepository(Trainee::class)->findAll();
        return $this->render('trainees/trainees.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/user/addTrainee/{formationId}', name: 'app_add_trainee')]
    public function addTrainees(Request $request, EntityManagerInterface $entityManager, $formationId = null): Response
    {
        if($formationId) {
            $formation  = $entityManager->getRepository(Formation::class)->findOneBy(['id'=> $formationId]);
        } else {
            $formation = new Formation();
        }
        $user = new Trainee();
        $form = $this->createForm(TraineeFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $entityManager->persist($user);
            $entityManager->flush();

           if ($request->request->get('formation_id')) {
                $TraineeFormation = new TraineeFormation();
                $TraineeFormation->setTrainee($user);
                $TraineeFormation->setFormation($formation);
                $entityManager->persist($TraineeFormation);
                $entityManager->flush();
            }

            if ($formationId) {
                return $this->redirectToRoute('app_add_trainee', ['formationId' => $formationId]);
            } else {
                return $this->redirectToRoute('app_trainees');
            }
        }
        return $this->render('trainees/new_trainee.html.twig', [
            'registrationForm' => $form->createView(),
            'formation' => $formation
        ]);
    }
    /** Teacher CRUD */
    #[Route('/user/teachers', name: 'app_teachers')]
    public function listOfTeacher(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager->getRepository(User::class)->findUsers('ROLE_TEACHER');
        return $this->render('user/teachers.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/user/addTeacher', name: 'app_add_teacher')]
    public function addTeacher(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UpdateUserFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            //set default password for Trainees
            $user->setPassword('00000000');

            $user->setRoles(['ROLE_TEACHER']);
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('app_teachers');
        }
        return $this->render('user/new_teacher.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/trainee/edit/{id}', name: 'app_edit_user')]
    public function updateTrainee(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        $user = $entityManager->getRepository(Trainee::class)->findOneBy(['id' => $id]);
        $form = $this->createForm(TraineeFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email
            return $this->redirectToRoute('app_trainees');
        }
        return $this->render('trainees/update_trainee.html.twig', [
            'setTraineeForm' => $form->createView(),
        ]);
    }
}
