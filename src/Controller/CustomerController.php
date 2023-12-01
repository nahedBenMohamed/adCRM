<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Form\CustomerType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

    #[Route('/customer/add', name: 'app_customer_add')]
    public function add(EntityManagerInterface $entityManager, Request $request, SluggerInterface $slugger): Response
    {
        $customer = new Customer();
        $form = $this->createForm(CustomerType::class,$customer);
        $form->handleRequest($request);
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
                        $this->getParameter('customer_file_directory'),
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

        return $this->render('customer/new.html.twig', [
            'newCustomer' => $form->createView(),
        ]);
    }

    #[Route('/customer/edit/{id}', name: 'app_customer_edit')]
    public function edit(EntityManagerInterface $entityManager, Request $request, SluggerInterface $slugger, $id): Response
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
                        $this->getParameter('customer_file_directory'),
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
}
