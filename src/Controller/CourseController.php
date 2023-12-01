<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\Trainee;
use App\Entity\TraineeFormation;
use App\Entity\User;
use App\Form\FormationFormType;
use App\Form\FormationInfoFormType;
use App\Form\TraineeFormationFormType;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CourseController extends AbstractController
{
  

    #[Route('/courses', name: 'app_courses')]
    public function viewCourses(EntityManagerInterface $entityManager): Response
    {
        $courses = $entityManager->getRepository(Formation::class)->findBy([],['id' => 'DESC']);
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
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($course);
            $entityManager->flush();
            return $this->redirectToRoute('app_courses_edit', ['id' => $course->getId()]);
        }
    
        return $this->render('courses/add.html.twig', [
            'formationForm' => $form->createView()
        ]);
    }

    #[Route('/courses/edit/{id}', name: 'app_courses_edit')]
    public function updateCourse(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        $course =  $entityManager->getRepository(Formation::class)->find($id);
        $form = $this->createForm(FormationFormType::class, $course);
        $form->handleRequest($request);
        $formInfo = $this->createForm(FormationInfoFormType::class, $course);
        $formInfo->handleRequest($request);
        $formationUser = $entityManager->getRepository(TraineeFormation::class)->findBy(['formation'=>$course]);
        $trainees = [];
        foreach ($formationUser as $item) {
            array_push($trainees, $item->getTrainee() );
        }
        if (($form->isSubmitted() && $form->isValid()) || ($formInfo->isSubmitted() && $formInfo->isValid())) {
            $entityManager->persist($course);
            $entityManager->flush();
            return $this->redirectToRoute('app_courses_edit', ['id' => $course->getId()]);
        }
        return $this->render('courses/edit.html.twig', [
            'formationForm' => $form->createView(),
            'formation' => $course,
            'trainees' => $trainees,
            'formInfo' => $formInfo->createView()

        ]);
    }

    #[Route('/courses/deleteTrainee/{idFormation}/{idTrainee}', name: 'app_trainee_formation_delete')]
    public function deleteTraineeFromFormation(Request $request, EntityManagerInterface $entityManager, $idFormation, $idTrainee ): Response
    {
        $formation = $entityManager->getRepository(Formation::class)->findOneBy(['id'=> $idFormation]);
        $trainee =  $entityManager->getRepository(Trainee::class)->findOneBy(['id' => $idTrainee]);
        $traineeToDelete = $entityManager->getRepository(TraineeFormation::class)->findOneBy(['formation' => $formation, 'trainee' =>$trainee]);
        $entityManager->remove($traineeToDelete);
        $entityManager->flush();
        return $this->redirectToRoute('app_courses_edit', ['id' => $idFormation]);

    }

    #[Route('/courses/sendConvocation/{idFormation}/{idTrainee}', name: 'app_trainee_send_conv')]
    public function sendConvToTrainee(Request $request, EntityManagerInterface $entityManager,MailerInterface $mailer, $idFormation, $idTrainee ): Response
    {
        $formation = $entityManager->getRepository(Formation::class)->findOneBy(['id'=> $idFormation]);
        $trainee =  $entityManager->getRepository(Trainee::class)->findOneBy(['id' => $idTrainee]);
        $traineesFormation =  $entityManager->getRepository(TraineeFormation::class)->findOneBy(['formation' => $formation, 'trainee' => $trainee]);
        $this->generate_pdf($formation,$trainee);
        $html = $this->renderView('emails/convocation.html.twig', [
            'dataF' => $formation,
            'dataS' => $trainee
        ]);
        $email = (new Email())
            ->from('nahedbenmohamed57@gmail.com')
            ->text('Bonjour, voici le doc de convocation')
            ->html($html)
            ->to($trainee->getEmail());
            //->attachFromPath('documents/convocations/convocation_'.$formation->getId().'_'.$trainee->getId().'.pdf');
        $mailer->send($email);
        $traineesFormation->setSendConvocation(true);
        $entityManager->persist($traineesFormation);
        $entityManager->flush();
        return $this->redirectToRoute('app_courses_edit', ['id' => $idFormation]);
    }

    #[Route('/courses/sendConvocation/{idFormation}', name: 'app_allTrainees_send_conv')]
    public function sendConvToAllTrainees(Request $request, EntityManagerInterface $entityManager,MailerInterface $mailer, $idFormation ): Response
    {
        $formation = $entityManager->getRepository(Formation::class)->findOneBy(['id'=> $idFormation]);
        $traineesFormation =  $entityManager->getRepository(TraineeFormation::class)->findBy(['formation' => $formation]);
        foreach ($traineesFormation as $item) {
            $trainee = $item->getTrainee();
            $this->generate_pdf($formation, $trainee);
            $html = $this->renderView('emails/convocation.html.twig', [
                'dataF' => $formation,
                'dataS' => $trainee
            ]);
            $email = (new Email())
                ->from('nahedbenmohamed57@gmail.com')
                ->text('Bonjour, voici le doc de convocation')
                ->html($html)
                ->to($trainee->getEmail());
                //->attachFromPath('documents/convocations/convocation_'.$formation->getId().'_'.$trainee->getId().'.pdf');
             $mailer->send($email);

            $item->setSendConvocation(true);
            $entityManager->persist($item);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_courses_edit', ['id' => $idFormation]);
    }

    /**
     * this function generate pdf for the given trainee and the given formation and save it in public/pdf
     * @param $formation
     * @param $trainee
     * @return void
     */
    public function generate_pdf($formation, $trainee){

        $options = new Options();
        $options->set('defaultFont', 'Roboto');
        $dompdf = new Dompdf($options);
        $html = $this->renderView('emails/convocation.html.twig', [
            'dataF' => $formation,
            'dataS' => $trainee
        ]);
        $dompdf->loadHtml($html);
        $dompdf->render();
        $output = $dompdf->output();
        file_put_contents('documents/convocations/convocation_'.$formation->getId().'_'.$trainee->getId().'.pdf', $output);
    }
}
