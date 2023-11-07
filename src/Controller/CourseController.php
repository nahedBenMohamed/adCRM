<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\FormationUser;
use App\Entity\User;
use App\Form\FormationFormType;
use App\Form\TraineeFormationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CourseController extends AbstractController
{
  

    #[Route('/courses', name: 'app_courses')]
    public function viewCourses(EntityManagerInterface $entityManager): Response
    {
        $courses = $entityManager->getRepository(Formation::class)->findAll();
        return $this->render('courses/index.html.twig', [
            'courses' => $courses
        ]);

    }

    #[Route('/courses/add', name: 'app_courses_add')]
    public function addCourse(Request $request, EntityManagerInterface $entityManager): Response
    {
        $course = new Formation();
        $form = $this->createForm(FormationFormType::class, $course);
        $form->handleRequest($request);
        $formationUser = new FormationUser();
        $formTrainee = $this->createForm(TraineeFormationFormType::class, $formationUser);
        $formTrainee->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($course);
            $entityManager->flush();
    
            if ($request->request->has('proceed')) {
                return new JsonResponse(['success' => true]);
            }
        }
    
        return $this->render('courses/add.html.twig', [
            'formationForm' => $form->createView(),
            'formationUserForm' => $formTrainee->createView(),
        ]);
    }
    #[Route('/courses/create-formation-users', name: 'app_courses_add_trainees')]
    public function createFormationUsers(Request $request, EntityManagerInterface $entityManager)
        {
            $data = json_decode($request->getContent(), true);

            foreach ($data['selectedUserIds'] as $userId) {
                $formationUser = new FormationUser();
                $formationUser->setFormation($entityManager->getReference(Formation::class, $data['formationId']));
                $formationUser->setUser($entityManager->getReference(User::class, $userId));

                $entityManager->persist($formationUser);
            }

            $entityManager->flush();

            return new JsonResponse(['message' => 'FormationUser records created']);
        }
}
