<?php

namespace App\Controller;

use App\Entity\Company;
use App\Form\CompanyFormType;
use App\Form\LinkFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CompanyController extends AbstractController
{
    #[Route('/company', name: 'app_company')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $companies = $entityManager->getRepository(Company::class)->findBy([],['id' => 'DESC']);
        return $this->render('company/index.html.twig', [
            'companies' => $companies,
        ]);
    }
    #[Route('/company/add', name: 'app_add_company')]
    public function addNewCompany(EntityManagerInterface $entityManager, Request $request): Response
    {
        $company = new Company();
        $form = $this->createForm(CompanyFormType::class, $company);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $company = $form->getData();
            $entityManager->persist($company);
            $entityManager->flush();
            return $this->redirectToRoute('app_company');
        }

        return $this->render('company/new.html.twig', [
            'companyForm' => $form->createView(),
        ]);
    }

    #[Route('/company/edit/{id}', name: 'app_edit_company')]
    public function editCompany(EntityManagerInterface $entityManager, Request $request, $id): Response
    {
        $company = $entityManager->getRepository(Company::class)->findOneBy(['id' => $id]);
        $form = $this->createForm(CompanyFormType::class, $company);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $company = $form->getData();
            $entityManager->persist($company);
            $entityManager->flush();
            return $this->redirectToRoute('app_company');
        }
        return $this->render('company/edit.html.twig', [
            'companyForm' => $form->createView(),
        ]);
    }
}
