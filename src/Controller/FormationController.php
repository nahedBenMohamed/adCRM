<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\Employeur;
use App\Entity\Formateur;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;


// Affichage de quelques informations
class FormationController extends AbstractController
{
  

    #[Route('/gestionnaire', name: 'app_gestionnaire')]
    public function viewFormation(EntityManagerInterface $entityManager): Response
    {
        $formations = $entityManager->getRepository(Formation::class)->findAll();
        $employ = $entityManager->getRepository(Employeur::class)->findAll();
        $formateurs = $entityManager->getRepository(Formateur::class)->findAll();
        return $this->render('views/view-gestionnaire.html.twig', [
            'formations' => $formations, 
            'employ' => $employ,
            'formateurs' => $formateurs,
        ]);

    }

    #[Route('/formation/{id}/stagiaires', name: 'formation_stagiaires')]
    public function showStagiaires(int $id,EntityManagerInterface $entityManager): Response
    {
       
        $formations = $entityManager->getRepository(Formation::class)->find($id);

        if (!$formations) {
            throw $this->createNotFoundException('Formation non trouvé.');
        }

        return $this->render('views/liste-stagiaire.html.twig', [
            'formation' => $formations,
        ]);
    }
}
