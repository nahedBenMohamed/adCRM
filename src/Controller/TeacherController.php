<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\Link;
use App\Entity\Trainee;
use App\Entity\TraineeFormation;
use App\Form\FormationFormType;
use App\Form\FormationInfoFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class TeacherController extends AbstractController
{
    #[Route('/trainer', name: 'app_trainer')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $trainer = $this->getUser();
        $courses = $entityManager->getRepository(Formation::class)->findBy(['formateur' => $trainer],['id' => 'DESC']);
        return $this->render('teacher/index.html.twig', [
            'courses' => $courses
        ]);
    }

    #[Route('/documentsTrainer', name: 'app_trainer_documents')]
    public function documentsTrainer(EntityManagerInterface $entityManager): Response
    {
        $trainer = $this->getUser();
        $courses = $entityManager->getRepository(Formation::class)->findBy(['formateur' => $trainer],['id' => 'DESC']);
        return $this->render('teacher/documents.html.twig', [
            'courses' => $courses
        ]);
    }

    #[Route('/documentsAdmin', name: 'app_admin_documents')]
    public function documentsAdmin(EntityManagerInterface $entityManager): Response
    {
        $trainer = $this->getUser();
        $courses = $entityManager->getRepository(Formation::class)->findBy(['formateur' => $trainer],['id' => 'DESC']);
        return $this->render('views/documents.html.twig', [
            'courses' => $courses
        ]);
    }
}
