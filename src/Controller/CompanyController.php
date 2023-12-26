<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Customer;
use App\Entity\Trainee;
use App\Form\CompanyFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

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
    public function addNewCompany(EntityManagerInterface $entityManager, Request $request, SluggerInterface $slugger): Response
    {
        $company = new Company();
        $form = $this->createForm(CompanyFormType::class, $company);
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
                        $this->getParameter('company_file_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $company->setInfoFilename($newFilename);
            }
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
    public function editCompany(EntityManagerInterface $entityManager, Request $request, SluggerInterface $slugger, $id): Response
    {
        $company = $entityManager->getRepository(Company::class)->findOneBy(['id' => $id]);
        $form = $this->createForm(CompanyFormType::class, $company);
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
                $company->setInfoFilename($newFilename);
            }
            $company = $form->getData();
            $entityManager->persist($company);
            $entityManager->flush();
            return $this->redirectToRoute('app_company');
        }
        return $this->render('company/edit.html.twig', [
            'companyForm' => $form->createView(),
        ]);
    }
    #[Route('/company/delete/{id}', name: 'app_delete_company')]
    public function deleteCompany(EntityManagerInterface $entityManager, $id): Response
    {
        $company = $entityManager->getRepository(Company::class)->findOneBy(['id' => $id]);
        $customerCompany = $entityManager->getRepository(Customer::class)->findOneBy(['company' => $company]);
        $traineeCompany = $entityManager->getRepository(Trainee::class)->findOneBy(['company' => $company]);
        $object = new \stdClass();
        if ($customerCompany) {
            $object->status = false;
            $object->message = "Un client est affecté à cette entreprise, il est impossible de le supprimer.";
        } else if($traineeCompany) {
            $object->status = false;
            $object->message = "Un stagiaire est affecté à cette entreprise, il est impossible de le supprimer.";
        } else {
            $entityManager->remove($company);
            $entityManager->flush();
            $object->status = true;
            $object->message = "L'entreprise est supprimée avec succès";
        }
        return new Response(json_encode($object));
    }
}
