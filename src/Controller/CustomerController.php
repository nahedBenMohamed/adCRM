<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Formation;
use App\Entity\Trainee;
use App\Form\CustomerType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class CustomerController extends AbstractController
{
    #[Route('/customer', name: 'app_customer')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $customers = $entityManager->getRepository(Customer::class)->findBy([],['id' => 'DESC']);
        return $this->render('customer/index.html.twig', [
            'customers' => $customers,
        ]);
    }

    #[Route('/customer/add/{idFormation}', name: 'app_customer_add')]
    public function add(EntityManagerInterface $entityManager, Request $request, SluggerInterface $slugger, $idFormation = null): Response
    {
        $customer = new Customer();
        $form = $this->createForm(CustomerType::class,$customer);
        $form->handleRequest($request);
        /*$referer = $request->headers->get('referer');
        $redirectToFormation = false;
        $type = "inter";
        if($referer && str_contains($referer, 'courses/courseManagement/intra')) {
            $redirectToFormation = true;
            $type = "intra";
        }
        if($referer && str_contains($referer, 'courses/courseManagement/inter')) {
            $redirectToFormation = true;
            $type= "inter";
        }*/
        if ($form->isSubmitted() && $form->isValid()) {
            $infoFile = $form->get('infoFilename')->getData();
            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($infoFile) {
                $originalFilename = pathinfo($infoFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$infoFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $infoFile->move(
                        $this->getParameter('company_file_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $customer->setInfoFilename($newFilename);
            }
            $customer = $form->getData();
            $entityManager->persist($customer);
            $entityManager->flush();
            if($idFormation) {
                $formation = $entityManager->getRepository(Formation::class)->findOneBy(['id' => $idFormation]);
                return $this->redirectToRoute('app_courses_manage', ['type' => $formation->getType(), 'idFormation' => $idFormation]);
            }
            /*if($redirectToFormation) {
                return $this->redirectToRoute('app_courses_manage', ['type' => $type, 'idFormation' => null]);
            }*/
            return $this->redirectToRoute('app_customer');
        }

        return $this->render('customer/new.html.twig', [
            'newCustomer' => $form->createView(),
        ]);
    }

    #[Route('/customer/edit/{id}', name: 'app_customer_edit')]
    public function edit(EntityManagerInterface $entityManager, Request $request,SluggerInterface $slugger, $id): Response
    {
        $customer = $entityManager->getRepository(Customer::class)->findOneBy(['id' => $id]);
        $form = $this->createForm(CustomerType::class,$customer);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $infoFile = $form->get('infoFilename')->getData();
            if ($infoFile) {
                $originalFilename = pathinfo($infoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$infoFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $infoFile->move(
                        $this->getParameter('company_file_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $customer->setInfoFilename($newFilename);
            }
            $customer = $form->getData();
            $entityManager->persist($customer);
            $entityManager->flush();
            return $this->redirectToRoute('app_customer');
        }

        return $this->render('customer/edit.html.twig', [
            'newCustomer' => $form->createView(),
        ]);
    }

    #[Route('/customer/delete/{id}', name: 'app_delete_customer')]
    public function deleteCustomer(EntityManagerInterface $entityManager, $id): Response
    {
        $customer = $entityManager->getRepository(Customer::class)->findOneBy(['id' => $id]);
        $traineebyCustomers = $entityManager->getRepository(Trainee::class)->findBy(['customer' => $customer]);
        $object = new \stdClass();
        foreach ($traineebyCustomers as $tr) {
            $tr->removeCustomer($customer);
            $entityManager->persist($tr);
        }
        $entityManager->flush();
        $entityManager->remove($customer);
        $entityManager->flush();
        $object->status = true;
        $object->message = "Le client est supprimé avec succès";
        return new Response(json_encode($object));
    }

    #[Route('/customerCourse', name: 'app_customer_course')]
    public function customerForFormation(EntityManagerInterface $entityManager): Response
    {
        $courses = $entityManager->getRepository(Formation::class)->findBy(['type' =>'intra'],['id' => 'DESC']);
        $customerWithCourses = [];
        foreach ($courses as $formation) {
            $customers = $formation->getCustomers();
            if($customers[0]) {
                $customers[0]->formationId = $formation->getId();
                if(!in_array($customerWithCourses, array($customers[0]))) {
                    $customerWithCourses[] = $customers[0];
                }
            }
        }
        return $this->render('customer/customerWithFormation.html.twig', [
            'customers' => $customerWithCourses,
        ]);
    }

    #[Route('/customerFormation/{customerId}', name: 'app_customer_formation')]
    public function customerFormations(EntityManagerInterface $entityManager, $customerId): Response
    {
        $courses = $entityManager->getRepository(Formation::class)->findBy([],['id' => 'DESC']);
        $customerCourses = [];
        foreach ($courses as $formation) {
            if($formation->getType() == "intra") {
                $customers = $formation->getCustomers();
                if($customers[0]) {
                    if($customers[0]->getId() == $customerId) {
                        $customerCourses[] = $formation;
                    }
                }
            }
        }
        return $this->render('customer/formationOfCustomer.html.twig', [
            'courses' => $customerCourses,
            'alowAddNew' => true
        ]);
    }

    #[Route('/sendEvalChaud', name: 'app_send_eval_chaud')]
    public function sendEvalChaud(EntityManagerInterface $entityManager, Request $request, MailerInterface $mailer): Response
    {
        $subject = $request->request->get('subject');
        $text = $request->request->get('text');
        $formationId = $request->request->get('formationId');
        $formation  = $entityManager->getRepository(Formation::class)->findOneBy(['id'=> $formationId]);
        if($formation) {
            $customer = $formation->getCustomers();
            //sen mail for all customers
            foreach ($customer as $item) {
                $emailCustomer = $item->getEmail();
                $email = (new Email())
                    ->from('formation@adconseil.org')
                    ->subject($subject)
                    ->html($text)
                    ->to($emailCustomer);
                $mailer->send($email);
               // $this->addFlash('success', "L'evaluation à chaud a été envoyée au client avec succès.");
            }
        }
        return new Response('false');
    }

    #[Route('/sendEvalFroid', name: 'app_send_eval_froid')]
    public function sendEvalFroid(EntityManagerInterface $entityManager, Request $request, MailerInterface $mailer): Response
    {
        $subject = $request->request->get('subject');
        $text = $request->request->get('text');
        $formationId = $request->request->get('formationId');
        $formation  = $entityManager->getRepository(Formation::class)->findOneBy(['id'=> $formationId]);
        if($formation) {
            $customer = $formation->getCustomers();
            //sen mail for all customers
            foreach ($customer as $item) {
                $emailCustomer = $item->getEmail();
                $email = (new Email())
                    ->from('formation@adconseil.org')
                    ->subject($subject)
                    ->html($text)
                    ->to($emailCustomer);
                $mailer->send($email);
               // $this->addFlash('success', "L'evaluation à froid a été envoyée au client avec succès.");
            }
        }
        return new Response('true');
    }
}
