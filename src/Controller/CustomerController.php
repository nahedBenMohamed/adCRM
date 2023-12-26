<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Formation;
use App\Form\CustomerType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    public function add(EntityManagerInterface $entityManager, Request $request): Response
    {
        $customer = new Customer();
        $form = $this->createForm(CustomerType::class,$customer);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
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
    public function edit(EntityManagerInterface $entityManager, Request $request, $id): Response
    {
        $customer = $entityManager->getRepository(Customer::class)->findOneBy(['id' => $id]);
        $form = $this->createForm(CustomerType::class,$customer);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
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
        $teacherFormation = $entityManager->getRepository(Formation::class)->findOneBy(['customer' => $customer]);
        $object = new \stdClass();
        if ($teacherFormation) {
            $object->status = false;
            $object->message = "Ce client est enregistré dans une formation et il est impossible de le supprimer.";
        } else {
            $entityManager->remove($customer);
            $entityManager->flush();
            $object->status = true;
            $object->message = "Le client est supprimé avec succès";
        }
        return new Response(json_encode($object));
    }
}
