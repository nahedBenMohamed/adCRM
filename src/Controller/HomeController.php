<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Financier;
use App\Entity\Formation;
use App\Entity\Link;
use App\Entity\Log;
use App\Entity\Trainee;
use App\Entity\TraineeFormation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use http\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{

    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        if (in_array('ROLE_TEACHER', $this->getUser()->getRoles(), true)) {
            return $this->redirectToRoute('app_home_trainer');
        }
        //Formation créée mais convocation pas encore envoyée
        $nbFormationsBrouillons = 0;
        //Convocation envoyée mais date < date de début de formation
        $nbFormationsAmont = 0;
        //Convocation envoyée et date de début de formation < today < date de fin de formation
        $nbFormationsEnCours = 0;
        //Convocation envoyée et date de fin de formation < today
        $nbFormationsAval = 0;
        //Convocation envoyée et date de fin de formation + 6 mois < today
        $nbFormationsArchivees = 0;

        $allFormations = $entityManager->getRepository(Formation::class)->findBy([],['id' => 'DESC']);
        foreach ($allFormations as $course) {
            if ($course->getStatus() == 0) {
                $nbFormationsBrouillons++;
            }
            if ($course->getStatus() == 1) {
                $fill1 = 0;
                $fill2 = 0;
                $fill3 = 0;
                $fill4 = 0;
                $updatedDatEnd = $course->getDateFinFormation()->modify('+3 month');
                $date = new \DateTime();
                $currentDate = $date->format('Y-m-d');
               if ($updatedDatEnd->format('Y-m-d')  <  $currentDate) {
                   $fill4 = 1;
               }
                if($course->getDateFinFormation()->format('Y-m-d') <  $currentDate) {
                    $fill3 = 1;
                }

                if ($currentDate > $course->getDateDebutFormation()->format('Y-m-d') and $currentDate < $course->getDateFinFormation()->format('Y-m-d')) {
                    $fill2 = 1;
                }
                if ($currentDate < $course->getDateDebutFormation()->format('Y-m-d')) {
                    $fill1 = 1;
                }
                if($fill4) {
                    $nbFormationsArchivees++;
                }
                if($fill3) {
                    $nbFormationsAval ++;
                }
                if($fill2) {
                    $nbFormationsEnCours ++;
                }
                if($fill1) {
                    $nbFormationsAmont ++;
                }
            }
         }
        $log = $entityManager->getRepository(Log::class)->findBy([],['id' => 'DESC'], 10);
        $allTraineeConvo = $entityManager->getRepository(TraineeFormation::class)->findAllTraineeByDate();
        $datesTab= [];
        $valTab = [];
        foreach ($allTraineeConvo as $key => $trainee ) {
            if ($trainee['dateConvocation'] != null) {
                array_push($datesTab, $trainee['dateConvocation']->format('Y-m-d'));
                array_push($valTab, $trainee['total']);
            }
        }

        $nbTeachers = count($entityManager->getRepository(User::class)->findUsers('ROLE_TEACHER'));
        $nbLink = count($entityManager->getRepository(Link::class)->findAll());
        $nbContactClient = count($entityManager->getRepository(Customer::class)->findAll());
        $nbFinanceur = count($entityManager->getRepository(Financier::class)->findAll());
        return $this->render('views/dashboard.html.twig', [
            'controller_name' => 'HomeController',
            'nbFormationsBrouillons' => $nbFormationsBrouillons,
            'nbFormationsAmont' => $nbFormationsAmont,
            'nbFormationsEnCours' => $nbFormationsEnCours,
            'nbFormationsAval' => $nbFormationsAval,
            'nbFormationsArchivees' => $nbFormationsArchivees,
            'nbTotale' => count($allFormations),
            'log' => $log,
            'datesTab' => $datesTab,
            'valTab' => $valTab,
            'nbLink' => $nbLink,
            'nbContactClient' => $nbContactClient,
            'nbFinanceur' => $nbFinanceur,
            'nbTeachers' => $nbTeachers
        ]);
    }


    #[Route('/dashboard', name: 'app_home_trainer')]
    public function indexTrainer(EntityManagerInterface $entityManager): Response
    {
        //Formation créée mais convocation pas encore envoyée
        $nbFormationsBrouillons = 0;
        //Convocation envoyée mais date < date de début de formation
        $nbFormationsAmont = 0;
        //Convocation envoyée et date de début de formation < today < date de fin de formation
        $nbFormationsEnCours = 0;
        //Convocation envoyée et date de fin de formation < today
        $nbFormationsAval = 0;
        //Convocation envoyée et date de fin de formation + 6 mois < today
        $nbFormationsArchivees = 0;

        $allFormations = $entityManager->getRepository(Formation::class)->findBy([],['id' => 'DESC']);
        foreach ($allFormations as $course) {
            if ($course->getStatus() == 0) {
                $nbFormationsBrouillons++;
            }
            if ($course->getStatus() == 1) {
                $fill1 = 0;
                $fill2 = 0;
                $fill3 = 0;
                $fill4 = 0;
                $updatedDatEnd = $course->getDateFinFormation()->modify('+3 month');
                $date = new \DateTime();
                $currentDate = $date->format('Y-m-d');
                if ($updatedDatEnd->format('Y-m-d')  <  $currentDate) {
                    $fill4 = 1;
                }
                if($course->getDateFinFormation()->format('Y-m-d') <  $currentDate) {
                    $fill3 = 1;
                }

                if ($currentDate > $course->getDateDebutFormation()->format('Y-m-d') and $currentDate < $course->getDateFinFormation()->format('Y-m-d')) {
                    $fill2 = 1;
                }
                if ($currentDate < $course->getDateDebutFormation()->format('Y-m-d')) {
                    $fill1 = 1;
                }
                if($fill4) {
                    $nbFormationsArchivees++;
                }
                if($fill3) {
                    $nbFormationsAval ++;
                }
                if($fill2) {
                    $nbFormationsEnCours ++;
                }
                if($fill1) {
                    $nbFormationsAmont ++;
                }
            }
        }
        $log = $entityManager->getRepository(Log::class)->findBy([],['id' => 'DESC'], 10);
        $allTraineeConvo = $entityManager->getRepository(TraineeFormation::class)->findAllTraineeByDate();
        $datesTab= [];
        $valTab = [];
        foreach ($allTraineeConvo as $key => $trainee ) {
            if ($trainee['dateConvocation'] != null) {
                array_push($datesTab, $trainee['dateConvocation']->format('Y-m-d'));
                array_push($valTab, $trainee['total']);
            }
        }

        $nbTeachers = count($entityManager->getRepository(User::class)->findUsers('ROLE_TEACHER'));
        $nbLink = count($entityManager->getRepository(Link::class)->findAll());
        $nbContactClient = count($entityManager->getRepository(Customer::class)->findAll());
        $nbFinanceur = count($entityManager->getRepository(Financier::class)->findAll());
        return $this->render('views/dashboard-trainer.html.twig', [
            'controller_name' => 'HomeController',
            'nbFormationsBrouillons' => $nbFormationsBrouillons,
            'nbFormationsAmont' => $nbFormationsAmont,
            'nbFormationsEnCours' => $nbFormationsEnCours,
            'nbFormationsAval' => $nbFormationsAval,
            'nbFormationsArchivees' => $nbFormationsArchivees,
            'nbTotale' => count($allFormations),
            'log' => $log,
            'datesTab' => $datesTab,
            'valTab' => $valTab,
            'nbLink' => $nbLink,
            'nbContactClient' => $nbContactClient,
            'nbFinanceur' => $nbFinanceur,
            'nbTeachers' => $nbTeachers
        ]);
    }

}
