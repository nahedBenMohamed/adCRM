<?php

namespace App\Controller;

use App\Entity\Financier;
use App\Entity\Formation;
use App\Form\FinancierFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FinancierController extends AbstractController
{
    #[Route('/financier', name: 'app_financier')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $financiers = $entityManager->getRepository(Financier::class)->findBy([],['id' => 'DESC']);
        return $this->render('financier/index.html.twig', [
            'financiers' => $financiers,
        ]);
    }

    #[Route('/financier/add', name: 'app_financier_add')]
    public function add(EntityManagerInterface $entityManager, Request $request): Response
    {
        $financier = new Financier();
        $form = $this->createForm(FinancierFormType::class, $financier);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $financier = $form->getData();
            $entityManager->persist($financier);
            $entityManager->flush();
            return $this->redirectToRoute('app_financier');
        }

        return $this->render('financier/new.html.twig', [
            'financier' => $form->createView(),
        ]);
    }

    #[Route('/financier/edit/{id}', name: 'app_financier_edit')]
    public function edit(EntityManagerInterface $entityManager, Request $request, $id): Response
    {
        $financier = $entityManager->getRepository(Financier::class)->findOneBy(['id' => $id]);
        $form = $this->createForm(FinancierFormType::class,$financier);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $financier = $form->getData();
            $entityManager->persist($financier);
            $entityManager->flush();
            return $this->redirectToRoute('app_financier');
        }

        return $this->render('financier/edit.html.twig', [
            'financier' => $form->createView(),
        ]);
    }
    #[Route('/financier/delete/{id}', name: 'app_delete_financier')]
    public function deleteFinancier(EntityManagerInterface $entityManager, $id): Response
    {
        $financier = $entityManager->getRepository(Financier::class)->findOneBy(['id' => $id]);
        $financierFormation = $entityManager->getRepository(Formation::class)->findOneBy(['financier' => $financier]);
        $object = new \stdClass();
        if ($financierFormation) {
            $object->status = false;
            $object->message = "Ce financeur est enregistré dans une formation et il est impossible de le supprimer.";
        } else {
            $entityManager->remove($financier);
            $entityManager->flush();
            $object->status = true;
            $object->message = "Le financeur est supprimé avec succès";
        }
        return new Response(json_encode($object));
    }
}
