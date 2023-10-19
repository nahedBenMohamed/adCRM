<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{

    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/gestionnaire', name: 'vue_gestionnaire')]
    public function viewGestionnaire(): Response
    {
        return $this->render('views/view-gestionnaire.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    // Les stagiaires des Formateurs
    #[Route('/formateur', name: 'vue_formateur')]
    public function viewFormateur(): Response
    {
        return $this->render('views/view-formateur.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/liste_stagiaire', name: 'vue_liste_stagiaire')]
    public function viewListeStagiaire(): Response
    {
        return $this->render('views/liste-stagiaire.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    // Vue pour déposer des informations du Stagiaire
    #[Route('/info_stagiaire', name: 'redirection_route')]
    //public function redirectionAction(Request $request)
    //{
        //$id = $request->request->get('id');
        //return $this->redirectToRoute('views/view-info-stagiaire.html.twig', ['id' => $id]);
    //}
    public function viewInfoStagiaire(): Response
    {
        return $this->render('views/view-info-stagiaire.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/compte_user', name: 'vue_compte_user')]
    public function viewCompteUser(): Response
    {
        return $this->render('views/view-compte-user.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/emargement-formation', name: 'vue_emargement_formation')]
    public function viewEmargementFormation(): Response
    {
        return $this->render('views/view-emargement.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

}
