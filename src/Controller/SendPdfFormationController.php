<?php
namespace App\Controller;

use Dompdf\Dompdf;
use App\Entity\Formateur;
use App\Entity\Formation;
use App\Repository\FormateurRepository;
use App\Repository\FormationRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class SendPdfFormationController extends AbstractController 
{
    #[Route('/ninho', name: 'pdf')]
    public function sendPdfFormation( Formation $formation,Request $request):Response
    {
        # code...
        $form = $this->createFormBuilder()
        ->add('formation',EntityType::class,[
            'class'=> Formation::class,
            'choice_label' => 'nomFormation',
            'placeholder' => 'Choisissez une formation',
            'query_builder' => function(FormationRepository $formationRepository){
                return $formationRepository->createQueryBuilder('f')->orderBy('f.nomFormation', 'ASC');
            }
        ])
        ->add('formateur',EntityType::class,[
            'class'=> Formateur::class,
            'choice_label' => 'nom',
            'placeholder' => 'Sélectionner le formateur',
            'query_builder' => function(FormateurRepository $formateurRepository){
                return $formateurRepository->createQueryBuilder('f')->orderBy('f.nom', 'ASC');
            }
        ])
        ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $formdata = [
                'nomFormation' => $formation->getNomFormation(),
                'dureeFormation' => $formation->getDureeFormation(),
                'domaineFormation' => $formation->getDomaineFormation(),
    
                // ... autres données du formulaire
            ];
            $nomFormation = $formation->getNomFormation(); 


            $html = $this->renderView('pdf/index.html.twig', [
                'formdata' => $formdata,
            ]);
    
            // Générer le PDF avec Dompdf
            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            dd($form);
        }
        return $this->render('home/test.html.twig', [
            'formSend' => $form->createView(),
        ]);
    }
}
