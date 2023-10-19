<?php

namespace App\Controller;


use App\Form\FormateurType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


// Classe à modifier comme StagiaireController.php pour envoyer les PDF aux formateurs

class FormateurController extends AbstractController
{
    #[Route('/formateur/pdf', name: 'app_formateur')]
    public function index(Request $request): Response
    {
        $forms = $this->createForm(FormateurType::class);
        $f = $forms->handleRequest($request);
        //dd($formateur);
        return $this->render('formateur/convocation-formateur.html.twig', [
            'formationGenerate' => $f->createView(),
        ]);
    }
}
