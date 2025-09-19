<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Formation;
use App\Entity\Link;
use App\Entity\Log;
use App\Entity\Trainee;
use App\Entity\TraineeFormation;
use App\Form\FormationFormType;
use App\Form\FormationInfoFormType;
use App\Form\TraineeFormationEvalType;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;

class CourseController extends AbstractController
{

    #[Route('/coursesInter', name: 'app_courses_inter')]
    public function viewCoursesInter(EntityManagerInterface $entityManager): Response
    {
        if (in_array('ROLE_TEACHER', $this->getUser()->getRoles(), true)) {
            $courses = $entityManager->getRepository(Formation::class)->findBy(['type' =>'inter', 'formateur' =>$this->getUser()],['id' => 'DESC']);
            $view = 'coursesFormateur/index.html.twig';
        } else {
            $courses = $entityManager->getRepository(Formation::class)->findBy(['type' =>'inter'],['id' => 'DESC']);
            $view = 'courses/index.html.twig';
        }
        return $this->render($view, [
            'courses' => $courses,
            'alowAddNew' => false
        ]);

    }
    #[Route('/courses/courseManagement/{type}/{idFormation}', name: 'app_courses_manage')]
    public function manageCourse(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger,$type = null, $idFormation = null ): Response
    {
        $clients = $entityManager->getRepository(Customer::class)->findBy([], ['id' => 'DESC']);
        $view = "courses/formationManagement.html.twig";
        if (in_array('ROLE_TEACHER', $this->getUser()->getRoles(), true)) {
           // $view = "teacher/showFormation.html.twig";
            $view = "coursesFormateur/formationManagement_tr.html.twig";
        }
        if($type == 'inter') {
            $view = "courses/formationManagementInter.html.twig";
            if (in_array('ROLE_TEACHER', $this->getUser()->getRoles(), true)) {
                $view = "coursesFormateur/formationManagementInter_tr.html.twig";
            }
        }
        if ($idFormation ==  null){
            $course = new Formation();
            $form = $this->createForm(FormationFormType::class, $course, ['allow_extra_fields' =>true]);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $course = $form->getData();
                $this->saveInlog('addFormation',"Création d'une nouvelle Formation: ". $course->getNomFormation(), $entityManager);
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
                    $course->setLinkformateur($link);
                }
                $course->setType($type);
                $entityManager->persist($course);
                $entityManager->flush();
                return $this->redirectToRoute('app_courses_manage', ['type' => $type, 'idFormation' => $course->getId()]);
            }

