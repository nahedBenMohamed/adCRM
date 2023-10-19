<?php

namespace App\Controller\Admin;

use App\Entity\Employeur;
use App\Entity\Formateur;
use App\Entity\Formation;
use App\Entity\Gestionnaire;
use App\Entity\Stagiaire;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// Dashboard généré avec EasyAsmin

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
    
         $adminUrlGenerator =$this->container->get(AdminUrlGenerator::class);
         return $this->redirect($adminUrlGenerator->setController(FormationCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Appli');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Formateur', 'fas fa-user-tie', Formateur::class);
        yield MenuItem::linkToCrud('Stagiaire', 'fas fa-graduation-cap', Stagiaire::class);
        yield MenuItem::linkToCrud('Employeur', 'fas fa-user-circle', Employeur::class);
        yield MenuItem::linkToCrud('Gestionnaire', 'fas fa-user-lock', Gestionnaire::class);
    }
}
