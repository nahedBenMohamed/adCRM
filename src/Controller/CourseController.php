<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\Trainee;
use App\Entity\TraineeFormation;
use App\Entity\User;
use App\Form\FormationFormType;
use App\Form\FormationInfoFormType;
use App\Form\TraineeFormationFormType;
use App\Form\TraineeFormType;
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
        $form = $this->createForm(FormationFormType::class, $course, ['allow_extra_fields' =>true]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $course = $form->getData();
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
        $form = $this->createForm(FormationFormType::class, $course, ['allow_extra_fields' =>true]);
        $form->handleRequest($request);
        $formInfo = $this->createForm(FormationInfoFormType::class, $course, ['allow_extra_fields' =>true]);
        $formInfo->handleRequest($request);
        $formationUser = $entityManager->getRepository(TraineeFormation::class)->findBy(['formation'=>$course]);
        $trainees = [];
        $AllTrainees = $entityManager->getRepository(Trainee::class)->findBy([],['id' => 'DESC']);
        foreach ($formationUser as $item) {
            array_push($trainees, $item->getTrainee() );
        }
        if (($form->isSubmitted() && $form->isValid()) || ($formInfo->isSubmitted() && $formInfo->isValid())) {
            $course = $form->getData();
            $entityManager->persist($course);
            $entityManager->flush();
            if ($formInfo->isSubmitted() && $formInfo->isValid()) {
                $course = $formInfo->getData();
                $entityManager->persist($course);
                $entityManager->flush();
            }
            return $this->redirectToRoute('app_courses_edit', ['id' => $course->getId()]);
        }
        return $this->render('courses/edit.html.twig', [
            'formationForm' => $form->createView(),
            'formation' => $course,
            'trainees' => $trainees,
            'formInfo' => $formInfo->createView(),
            'allTrainees' => $AllTrainees

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
            ->from('formation@adconseil.org')
            ->subject('Convocation à la formation '.$formation->getNomFormation())
            ->html($html)
            ->to($trainee->getEmail());
        $mailer->send($email);
        $traineesFormation->setSendConvocation(true);
        $entityManager->persist($traineesFormation);
        $entityManager->flush();
        $this->addFlash('success', "La convocation a été envoyée avec succès.");
        return $this->redirectToRoute('app_courses_edit', ['id' => $idFormation]);
    }

    #[Route('/courses/sendConvocation/{idFormation}', name: 'app_allTrainees_send_conv')]
    public function sendConvToAllTrainees(Request $request, EntityManagerInterface $entityManager,MailerInterface $mailer, $idFormation ): Response
    {
        $formation = $entityManager->getRepository(Formation::class)->findOneBy(['id'=> $idFormation]);
        $traineesFormation =  $entityManager->getRepository(TraineeFormation::class)->findBy(['formation' => $formation]);
        $listOfTrainees = [];
        foreach ($traineesFormation as $item) {
            $trainee = $item->getTrainee();
            $listOfTrainees[] = $trainee;
            $this->generate_pdf($formation, $trainee);
            $html = $this->renderView('emails/convocation.html.twig', [
                'dataF' => $formation,
                'dataS' => $trainee
            ]);
            $email = (new Email())
                ->from('formation@adconseil.org')
                ->subject('Convocation à la formation '.$formation->getNomFormation())
                ->html($html)
                ->to($trainee->getEmail());
            $mailer->send($email);

            $item->setSendConvocation(true);
            $entityManager->persist($item);
            $entityManager->flush();
        }
        $traineer = $formation->getFormateur();
        if($traineer) {
            $html2 = $this->renderView('emails/convocation.html.twig', [
                'dataF' => $formation,
                'dataS' => $traineesFormation[0]
            ]);
            //send copy stag mail to adconseil
            $emailAdmin = (new Email())
                ->from('formation@adconseil.org')
                ->subject('Convocation à la formation '.$formation->getNomFormation())
                ->html($html2)
                ->to('formation@adconseil.org');
            $mailer->send($emailAdmin);
            //send specific mail to Traineer
            $htmlRecap = $this->renderView('emails/convocation_traineer.html.twig', [
                'dataF' => $formation,
                'dataS' => $traineesFormation[0],
                'traineesFormation' => $listOfTrainees
            ]);
            $emailTraineer = (new Email())
                ->from('formation@adconseil.org')
                ->subject("Récap’ formation ".$formation->getNomFormation())
                ->html($htmlRecap)
                ->cc('formation@adconseil.org')
                ->to($traineer->getEmail());
            $mailer->send($emailTraineer);
            //send specific mail to contact client
            $client = $formation->getCustomer();
            if($client) {
                $htmlClient = $this->renderView('emails/convocation_client.html.twig', [
                    'dataF' => $formation,
                    'dataS' => $traineesFormation[0],
                    'traineesFormation' => $listOfTrainees
                ]);
                $emailClient = (new Email())
                    ->from('formation@adconseil.org')
                    ->subject('Convocationde vos apprenant.es à la formation '.$formation->getNomFormation())
                    ->html($htmlClient)
                    ->cc('nahedbenmohamed57@gmail.com')
                    ->to($client->getEmail());
                $mailer->send($emailClient);
            }
        }


        $this->addFlash('success', "Les convocations ont bien été envoyées.");
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

    #[Route('/courses/affectTrainee', name: 'app_affect_trainee')]
    public function affectTrainee(Request $request,  EntityManagerInterface $entityManager): Response
    {
        $itemId = $request->request->get('selecedItem');
        $formationId = $request->request->get('formationId');
        if ($formationId && $itemId > 0) {
            $formation  = $entityManager->getRepository(Formation::class)->findOneBy(['id'=> $formationId]);
            $user = $entityManager->getRepository(Trainee::class)->findOneBy(['id' => $itemId]);
            $TraineeFormation = new TraineeFormation();
            $userInFormation = $entityManager->getRepository(TraineeFormation::class)->findOneBy(['trainee' => $user,'formation' => $formation]);
            if ($userInFormation == null) {
                $TraineeFormation->setTrainee($user);
                $TraineeFormation->setFormation($formation);
                $entityManager->persist($TraineeFormation);
                $entityManager->flush();
                $object = new \stdClass();
                $object->status = true;
                $object->id = $user->getId();
                $object->firstName = $user->getFirstName()?$user->getFirstName():'';
                $object->lastName = $user->getLastName()?$user->getLastName(): '';
                $object->position = $user->getPosition()?$user->getPosition(): '';
                $object->message = '';
                return new Response(json_encode($object));
            } else {
                $object = new \stdClass();
                $object->status = false;
                $object->message = 'Utilisateur existe déjà';
                return new Response(json_encode($object));
            }

        }
        return new Response('false');
    }

    #[Route('/courses/seeConvocation/{idFormation}/{idTrainee}', name: 'app_trainee_see_conv')]
    public function seeConvToTrainee(Request $request, EntityManagerInterface $entityManager,MailerInterface $mailer, $idFormation, $idTrainee ): Response
    {
        $formation = $entityManager->getRepository(Formation::class)->findOneBy(['id'=> $idFormation]);
        $trainee =  $entityManager->getRepository(Trainee::class)->findOneBy(['id' => $idTrainee]);
        $this->generate_pdf($formation,$trainee);
        return $this->render('emails/convocation.html.twig', [
            'dataF' => $formation,
            'dataS' => $trainee
        ]);
    }
    #[Route('/formation/delete/{id}', name: 'app_delete_formation')]
    public function deleteFormation(EntityManagerInterface $entityManager, $id): Response
    {
        $formation = $entityManager->getRepository(Formation::class)->findOneBy(['id' => $id]);
        $traineeFormation = $entityManager->getRepository(TraineeFormation::class)->findBy(['formation' => $formation]);
        $deleted = 0;
        foreach ($traineeFormation as $item) {
           $entityManager->remove($item);
           $entityManager->flush();
           $deleted ++;
        }

        if($deleted == count($traineeFormation)) {
            $entityManager->remove($formation);
            $entityManager->flush();
        }
        $object = new \stdClass();
        $object->status = true;
        $object->message = "La formation est supprimée avec succès";
        return new Response(json_encode($object));
    }

    #[Route('/courses/seeConvocationRecap/{idFormation}', name: 'app_trainee_see_conv_recap')]
    public function seeConvRecap(Request $request, EntityManagerInterface $entityManager,MailerInterface $mailer, $idFormation ): Response
    {
        $formation = $entityManager->getRepository(Formation::class)->findOneBy(['id'=> $idFormation]);
        $traineesFormation =  $entityManager->getRepository(TraineeFormation::class)->findBy(['formation' => $formation]);
        $listOfTrainees = [];
        foreach ($traineesFormation as $item) {
            $trainee = $item->getTrainee();
            $listOfTrainees[] = $trainee;
        }
        return $this->render('emails/convocation_traineer.html.twig', [
            'dataF' => $formation,
            'traineesFormation' => $listOfTrainees
        ]);
    }

    #[Route('/courses/seeConvocationClient/{idFormation}', name: 'app_trainee_see_conv_client')]
    public function seeConvClient(Request $request, EntityManagerInterface $entityManager,MailerInterface $mailer, $idFormation ): Response
    {
        $formation = $entityManager->getRepository(Formation::class)->findOneBy(['id'=> $idFormation]);
        $traineesFormation =  $entityManager->getRepository(TraineeFormation::class)->findBy(['formation' => $formation]);
        $listOfTrainees = [];
        foreach ($traineesFormation as $item) {
            $trainee = $item->getTrainee();
            $listOfTrainees[] = $trainee;
        }
        return $this->render('emails/convocation_client.html.twig', [
            'dataF' => $formation,
            'traineesFormation' => $listOfTrainees
        ]);
    }
}