            return $this->render($view, [
                'formationForm' => $form->createView(),
                'idFormation' => '',
                'formInfo' => '',
                'clients' => $clients,
                'type' => $type,
                'courseName' => '',
                'selectedClient' => [],
                'formationClient' => []
            ]);
        } else {
            $course =  $entityManager->getRepository(Formation::class)->find($idFormation);
            $formationClient = $course->getCustomers();
            $selectedClient = [];
            if($formationClient) {
                if ($type == 'intra') {
                    foreach ($formationClient as $client) {
                        $selectedClient[] = $client->getId();
                    }

                } else {
                    foreach ($formationClient as $client) {
                        $selectedClient[] = $client->getId();
                    }
                }
            } else{
                $form = $this->createForm(FormationFormType::class, $course, ['allow_extra_fields' =>true]);
                return $this->render($view, [
                    'formationForm' => $form->createView(),
                    'idFormation' => $idFormation,
                    'formInfo' => '',
                    'clients' => $clients,
                    'type' => $type,
                    'courseName' => '',
                    'selectedClient' => [],
                    'formationClient' => $formationClient
                ]);
            }
            $courseStatus = 'new';
            if($course->getNomFormation()) {
                $courseStatus = 'old';
            }
            $form = $this->createForm(FormationFormType::class, $course, ['allow_extra_fields' =>true]);
            $form->handleRequest($request);

            $formInfo = $this->createForm(FormationInfoFormType::class, $course, ['allow_extra_fields' =>true]);
            $formInfo->handleRequest($request);
            $formationUser = $entityManager->getRepository(TraineeFormation::class)->findBy(['formation'=>$course]);
            $AllTrainees = $entityManager->getRepository(Trainee::class)->findBy([],['id' => 'DESC']);

            if (($form->isSubmitted() && $form->isValid()) || ($formInfo->isSubmitted() && $formInfo->isValid())) {
                $course = $form->getData();
                if ($form->isSubmitted()) {
                    if($courseStatus == 'new') {
                        $this->saveInlog('addFormation',"Création d'une nouvelle Formation: ". $course->getNomFormation(), $entityManager);
                    } else {
                        $this->saveInlog('updateFormation',"Modification de la Formation: ". $course->getNomFormation(), $entityManager);
                    }
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
                        $course->setLinkformateur($link);
                    }
                    $course->setType($type);
                    $entityManager->persist($course);
                    $entityManager->flush();
                }
                if ($formInfo->isSubmitted() && $formInfo->isValid()) {
                    $course = $formInfo->getData();
                    $entityManager->persist($course);
                    $entityManager->flush();
                }
                return $this->redirectToRoute('app_courses_manage', ['type' => $type, 'idFormation' => $course->getId()]);
            }

            // this part for teacher view
            $defaultData = ['mailFormateurText' => $course->getMailFormateurText()];
            $formMail = $this->createFormBuilder($defaultData)
                ->add('mailFormateurText', TextareaType::class,[
                    'label' =>"Contenu de l'email",
                    'required' => false
                ])
                ->getForm();
            $formMail->handleRequest($request);
            if ($formMail->isSubmitted() && $formMail->isValid()) {
                $data = $formMail->getData();
                if($data['mailFormateurText'] !== null) {
                    $course->setMailFormateurText($data['mailFormateurText']);
                } else {
                    $course->setMailFormateurText('');
                }

                $entityManager->persist($course);
                $entityManager->flush();
                return $this->redirectToRoute('app_courses_manage', ['type' => $type, 'idFormation' => $course->getId()]);
            }
            return $this->render($view, [
                'formationForm' => $form->createView(),
                'formation' => $course,
                'trainees' => $formationUser,
                'formInfo' => $formInfo->createView(),
                'allTrainees' => $AllTrainees,
                'idFormation' =>  $course->getId(),
                'clients' => $clients,
                'type' => $type,
                'courseName' => $course->getNomFormation(),
                'selectedClient' => $selectedClient,
                'formationClient' => $course->getCustomers(),
                'formMail' => $formMail->createView()
            ]);
        }
        return 'true';

    }

    #[Route('/courses/deleteTrainee/{idFormation}/{idTrainee}', name: 'app_trainee_formation_delete')]
    public function deleteTraineeFromFormation(Request $request, EntityManagerInterface $entityManager, $idFormation, $idTrainee ): Response
    {
        $formation = $entityManager->getRepository(Formation::class)->findOneBy(['id'=> $idFormation]);
        $trainee =  $entityManager->getRepository(Trainee::class)->findOneBy(['id' => $idTrainee]);
        $traineeToDelete = $entityManager->getRepository(TraineeFormation::class)->findOneBy(['formation' => $formation, 'trainee' =>$trainee]);
        $entityManager->remove($traineeToDelete);
        $entityManager->flush();
        return $this->redirectToRoute('app_courses_manage', ['type' => $formation->getType(), 'idFormation' => $idFormation]);

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
            ->cc('y.chicha@adconseil.org')
            ->to($trainee->getEmail());
        $mailer->send($email);
        $traineesFormation->setSendConvocation(true);
        $dateConv = new \DateTime();
        $traineesFormation->setDateConvocation($dateConv);
        $entityManager->persist($traineesFormation);
        $formation->setStatus(1);
        $entityManager->persist($formation);
        $entityManager->flush();
       // $this->addFlash('success', "La convocation a été envoyée avec succès.");
        $this->saveInlog('EnvoiConnvForOneStag_'.$idFormation,"Envoi d'une Convocation à la formation ".$formation->getNomFormation().'. Email de stagiaire:'.$trainee->getEmail(), $entityManager);
        return $this->redirectToRoute('app_courses_manage', ['type' => $formation->getType(), 'idFormation' => $idFormation]);
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
                ->cc('y.chicha@adconseil.org')
                ->to($trainee->getEmail());
            $mailer->send($email);
            /************ end send mail to all trainee ***/

            $item->setSendConvocation(true);
            $dateConv = new \DateTime();
            $item->setDateConvocation($dateConv);
            $entityManager->persist($item);
            $entityManager->flush();
        }
        $formation->setStatus(1);
        $entityManager->persist($formation);
        $entityManager->flush();
        $traineer = $formation->getFormateur();
        if($traineer) {
            /************ send copy of convocation to adconseil admin ********/
            $html2 = $this->renderView('emails/convocation.html.twig', [
                'dataF' => $formation,
                'dataS' => $traineesFormation[0]
            ]);
            //send copy stag mail to adconseil
            $emailAdmin = (new Email())
                ->from('formation@adconseil.org')
                ->subject('Convocation à la formation '.$formation->getNomFormation())
                ->html($html2)
                ->cc('y.chicha@adconseil.org')
                ->to('formation@adconseil.org');
           $mailer->send($emailAdmin);

            /************ send specific recap mail to formateur and copy to adconseil ******************/
            $htmlRecap = $this->renderView('emails/convocation_traineer.html.twig', [
                'dataF' => $formation,
                'dataS' => $traineesFormation[0],
                'traineesFormation' => $listOfTrainees
            ]);
            $emailTraineer = (new Email())
                ->from('formation@adconseil.org')
                ->subject("Récap’ formation ".$formation->getNomFormation())
                ->html($htmlRecap)
                ->cc(['formation@adconseil.org','y.chicha@adconseil.org'])
                ->to($traineer->getEmail());
            $mailer->send($emailTraineer);
            /**************end send mail to Traineer and copy to adconseil******/
            //send specific mail to contact client
            $client = $formation->getCustomers();
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
                    ->subject('Convocation de vos apprenant.es à la formation '.$formation->getNomFormation())
                    ->html($htmlClient)
                    ->cc('y.chicha@adconseil.org')
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
                        ->subject('Convocation de vos apprenant.es à la formation '.$formation->getNomFormation())
                        ->html($htmlClient)
                        ->to($cl->getEmail());
                   $mailer->send($emailClient);
                }
                /**** send mail to contact administrative ***/
                /*$contactAdmin = $formation->getCustomers()->getContactAdministratif();
                if($contactAdmin) {
                    $htmlClient = $this->renderView('emails/convocation_client.html.twig', [
                        'dataF' => $formation,
                        'dataS' => $traineesFormation[0],
                        'traineesFormation' => $listOfTrainees,
                        'client' => $cl
                    ]);
                    $emailClient = (new Email())
                        ->from('formation@adconseil.org')
                        ->subject('Convocation de vos apprenant.es à la formation '.$formation->getNomFormation())
                        ->html($htmlClient)
                        ->to($cl->getEmail());
                    $mailer->send($contactAdmin);
                }*/

                /**** fin send mail to all client ************/

            }
        }

        $this->saveInlog('EnvoiConnvForAll_'.$idFormation,"Envoi d'une Convocation à tous les stagiaires pour la formation ".$formation->getNomFormation(), $entityManager);
        $this->addFlash('success', "Les convocations ont bien été envoyées.");
        return $this->redirectToRoute('app_courses_manage', ['type' => $formation->getType(), 'idFormation' => $idFormation]);
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
                $dateAffectation = new \DateTime();
                $TraineeFormation->setDateAffectationFormation($dateAffectation);
                $entityManager->persist($TraineeFormation);
                $entityManager->flush();
                $object = new \stdClass();
                $object->status = true;
                $object->id = $user->getId();
                $object->firstName = $user->getFirstName()?$user->getFirstName():'';
                $object->lastName = $user->getLastName()?$user->getLastName(): '';
                $object->position = $user->getPosition()?$user->getPosition(): '';
                $object->email = $user->getEmail()?$user->getEmail(): '';
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
            //delete customer for the trainer
           $entityManager->remove($item);
           $deleted ++;
        }
        $FormationCustomers = $formation->getCustomers();
        if (!empty($FormationCustomers)) {
            foreach($FormationCustomers as $customer) {
                $formation->removeCustomer($customer);
            }
        }
        $entityManager->flush();
        if($deleted == count($traineeFormation)) {
            $entityManager->remove($formation);
            $entityManager->flush();
        }
        $object = new \stdClass();
        $object->status = true;
        $object->message = "La formation est supprimée avec succès";
        $this->saveInlog('deleteformaion',"La formation ".$formation->getNomFormation()." est supprimée avec succès", $entityManager);
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
            'client' => $formation->getCustomers()[0]
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
       // $this->addFlash('success', "Les stagiaires sont enregistrés avec succès.");
        $this->saveInlog('AddTrainee',"Ajout des stagiaires à la formation: <b>".$formation->getNomFormation()."</b> à partir un fichier excel", $entityManager);
        return $this->redirectToRoute('app_courses_manage', ['type' => $formation->getType(), 'idFormation' => $formation->getId()]);
    }
    #[Route('/courses/customerToformation', name: 'app_add_customers')]
    public function customerToformation(Request $request,  EntityManagerInterface $entityManager): Response
    {

        $itemIds =  json_decode($request->request->get('selectedItems'), true);
        $formationId = $request->request->get('formationId');
        $typeFormation = $request->request->get('typeFormation');
        if($formationId) {
            $formation  = $entityManager->getRepository(Formation::class)->findOneBy(['id'=> $formationId]);
        } else {
            // create new formation and affect selected clients
            $formation = new Formation();
            $formation->setType($typeFormation);
        }
        //remove all customer
        if ($formation->getCustomers()) {
            foreach($formation->getCustomers() as $client) {
                $formation->removeCustomer($client);
            }
        }

        if ($itemIds > 0) {
            foreach ($itemIds as $item) {
                $customer = $entityManager->getRepository(Customer::class)->findOneBy(['id'=> $item]);
                $formation->addCustomer($customer);
                $entityManager->persist($formation);
            }
            $entityManager->flush();
            return new Response($formation->getId());
        }
        return new Response('false');
    }
    /**
     * save action in table log
     * @param $activity
     * @param $description
     * @param $entityManager
     * @return void
     */
    function saveInlog($activity, $description, $entityManager)
    {
        $log = new Log();
        $log->setActivityName($activity);
        $log->setActivityDescription($description);
        $this->getUser();
        $log->setUserName($this->getUser()->getfirstName().' '.$this->getUser()->getLastName());
        $date = new \DateTime();
        $log->setDateAjout($date);
        $entityManager->persist($log);
        $entityManager->flush();
    }
    #[Route('/courses/sendAlertTeacher/{idFormation}', name: 'app_teacher_send_alert')]
    public function sendAlertTeacher(Request $request, EntityManagerInterface $entityManager,MailerInterface $mailer, $idFormation ): Response
    {
        $formation = $entityManager->getRepository(Formation::class)->findOneBy(['id'=> $idFormation]);
        $traineesFormation =  $entityManager->getRepository(TraineeFormation::class)->findBy(['formation' => $formation]);
        $listOfTrainees = [];
        foreach ($traineesFormation as $item) {
            $trainee = $item->getTrainee();
            $listOfTrainees[] = $trainee;
        }
        return $this->render('emails/alert_teacher.html.twig', [
            'dataF' => $formation,
            'traineesFormation' => $listOfTrainees,
            'client' => $formation->getCustomers()[0]
        ]);
    }

    #[Route('/courses/sendEmailAlertTeacher/{idFormation}', name: 'app_teacher_send_email_alert')]
    public function sendEmailAlertTeacher(Request $request, EntityManagerInterface $entityManager,MailerInterface $mailer, $idFormation ): Response
    {
        $formation = $entityManager->getRepository(Formation::class)->findOneBy(['id'=> $idFormation]);
        $traineesFormation =  $entityManager->getRepository(TraineeFormation::class)->findBy(['formation' => $formation]);
        $listOfTrainees = [];
        foreach ($traineesFormation as $item) {
            $trainee = $item->getTrainee();
            $listOfTrainees[] = $trainee;
        }
        if ($formation->getFormateur()) {
            $this->saveInlog('EnvoiRappelTeacher_'.$idFormation,". Envoi de Rappel des informations clés pour la formation".$formation->getNomFormation().'. Email de formateur:'.$formation->getFormateur()->getEmail(), $entityManager);
        }
        $html = $this->renderView('emails/mail_alert_teacher.html.twig', [
            'dataF' => $formation,
            'traineesFormation' => $listOfTrainees,
            'client' => $formation->getCustomers()[0]
        ]);
        $email = (new Email())
            ->from('formation@adconseil.org')
            ->subject('Convocation à la formation '.$formation->getNomFormation())
            ->html($html)
            ->cc('formation@adconseil.org')
            ->to($formation->getFormateur()->getEmail());
        $mailer->send($email);
        $this->addFlash('success', "Le rappel des informations clés a été envoyée au formateur avec succès.");
        return $this->redirectToRoute('app_courses_manage', ['type' => $formation->getType(), 'idFormation' => $idFormation]);
    }

    #[Route('/courses/seeHistoric/{formationId}', name: 'app_see_historic')]
    public function seeHistoric(Request $request, EntityManagerInterface $entityManager, $formationId ): Response
    {
        $formation = $entityManager->getRepository(Formation::class)->findOneBy(['id'=> $formationId]);
        $allLog = $entityManager->getRepository(Log::class)->findBy([],['id' => 'DESC'], 100);
        $log = [];
        foreach ($allLog as $item) {
            $tabItem = explode('_', $item->getActivityName() );
            if(sizeof($tabItem) > 1) {
                if($tabItem[1] == $formationId) {
                    $log[] = $item;
                }
            }
        }
        return $this->render('views/historic.html.twig', [
            'log' => $log,
            'formationName' => $formation->getNomFormation()
        ]);
    }

    #[Route('/courses/seeAttestationTrainee/{idFormation}/{idTrainee}', name: 'app_trainee_see_attestation')]
    public function seeAttestationTrainee(Request $request, EntityManagerInterface $entityManager,MailerInterface $mailer, $idFormation, $idTrainee ): Response
    {
        $formation = $entityManager->getRepository(Formation::class)->findOneBy(['id'=> $idFormation]);
        $trainee =  $entityManager->getRepository(Trainee::class)->findOneBy(['id' => $idTrainee]);
        $evalTrainee = $entityManager->getRepository(TraineeFormation::class)->findOneBy(['trainee' => $trainee,'formation' => $formation]);
        $traineesFormation =  $entityManager->getRepository(TraineeFormation::class)->findOneBy(['formation' => $formation, 'trainee' => $trainee]);
        $this->generate_pdf($formation,$trainee);
        return $this->render('emails/attestation.html.twig', [
            'dataF' => $formation,
            'dataS' => $trainee,
            'evalTrainee' => $evalTrainee,
            'traineesFormation' => $traineesFormation
        ]);
    }

    #[Route('/courses/seeCertifTrainee/{idFormation}/{idTrainee}', name: 'app_trainee_see_certif')]
    public function seeCertifTrainee(Request $request, EntityManagerInterface $entityManager,MailerInterface $mailer, $idFormation, $idTrainee ): Response
    {
        $formation = $entityManager->getRepository(Formation::class)->findOneBy(['id'=> $idFormation]);
        $trainee =  $entityManager->getRepository(Trainee::class)->findOneBy(['id' => $idTrainee]);
        $traineesFormation =  $entityManager->getRepository(TraineeFormation::class)->findOneBy(['formation' => $formation, 'trainee' => $trainee]);
        $this->generate_pdf($formation,$trainee);
        return $this->render('emails/certif.html.twig', [
            'dataF' => $formation,
            'dataS' => $trainee,
            'traineesFormation' => $traineesFormation
        ]);
    }

    #[Route('/courses/addTraineeInfo/{idFormation}/{idTrainee}', name: 'app_trainee_add_info')]
    public function addTraineeInfo(Request $request, EntityManagerInterface $entityManager,MailerInterface $mailer, $idFormation, $idTrainee ): Response
    {
        $formation = $entityManager->getRepository(Formation::class)->findOneBy(['id'=> $idFormation]);
        $trainee =  $entityManager->getRepository(Trainee::class)->findOneBy(['id' => $idTrainee]);
        $evalTrainee = $entityManager->getRepository(TraineeFormation::class)->findOneBy(['trainee' => $trainee]);
        $form = $this->createForm(TraineeFormationEvalType::class, $evalTrainee);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $evalTrainee = $form->getData();
            $entityManager->persist($evalTrainee);
            $entityManager->flush();
            return $this->redirectToRoute('app_courses_manage', ['type' => $formation->getType(), 'idFormation' => $formation->getId()]);
        }
        $this->generate_pdf($formation,$trainee);
        return $this->render('trainees/evalutaion.html.twig', [
            'evaluation' => $form->createView(),
            'trainee' => $trainee
        ]);
    }

    #[Route('/courses/sendCertifAttestationToTrainee/{idFormation}/{idTrainee}', name: 'app_trainee_send_certif_attestaion')]
    public function sendCertifAttestationToTrainee(Request $request, EntityManagerInterface $entityManager,MailerInterface $mailer, $idFormation, $idTrainee ): Response
    {
        $formation = $entityManager->getRepository(Formation::class)->findOneBy(['id'=> $idFormation]);
        $trainee   =  $entityManager->getRepository(Trainee::class)->findOneBy(['id' => $idTrainee]);
        $traineesFormation =  $entityManager->getRepository(TraineeFormation::class)->findOneBy(['formation' => $formation, 'trainee' => $trainee]);
        $certif = $this->generate_pdf_certif_attestation($formation,$trainee, $entityManager);
        if($formation->getMailFormateurText()) {
            $htmlContent = $formation->getMailFormateurText();
        } else {
            $htmlContent = $this->renderView('emails/contentCertifAttestation.html.twig', [
                'dataF' => $formation,
                'dataS' => $trainee
            ]);
        }

        $email = (new Email())
            ->from('formation@adconseil.org')
            ->subject('Formation : '.$formation->getNomFormation())
            ->html($htmlContent)
            ->addPart(new DataPart(new File($this->getParameter('certif_file_directory').'/certif_'.$formation->getId().'_'.$trainee->getId().'.pdf')))
            ->addPart(new DataPart(new File($this->getParameter('certif_file_directory').'/attestation_'.$formation->getId().'_'.$trainee->getId().'.pdf')))
            ->to( $trainee->getEmail());
        $mailer->send($email);
        //certif et attestation envoyé
        $traineesFormation->setSendCertif(1);
        $entityManager->persist($traineesFormation);
        $entityManager->flush();
        $this->addFlash('success', "Le certificat et l'attestation ont a été envoyée avec succès.");
        $this->saveInlog('EnvoiCertifAttestationForOneStag_'.$idFormation,"Envoi de certificat et l'attestation à la formation ".$formation->getNomFormation().'. Email de stagiaire:'.$trainee->getEmail(), $entityManager);
        return $this->redirectToRoute('app_courses_manage', ['type' => $formation->getType(), 'idFormation' => $formation->getId()]);
    }

    public function generate_pdf_certif_attestation($formation, $trainee, $entityManager)
    {

        $evalTrainee = $entityManager->getRepository(TraineeFormation::class)->findOneBy(['trainee' => $trainee]);
        $traineesFormation =  $entityManager->getRepository(TraineeFormation::class)->findOneBy(['formation' => $formation, 'trainee' => $trainee]);
        $options = new Options();
        $options->set('defaultFont', 'Roboto');
        //create certif
        $dompdf = new Dompdf($options);
        $html = $this->renderView('emails/certif.html.twig', [
            'dataF' => $formation,
            'dataS' => $trainee,
            'traineesFormation' => $traineesFormation
        ]);
        $dompdf->loadHtml($html);
        $dompdf->render();
        $output = $dompdf->output();
        file_put_contents('documents/convocations/certif_'.$formation->getId().'_'.$trainee->getId().'.pdf', $output);
        //create attestation
        $dompdf2 = new Dompdf($options);
        $html2 = $this->renderView('emails/attestation.html.twig', [
            'dataF' => $formation,
            'dataS' => $trainee,
            'evalTrainee' => $evalTrainee,
            'traineesFormation' => $traineesFormation
        ]);
        $dompdf2->loadHtml($html2);
        $dompdf2->render();
        $output2 = $dompdf2->output();
        file_put_contents('documents/convocations/attestation_'.$formation->getId().'_'.$trainee->getId().'.pdf', $output2);
    }

    #[Route('/downloadCertifAttestationByFormation/{idFormation}', name: 'app_download_certif_attestation')]
    public function downloadTraineeByFormation(EntityManagerInterface $entityManager, $idFormation = null): Response
    {
        $course =  $entityManager->getRepository(Formation::class)->find($idFormation);
        $formationUser = $entityManager->getRepository(TraineeFormation::class)->findBy(['formation' => $course]);
        foreach ($formationUser as $item) {
            $this->generate_pdf_certif_attestation($course,$item, $entityManager);
        }
        // Specify the directory containing files
        $directoryPath ='documents/convocations/';
        // Files to include in the ZIP
        $files = [];
        // Iterate through the files in the directory
        foreach (new \DirectoryIterator($directoryPath) as $fileInfo) {
            if(str_contains($fileInfo->getFilename(), 'certif_'.$idFormation)) {
               // if ($fileInfo->isFile()) {
                    // Add the file to the zip
                    $files[$fileInfo->getRealPath()] = $fileInfo->getFilename();
               // }
            }
        }

        $zipFilePath = sys_get_temp_dir() . '/manual_certif.zip';

        // Open a new file pointer to write the ZIP
        $zipFile = fopen($zipFilePath, 'w');
        if (!$zipFile) {
            return new Response('Could not create ZIP file.', 500);
        }
        // Add the ZIP file structure
        //fwrite($zipFile, $this->createZipFileHeader($files));

        // Close the ZIP file
        fclose($zipFile);

        // Serve the ZIP file as a download
       // return $this->downloadZipFile($zipFilePath, 'manual_certif.zip');
        $downloadFileName ='manual_certif.zip';
        if (!file_exists($zipFilePath)) {
            throw $this->createNotFoundException('The file does not exist.');
        }

        // Serve the file as a download
        return new BinaryFileResponse($zipFilePath, 200, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment;filename="' . $downloadFileName . '"'
        ]);
    }

    /**
     * Create a simple ZIP file header.
     */
    private function createZipFileHeader(array $files): string
    {
        $zipData = '';

        foreach ($files as $filePath => $filename) {
            if (file_exists($filePath)) {
                $fileContent = file_get_contents($filePath);
                // Generate ZIP file structure (simplified)
                $zipData .= "File: $filename\n" . $fileContent;
            }
        }

        return $zipData;
    }
    public function downloadZip(string $zipFilePath, string $downloadFileName): StreamedResponse
    {
        // Ensure the file exists
        if (!file_exists($zipFilePath)) {
            throw $this->createNotFoundException('The file does not exist.');
        }

        $response = new StreamedResponse(function() use ($zipFilePath) {
            readfile($zipFilePath);
        });

        // Set headers to force download
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $downloadFileName . '"');
        $response->headers->set('Content-length', filesize($zipFilePath));

        // Optionally delete the file after streaming it
        $response->sendHeaders();
        register_shutdown_function('unlink', $zipFilePath);

        return $response;
    }
    #[Route('/courses/updateTimeFormation/{id}/{formationId}', name: 'app_update_time')]
    public function updateTimeFormation(Request $request,EntityManagerInterface $entityManager, $id, $formationId): Response
    {
        $trainee = $entityManager->getRepository(Trainee::class)->findOneBy(['id' => $id]);
        $formation = $entityManager->getRepository(Formation::class)->findOneBy(['id' => $formationId]);
        $traineeFormation = $entityManager->getRepository(TraineeFormation::class)->findOneBy(['trainee' => $trainee,'formation' => $formation]);
        $nbHour = $request->request->get('nbHour');
        $traineeFormation->setNbHour($nbHour);
        $entityManager->persist($traineeFormation);
        $entityManager->flush();
        return new Response('true');

    }

    #[Route('/downloadCertifByFormationTr/{idFormation}/{idTrainee}', name: 'app_download_certif_tr')]
    public function downloadCertifByFormationTr(EntityManagerInterface $entityManager, $idFormation = null, $idTrainee= null): Response
    {
        $formation = $entityManager->getRepository(Formation::class)->findOneBy(['id'=> $idFormation]);
        $trainee   =  $entityManager->getRepository(Trainee::class)->findOneBy(['id' => $idTrainee]);
        $this->generate_pdf_certif_attestation($formation,$trainee, $entityManager);
        $certificat = $this->getParameter('certif_file_directory').'/certif_'.$idFormation.'_'.$idTrainee.'.pdf';
        $attestation = $this->getParameter('certif_file_directory').'/attestation_'.$formation->getId().'_'.$idTrainee.'.pdf';
        // Create a BinaryFileResponse to download the file
        $response = new BinaryFileResponse($certificat);

        // Set headers to download the file instead of displaying it
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'certif_'.$trainee->getFirstName().'.pdf'
        );

        return $response;
    }

    #[Route('/downloadAttestationByFormationTr/{idFormation}/{idTrainee}', name: 'app_download_attestation_tr')]
    public function downloadAttestationTr(EntityManagerInterface $entityManager, $idFormation = null, $idTrainee= null): Response
    {
        $formation = $entityManager->getRepository(Formation::class)->findOneBy(['id'=> $idFormation]);
        $trainee   =  $entityManager->getRepository(Trainee::class)->findOneBy(['id' => $idTrainee]);
        $this->generate_pdf_certif_attestation($formation,$trainee, $entityManager);
        $attestation = $this->getParameter('certif_file_directory').'/attestation_'.$formation->getId().'_'.$idTrainee.'.pdf';
        // Create a BinaryFileResponse to download the file
        $response = new BinaryFileResponse($attestation);

        // Set headers to download the file instead of displaying it
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'attestation_'.$trainee->getFirstName().'.pdf'
        );

        return $response;
    }

    #[Route('/courses/addOneCustomerToformation', name: 'app_add_one_customers')]
    public function addOneCustomerToformation(Request $request,  EntityManagerInterface $entityManager): Response
    {

        $itemId = json_decode($request->request->get('selectedItem'), true);
        $formationId = $request->request->get('formationId');

        if ($formationId && $itemId) {
            $formation  = $entityManager->getRepository(Formation::class)->findOneBy(['id'=> $formationId]);
            $customer = $entityManager->getRepository(Customer::class)->findOneBy(['id'=> $itemId]);
            if(!empty($formation->getCustomers())) {
                foreach($formation->getCustomers() as $cust) {
                    if ($cust->getId() == $itemId) {
                        $object = new \stdClass();
                        $object->status = false;
                        $object->message = 'Client existe déjà';
                        return new Response(json_encode($object));
                    }
                }
            }
            if ($customer != null) {
                $formation->addCustomer($customer);
                $entityManager->persist($formation);
                $entityManager->flush();
                $object = new \stdClass();
                $object->status = true;
                $object->id = $customer->getId();
                $object->name = $customer->getName()?$customer->getName():'';
                $object->email = $customer->getEmail()?$customer->getEmail(): '';
                return new Response(json_encode($object));
            }
        }
        return new Response('false');
    }

    #[Route('/courses/deleteTCustomerFromFormation/{idFormation}/{idCustomer}', name: 'app_customer_formation_delete')]
    public function deleteCustomerFromFormation(Request $request, EntityManagerInterface $entityManager, $idFormation, $idCustomer ): Response
    {
        $formation = $entityManager->getRepository(Formation::class)->findOneBy(['id'=> $idFormation]);
        $customer = $entityManager->getRepository(Customer::class)->findOneBy(['id'=> $idCustomer]);
        if(!empty($formation->getCustomers())) {
            $formation->removeCustomer($customer);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_courses_manage', ['type' => $formation->getType(), 'idFormation' => $idFormation]);

    }

    #[Route('/courses/app_update_link_drive/{formationId}', name: 'app_update_link_drive')]
    public function updateDriveLink(Request $request,EntityManagerInterface $entityManager, $formationId): Response
    {
        $formation = $entityManager->getRepository(Formation::class)->findOneBy(['id' => $formationId]);
        $driveLink = $request->request->get('linkDrive');
        $formation->setLinkDrive($driveLink);
        $entityManager->persist($formation);
        $entityManager->flush();
        return new Response('true');

    }

}
