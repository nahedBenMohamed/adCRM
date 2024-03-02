<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Formation;
use App\Entity\Link;
use App\Entity\Trainee;
use App\Entity\TraineeFormation;
use App\Entity\User;
use App\Form\CompanyFormType;
use App\Form\FormationFormType;
use App\Form\FormationInfoFormType;
use App\Form\TraineeFormationFormType;
use App\Form\TraineeFormType;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;

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

    #[Route('/courses/add/{idCompany}/{idFormation}', name: 'app_courses_add', requirements: ['idCompany' => '\d+'])]
    public function addCourse(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, $idCompany = null, $idFormation = null ): Response
    {
        if($idCompany == 0){
            $this->addFlash('warning', "La formation est sans organisation il y a un problème lors de la création de l'organisation");
            return $this->redirectToRoute('app_courses');
        }
        if ($idCompany == null && $idFormation ==  null){
            //add company info
            $company = new Company();
            $form = $this->createForm(CompanyFormType::class, $company);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->formCompanySave($form, $slugger,$company, $entityManager);
                return $this->redirectToRoute('app_courses_add', ['idCompany' => $company->getId()]);
            }
            return $this->render('courses/add.html.twig', [
                'companyForm' => $form->createView(),
                'idCompany' => $idCompany,
                'idFormation' => '',
                'formInfo' => ''
            ]);
        }
        if ($idCompany && !$idFormation) {
            //get company
            $company = $entityManager->getRepository(Company::class)->findOneBy(['id' => $idCompany]);
            $formation = $entityManager->getRepository(Formation::class)->findOneBy(['company' => $company]);
            if ($formation) {
                return $this->redirectToRoute('app_courses_add', ['idCompany' => $idCompany, 'idFormation' => $formation->getId()]);
            }
            $formCompany = $this->createForm(CompanyFormType::class, $company);
            $formCompany->handleRequest($request);
            if ($formCompany->isSubmitted() && $formCompany->isValid()) {
                $this->formCompanySave($formCompany, $slugger,$company, $entityManager);
            }
            //add course info
            $course = new Formation();
            $form = $this->createForm(FormationFormType::class, $course, ['allow_extra_fields' => true]);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $course = $form->getData();
                if ($request->request->get('otherProgram') != '') {
                    //create new link
                    $link = new Link();
                    $link->setName('lien statique');
                    $link->setValue($request->request->get('otherProgram'));
                    $entityManager->persist($link);
                    $entityManager->flush();
                    //affect the new link to the new course
                    $course->setLinkToProgram($link);
                }
                if($request->request->get('otherLinkFormateur') != '') {
                    $link = new Link();
                    $link->setName('lien statique');
                    $link->setValue($request->request->get('otherLinkFormateur'));
                    $entityManager->persist($link);
                    $entityManager->flush();
                    //affect the new link to the new course
                    $course->setLinkformateur ($link);
                }
                $entityManager->persist($course);
                $entityManager->flush();
                return $this->redirectToRoute('app_courses_add', ['idCompany' => $idCompany, 'idFormation' => $course->getId()]);
            }
            return $this->render('courses/add.html.twig', [
                'formationForm' => $form->createView(),
                'companyForm' => $formCompany->createView(),
                'idCompany' => $idCompany,
                'idFormation' =>  $course->getId(),
                'formInfo' => ''
            ]);
        }
        //add trainer
        if ($idCompany && $idFormation) {
            //get company
            $company = $entityManager->getRepository(Company::class)->findOneBy(['id' => $idCompany]);
            $formCompany = $this->createForm(CompanyFormType::class, $company);
            $formCompany->handleRequest($request);
            if ($formCompany->isSubmitted() && $formCompany->isValid()) {
                $this->formCompanySave($formCompany, $slugger,$company, $entityManager);
            }

            $course2 =  $entityManager->getRepository(Formation::class)->find($idFormation);
            $form = $this->createForm(FormationFormType::class, $course2, ['allow_extra_fields' =>true]);
            $form->handleRequest($request);

            $formInfo = $this->createForm(FormationInfoFormType::class, $course2, ['allow_extra_fields' =>true]);
            $formInfo->handleRequest($request);
            $formationUser = $entityManager->getRepository(TraineeFormation::class)->findBy(['formation'=>$course2]);
            $trainees = [];
            $AllTrainees = $entityManager->getRepository(Trainee::class)->findBy([],['id' => 'DESC']);
            foreach ($formationUser as $item) {
                array_push($trainees, $item->getTrainee() );
            }

            if (($form->isSubmitted() && $form->isValid()) || ($formInfo->isSubmitted() && $formInfo->isValid())) {
                $course2 = $form->getData();
                if ($form->isSubmitted()) {
                    if ($request->request->get('otherProgram') != '') {
                        //create new link
                        $link = new Link();
                        $link->setName('lien statique');
                        $link->setValue($request->request->get('otherProgram'));
                        $entityManager->persist($link);
                        $entityManager->flush();
                        //affect the new link to the new course
                        $course2->setLinkToProgram($link);
                    }
                    if($request->request->get('otherLinkFormateur') != '') {
                        $link = new Link();
                        $link->setName('lien statique');
                        $link->setValue($request->request->get('otherLinkFormateur'));
                        $entityManager->persist($link);
                        $entityManager->flush();
                        //affect the new link to the new course
                        $course2->setLinkformateur($link);
                    }
                    $entityManager->persist($course2);
                    $entityManager->flush();
                }
                if ($formInfo->isSubmitted() && $formInfo->isValid()) {
                    $course2 = $formInfo->getData();
                    $entityManager->persist($course2);
                    $entityManager->flush();
                }
                return $this->redirectToRoute('app_courses_add', ['idCompany' => $idCompany, 'idFormation' => $course2->getId()]);
            }
            return $this->render('courses/add.html.twig', [
                'formationForm' => $form->createView(),
                'companyForm' => $formCompany->createView(),
                'formation' => $course2,
                'trainees' => $trainees,
                'formInfo' => $formInfo->createView(),
                'allTrainees' => $AllTrainees,
                'idCompany' => $idCompany,
                'idFormation' =>  $course2->getId()
            ]);
        }

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
            if ($form->isSubmitted()) {
                if ($course->getLinkToProgram() == null) {
                    if($request->request->get('otherProgram') != '') {
                        //create new link
                        $link = new Link();
                        $link->setName('lien statique');
                        $link->setValue($request->request->get('otherProgram'));
                        $entityManager->persist($link);
                        $entityManager->flush();
                        //affect the new link to the new course
                        $course->setLinkToProgram($link);
                    }
                    if($request->request->get('otherLinkFormateur') != '') {
                        $link = new Link();
                        $link->setName('lien statique');
                        $link->setValue($request->request->get('otherLinkFormateur'));
                        $entityManager->persist($link);
                        $entityManager->flush();
                        //affect the new link to the new course
                        $course->setLinkformateur ($link);
                    }
                }
                $entityManager->persist($course);
                $entityManager->flush();
            }
            if ($formInfo->isSubmitted() && $formInfo->isValid()) {
                $course = $formInfo->getData();
                $entityManager->persist($course);
                $entityManager->flush();
            }
            return $this->redirectToRoute('app_courses_add', ['id' => $course->getId()]);
            //return $this->redirectToRoute('app_courses_edit', ['id' => $course->getId()]);
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
        //return $this->redirectToRoute('app_courses_edit', ['id' => $idFormation]);
        return $this->redirectToRoute('app_courses_add', ['idCompany' => $formation->getCompany()->getId(), 'idFormation' => $idFormation]);

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
        //return $this->redirectToRoute('app_courses_edit', ['id' => $idFormation]);
        return $this->redirectToRoute('app_courses_add', ['idCompany' => $formation->getCompany()->getId(), 'idFormation' => $idFormation]);
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
            /********** send mail of convocation to all trainees ***/
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
            /************ end send mail to all trainee ***/

            $item->setSendConvocation(true);
            $entityManager->persist($item);
            $entityManager->flush();
        }
        $traineer = $formation->getFormateur();
        if($traineer) {
            /************ send mail trainee to adconseil ********/
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

            /************ end mail trainee to adconseil *******/

            /************ send specific mail to Traineer and copy to adconseil ******************/
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
            /**************end send mail to Traineer and copy to adconseil******/
            //send specific mail to contact client
            $client = $formation->getCustomer();
            if($client) {
                /*********** Send copy of mail client to adconseil *****************/
                $htmlClient = $this->renderView('emails/convocation_client.html.twig', [
                    'dataF' => $formation,
                    'dataS' => $traineesFormation[0],
                    'traineesFormation' => $listOfTrainees,
                    'client' => $client[0]
                ]);
                $emailClient = (new Email())
                    ->from('formation@adconseil.org')
                    ->subject('Convocationde vos apprenant.es à la formation '.$formation->getNomFormation())
                    ->html($htmlClient)
                    ->to('formation@adconseil.org');
                $mailer->send($emailClient);
                /******* end mail to adconseil *****/

                /********* send mail to all clients ******/
                foreach ($client as $cl) {
                    $htmlClient = $this->renderView('emails/convocation_client.html.twig', [
                        'dataF' => $formation,
                        'dataS' => $traineesFormation[0],
                        'traineesFormation' => $listOfTrainees,
                        'client' => $cl
                    ]);
                    $emailClient = (new Email())
                        ->from('formation@adconseil.org')
                        ->subject('Convocationde vos apprenant.es à la formation '.$formation->getNomFormation())
                        ->html($htmlClient)
                        ->to($cl->getEmail());
                    $mailer->send($emailClient);
                }
                /**** fin send mail to all client ************/

            }
        }


        $this->addFlash('success', "Les convocations ont bien été envoyées.");
        return $this->redirectToRoute('app_courses_add', ['idCompany' => $formation->getCompany()->getId(), 'idFormation' => $idFormation]);
        //return $this->redirectToRoute('app_courses_edit', ['id' => $idFormation]);
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
            'traineesFormation' => $listOfTrainees,
            'client' => $formation->getCustomer()[0]
        ]);
    }

    #[Route('/courses/uploadTraineeFromFile/{idFormation}', name: 'app_upload_trainee')]
    public function uploadTraineeFromFile(Request $request, EntityManagerInterface $entityManager, $idFormation ): Response
    {
        $formation = $entityManager->getRepository(Formation::class)->findOneBy(['id'=> $idFormation]);
        if ($request->isMethod('POST')) {
           // $file  = $request->request->get('trainees');
            $file =  $request->files->get('fileTrainee');
            if ($file->getClientOriginalExtension() =='xlsx') {
                $fileFolder = $this->getParameter('trainee_file_directory');  //choose the folder in which the uploaded file will be stored
                $filePathName = md5(uniqid()) . $file->getClientOriginalName();
                // apply md5 function to generate an unique identifier for the file and concat it with the file extension
                try {
                    $file->move($fileFolder, $filePathName);
                } catch (FileException $e) {
                    dd($e);
                }
                $reader = new Xls();
                $spreadsheet = IOFactory::load($fileFolder .'/'. $filePathName); // Here we are able to read from the excel file
               // $row = $spreadsheet->getActiveSheet()->removeRow(1); // I added this to be able to remove the first file line
                $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true); // here, the read data is turned into an array
                if(count($sheetData)>0) {
                    foreach ($sheetData as $Row)
                    {
                        //we save only row that contain mail
                        if ($Row['A'] !== null && str_contains($Row['D'], '@') !== false) {
                            $first_name= $Row['A'];     // store the first_name on each iteration
                            $last_name = $Row['B']; // store the last_name on each iteration
                            $position = $Row['C']; // store the position on each iteration
                            $email = $Row['D'];   // store the email on each iteration
                            //$email = $Row['E'];     // store the email on each iteration

                            $user_existant = $entityManager->getRepository(Trainee::class)->findOneBy(array('email' => $email));
                            // make sure that the user does not already exists in your db

                            if (!$user_existant && $email != null)
                            {
                                $student = new Trainee();
                                //$student->setCivility($civility);
                                $student->setFirstName($first_name);
                                $student->setLastName($last_name);
                                $student->setEmail($email);
                                $student->setPosition($position);
                                $entityManager->persist($student);
                                $entityManager->flush();
                                // here Doctrine checks all the fields of all fetched data and make a transaction to the database.
                                //affect the user to the formation
                                $TraineeFormation = new TraineeFormation();
                                $TraineeFormation->setTrainee($student);
                                $TraineeFormation->setFormation($formation);
                                $entityManager->persist($TraineeFormation);
                                $entityManager->flush();
                            }
                            // affect user to the current formation
                            if($user_existant) {
                                $existTraineeFormation = new TraineeFormation();
                                $existTraineeFormation->setTrainee($user_existant);
                                $existTraineeFormation->setFormation($formation);
                                $entityManager->persist($existTraineeFormation);
                                $entityManager->flush();
                            }
                        }

                    }
                }
            }
        }
        $this->addFlash('success', "Les stagiaires sont enregistrés avec succès.");
        //return $this->redirectToRoute('app_courses_edit', ['id' => $formation->getId()]);
        return $this->redirectToRoute('app_courses_add', ['idCompany' => $formation->getCompany()->getId(), 'idFormation' => $formation->getId()]);
    }

    function formCompanySave($form, $slugger,$company, $entityManager) {
        $infoFile = $form->get('infoFilename')->getData();
        // this condition is needed because the 'brochure' field is not required
        // so the PDF file must be processed only when a file is uploaded
        if ($infoFile) {
            $originalFilename = pathinfo($infoFile->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$infoFile->guessExtension();
            // Move the file to the directory where brochures are stored
            $infoFile->move(
                $this->getParameter('company_file_directory'),
                $newFilename
            );
            $company->setInfoFilename($newFilename);
        }
        $company = $form->getData();
        $entityManager->persist($company);
        $entityManager->flush();
    }
}
