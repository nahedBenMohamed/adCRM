<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\Trainee;
use App\Entity\TraineeFormation;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\TraineeFormType;
use App\Form\UpdateUserFormType;
use Doctrine\DBAL\Types\TextType;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use App\Security\AppAuthenticator;
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
        $teacher = false;
        if (in_array('ROLE_TEACHER', $this->getUser()->getRoles(), true)) {
            $teacher = true;
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email
            if ($teacher) {
                return $this->redirectToRoute('app_trainer');
            }
            return $this->redirectToRoute('app_user');
        }
        return $this->render('user/update.html.twig', [
            'setUserForm' => $form->createView(),
            'teacher' => $teacher
        ]);
    }
    /** Trainees CRUD */
    #[Route('/user/trainees', name: 'app_trainees')]
    public function listOfTrainees(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager->getRepository(Trainee::class)->findBy([],['id' => 'DESC']);
        $isTeacher = false;
        if (in_array('ROLE_TEACHER', $this->getUser()->getRoles(), true)) {
            $isTeacher = true;
        }
        return $this->render('trainees/trainees.html.twig', [
            'users' => $users,
            'base_template' => $isTeacher ? 'baseTeacher.html.twig' : 'baseAdmin.html.twig'
        ]);
    }

    #[Route('/user/addTrainee/{formationId}', name: 'app_add_trainee')]
    public function addTrainees(Request $request, EntityManagerInterface $entityManager, $formationId = null): Response
    {
        $user = new Trainee();
        $form = $this->createForm(TraineeFormType::class, $user);
        $form->handleRequest($request);
        $type = "";
        if($formationId) {
            $formation = $entityManager->getRepository(Formation::class)->findOneBy(['id' => $formationId]);
            $type = $formation->getType();
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $entityManager->persist($user);
            $entityManager->flush();
            if($formationId) {
                $formation  = $entityManager->getRepository(Formation::class)->findOneBy(['id'=> $formationId]);
                $TraineeFormation = new TraineeFormation();
                $TraineeFormation->setTrainee($user);
                $TraineeFormation->setFormation($formation);
                $entityManager->persist($TraineeFormation);
                $entityManager->flush();
                return $this->redirectToRoute('app_courses_manage', ['idFormation' => $formationId, 'type' => $formation->getType()]);
            } else {
                return $this->redirectToRoute('app_trainees');
            }
        }
        $isTeacher = false;
        if (in_array('ROLE_TEACHER', $this->getUser()->getRoles(), true)) {
            $isTeacher = true;
        }
        return $this->render('trainees/new_trainee.html.twig', [
            'registrationForm' => $form->createView(),
            'formationId' => $formationId,
            'typeFormation' => $type,
            'base_template' => $isTeacher ? 'baseTeacher.html.twig' : 'baseAdmin.html.twig'
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
    public function addTeacher(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UpdateUserFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            //set default password for Trainees 00000000
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    '00000000'
                )
            );

            $user->setRoles(['ROLE_TEACHER']);
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('app_teachers');
        }
        return $this->render('user/new_teacher.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/trainee/edit/{id}/{idFormation}', name: 'app_edit_trainee')]
    public function updateTrainee(Request $request, EntityManagerInterface $entityManager, $id, $idFormation = null): Response
    {
        $user = $entityManager->getRepository(Trainee::class)->findOneBy(['id' => $id]);
        $form = $this->createForm(TraineeFormType::class, $user);
        $type = "";
        if($idFormation) {
            $formation = $entityManager->getRepository(Formation::class)->findOneBy(['id' => $idFormation]);
            $type = $formation->getType();
        }
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $entityManager->persist($user);
            $entityManager->flush();
            if ($idFormation !== null) {
                return $this->redirectToRoute('app_courses_manage', ['type' => $type, 'idFormation' => $idFormation]);
            }
            return $this->redirectToRoute('app_trainees');
        }
        $isTeacher = false;
        if (in_array('ROLE_TEACHER', $this->getUser()->getRoles(), true)) {
            $isTeacher = true;
        }
        return $this->render('trainees/update_trainee.html.twig', [
            'setTraineeForm' => $form->createView(),
            'typeFormation' => $type,
            'base_template' => $isTeacher ? 'baseTeacher.html.twig' : 'baseAdmin.html.twig'
        ]);
    }

    #[Route('/update-password', name: 'app_update_password')]
    public function updatePassword(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            if($email != "") {
                $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
                if ($user) {
                    //send mail to user with token
                    $link = 'https://adformation.online'.$this->generateUrl('app_update_user_password',['email'=>$email]);
                    $emailToSend = (new Email())
                        ->from('formation@adconseil.org')
                        ->subject('Modification de mot de passe')
                        ->html('<p>Bonjour, cliquer sur le lien pour modifier votre mot de passe:<br><a href="'.$link.'">'.$link.'</a></p>')
                        ->to($user->getEmail());
                    $mailer->send($emailToSend);
                    $this->addFlash('success', "Un email est envoyé à votre compte.");
                    return $this->redirectToRoute('app_update_password');
                } else {
                    // show error message
                    $this->addFlash('warning', "Cet email n'existe pas!");
                    return $this->redirectToRoute('app_update_password');
                }
            }
            return $this->redirectToRoute('app_update_password');
        }
        return $this->render('security/resetPassword.html.twig', [
        ]);
    }

    #[Route('/update-user-password/{email}', name: 'app_update_user_password')]
    public function updateUserPassword(Request $request, EntityManagerInterface $entityManager,UserPasswordHasherInterface $userPasswordHasher, $email): Response
    {
        if ($request->isMethod('POST')) {
            $password= $request->request->get('password');
            if($email != "" && $password != "") {
                $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
                if ($user) {
                    //update password
                    $user->setPassword(
                        $userPasswordHasher->hashPassword(
                            $user,
                            $password
                        )
                    );
                    $entityManager->persist($user);
                    $entityManager->flush();
                    $this->addFlash('success', "Votre mot de passe aura été changé avec succès");
                    return $this->redirectToRoute('app_login');
                } else {
                    // show error message
                    $this->addFlash('warning', "Le lien est incorrect");
                    return $this->redirectToRoute('app_update_user_password');
                }
            }
            return $this->redirectToRoute('app_login');
        }
        return $this->render('security/newPassword.html.twig', [
        ]);
    }
    #[Route('/trainee/delete/{id}', name: 'app_delete_trainee')]
    public function deleteTrainee(EntityManagerInterface $entityManager, $id): Response
    {
        $trainee = $entityManager->getRepository(Trainee::class)->findOneBy(['id' => $id]);
        $traineeFormation = $entityManager->getRepository(TraineeFormation::class)->findBy(['trainee' => $trainee]);
        //find if trainee is affected to formation
        $object = new \stdClass();
        if($traineeFormation) {
            foreach ($traineeFormation as $trfor) {
                 $entityManager->remove($trfor);
            }
            $entityManager->flush();
        }

        $entityManager->remove($trainee);
        $entityManager->flush();
        $object->status = true;
        $object->message = "Le stagiaire est supprimé avec succès";
        return new Response(json_encode($object));
    }
    #[Route('/teacher/delete/{id}', name: 'app_delete_teacher')]
    public function deleteTeacher(EntityManagerInterface $entityManager, $id): Response
    {
        $teacher = $entityManager->getRepository(User::class)->findOneBy(['id' => $id]);
        //find if trainee is affected to formation
        $teacherFormation = $entityManager->getRepository(Formation::class)->findOneBy(['formateur' => $teacher]);
        $object = new \stdClass();
        if ($teacherFormation) {
            $object->status = false;
            $object->message = "Ce formateur est enregistré dans une formation et il est impossible de le supprimer.";
        } else {
            $entityManager->remove($teacher);
            $entityManager->flush();
            $object->status = true;
            $object->message = "Le formateur est supprimé avec succès";
        }
        return new Response(json_encode($object));
    }

    #[Route('/user/delete/{id}', name: 'app_delete_user')]
    public function deleteUser(EntityManagerInterface $entityManager, $id): Response
    {
        $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $id]);
        $object = new \stdClass();
        $entityManager->remove($user);
        $entityManager->flush();
        $object->status = true;
        $object->message = "L'utilisateur est supprimé avec succès";
        return new Response(json_encode($object));
    }

    #[Route('/downloadTrainee', name: 'app_download_trainee')]
    public function downloadTrainee(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager->getRepository(Trainee::class)->findBy([],['id' => 'DESC']);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Nom');
        $sheet->setCellValue('B1', 'Prénom');
        $sheet->setCellValue('C1', 'Fonction');
        $sheet->setCellValue('D1', 'Email');
        $counter = 2;
        foreach ($users as $user) {
            $sheet->setCellValue('A' . $counter, $user->getFirstName());
            $sheet->setCellValue('B' . $counter, $user->getLastName());
            $sheet->setCellValue('C' . $counter, $user->getPosition());
            $sheet->setCellValue('D' . $counter, $user->getEmail());
            $counter++;
        }
        $writer = new Xls($spreadsheet);
        $response =  new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        $fileName = "ExportEmails_".date('m-d-Y_hia').".xls";
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . '"' . $fileName . '"');
        $response->headers->set('Cache-Control','max-age=0');
        return $response;
        //$this->addFlash('success', "Les stagiaires sont télechargées avec succès.");
       // return $this->redirectToRoute('app_trainees');
    }

    #[Route('/downloadTraineeByFormation/{idFormation}', name: 'app_download_trainee_by_formation')]
    public function downloadTraineeByFormation(EntityManagerInterface $entityManager, $idFormation = null): Response
    {
        $course =  $entityManager->getRepository(Formation::class)->find($idFormation);
        $formationUser = $entityManager->getRepository(TraineeFormation::class)->findBy(['formation' => $course]);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Nom');
        $sheet->setCellValue('B1', 'Prénom');
        $sheet->setCellValue('C1', 'Fonction');
        $sheet->setCellValue('D1', 'Email');
        $counter = 2;
        foreach ($formationUser as $item) {
            $sheet->setCellValue('A' . $counter, $item->getTrainee()->getFirstName());
            $sheet->setCellValue('B' . $counter, $item->getTrainee()->getLastName());
            $sheet->setCellValue('C' . $counter, $item->getTrainee()->getPosition());
            $sheet->setCellValue('D' . $counter, $item->getTrainee()->getEmail());
            $counter++;
        }
        $writer = new Xls($spreadsheet);
        $response =  new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        $fileName = "ExportEmails_".str_replace(' ','',$course->getNomFormation())."_".date('m-d-Y_hia').".xls";
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . '"' . $fileName . '"');
        $response->headers->set('Cache-Control','max-age=0');
        return $response;
        //$this->addFlash('success', "Les stagiaires sont télechargées avec succès.");
        // return $this->redirectToRoute('app_trainees');
    }

    #[Route('/user/profileAdmin/{id}', name: 'app_edit_admin')]
    public function updateAdminProfile(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        if($id) {
            $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $id]);
        } else {
            $user = $this->getUser();
        }
        $form = $this->createForm(UpdateUserFormType::class, $user);
        $form->handleRequest($request);
        $teacher = false;
        if (in_array('ROLE_TEACHER', $this->getUser()->getRoles(), true)) {
            $teacher = true;
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email
            if ($teacher) {
                return $this->redirectToRoute('app_trainer');
            }
            return $this->redirectToRoute('app_user');
        }
        return $this->render('user/update.html.twig', [
            'setUserForm' => $form->createView(),
            'teacher' => $teacher
        ]);
    }

    #[Route('/user/profileFormateur/{id}', name: 'app_edit_formateur')]
    public function updateFormateurProfile(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        if($id) {
            $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $id]);
        } else {
            $user = $this->getUser();
        }
        $form = $this->createForm(UpdateUserFormType::class, $user);
        $form->handleRequest($request);
        $teacher = false;
        if (in_array('ROLE_TEACHER', $this->getUser()->getRoles(), true)) {
            $teacher = true;
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email
            if ($teacher) {
                return $this->redirectToRoute('app_trainer');
            }
            return $this->redirectToRoute('app_user');
        }
        return $this->render('user/update.html.twig', [
            'setUserForm' => $form->createView(),
            'teacher' => $teacher
        ]);
    }

    #[Route('/user/loginTeacher/{id}', name: 'app_login_formateur')]
    public function loginTeacher(EntityManagerInterface $entityManager,
                                 UserAuthenticatorInterface $userAuthenticator,
                                 AppAuthenticator $authenticator,
                                 Request $request,
        $id): Response
    {
        if (in_array('ROLE_SUPER_ADMIN', $this->getUser()->getRoles(), true)) {
            $admin =$this->getUser();
            $request->getSession()->set('adminId', $admin->getId());
        }

        // Load the user by ID (assuming Doctrine)
        $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $id]);
        // Authenticate the user
        $userAuthenticator->authenticateUser($user, $authenticator, $request);

        // Redirect to homepage (or another route)
        if (in_array('ROLE_SUPER_ADMIN', $this->getUser()->getRoles(), true)) {
            return $this->redirectToRoute('app_home');
        } else {
            return $this->redirectToRoute('app_home_trainer');
        }

    }
}
