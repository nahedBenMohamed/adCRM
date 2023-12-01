<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\Link;
use App\Form\LinkFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LinkController extends AbstractController
{
    #[Route('/link', name: 'app_link')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $links = $entityManager->getRepository(Link::class)->findBy([],['id' => 'DESC']);
        return $this->render('link/index.html.twig', [
            'links' => $links,
        ]);
    }

    #[Route('/link/add', name: 'app_add_link')]
    public function addNewLink(EntityManagerInterface $entityManager, Request $request): Response
    {
        $link = new Link();
        $form = $this->createForm(LinkFormType::class, $link);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $link = $form->getData();
            $entityManager->persist($link);
            $entityManager->flush();
            return $this->redirectToRoute('app_link');
        }

        return $this->render('link/new.html.twig', [
            'linkForm' => $form->createView(),
        ]);
    }

    #[Route('/link/edit/{id}', name: 'app_edit_link')]
    public function editLink(EntityManagerInterface $entityManager, Request $request, $id): Response
    {
        $link = $entityManager->getRepository(Link::class)->findOneBy(['id' => $id]);
        $form = $this->createForm(LinkFormType::class, $link);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $link = $form->getData();
            $entityManager->persist($link);
            $entityManager->flush();
            return $this->redirectToRoute('app_link');
        }
        return $this->render('link/edit.html.twig', [
            'linkForm' => $form->createView(),
        ]);
    }

    #[Route('/link/delete/{id}', name: 'app_delete_link')]
    public function deleteLink(EntityManagerInterface $entityManager, $id): Response
    {
        $link = $entityManager->getRepository(Link::class)->findOneBy(['id' => $id]);
        //delete all formation that use this link before
        $fomationLinkToProgram = $entityManager->getRepository(Formation::class)->findOneBy(['linkToProgram' => $link]);
        $fomationLinkToLivretAccueil = $entityManager->getRepository(Formation::class)->findOneBy(['linkToLivretAccueil' => $link]);
        $fomationLinkGuide = $entityManager->getRepository(Formation::class)->findOneBy(['linkGuide' => $link]);
        $fomationLinkFormulaire = $entityManager->getRepository(Formation::class)->findOneBy(['linkFormulaire' => $link]);
        if($fomationLinkToProgram || $fomationLinkToLivretAccueil || $fomationLinkGuide || $fomationLinkFormulaire) {
            $this->addFlash(
                'danger',
                "Ce lien est utilisé dans des formations, vous devez supprimer les formations qu'ils utilisent avant");
        } else {
            $entityManager->remove($link);
            $entityManager->flush();
            $this->addFlash(
                'success',
                "Ce lien est supprimé avec succes");

        }
        return $this->redirectToRoute('app_link');
    }

}
