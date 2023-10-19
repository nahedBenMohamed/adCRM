<?php

namespace App\Controller;


use App\Entity\Stagiaire;
use App\Entity\Formation;
use App\Repository\FormationRepository;
use App\Repository\StagiaireRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// Classe qui permet d'envoyer la convocation par mail 
// Utilisation de MailerInterface

class StagiaireController extends AbstractController
{
    #[Route('/stagiaire/pdf', name: 'app_stagiaire')]
    public function index(Request $request,MailerInterface $mailerInterface,Formation $formation,Stagiaire $stagiaire): Response
    {
    $dataS = null;
    $dataF = null;
        $forms = $this->createFormBuilder()
        ->add('stagiaire',EntityType::class,[
            'class'=> Stagiaire::class,
            'placeholder' => 'Sélectionner le Stagiaire',
            'label' =>'Choisissez le Stagiaire',
            'choice_label' => function (Stagiaire $stagiaire): string {
                return $stagiaire->getNom(). ' ' .$stagiaire->getPrenom();
            },
            'query_builder' => function(StagiaireRepository $stagiaireRepository){
                return $stagiaireRepository->createQueryBuilder('f')->orderBy('f.nom', 'ASC');
            }
            ])
            ->add('formation',EntityType::class,[
                'class'=> Formation::class,
                'label' =>'Formateur',
                'choice_label' => 'nomFormation',
                'placeholder' => 'Sélectionner la Formation',
                'required'=> 'false',
                'query_builder' => function(FormationRepository $formationRepository){
                    return $formationRepository->createQueryBuilder('f')->orderBy('f.nomFormation', 'ASC');
                }
            ])
            ->add('message',   TextareaType::class,[
                'label' =>'Message',
                'mapped' => false,
            ])
            ->add('valider', SubmitType::class)
            ->getForm();
        
        $forms->handleRequest($request);

        if ($forms->isSubmitted() && $forms->isValid()) {
            
            # code...
            // Stockage des données reçues dans les variables
            $formData = $forms->getData();
            $stagiaire = $formData['stagiaire'];
            $formation = $formData['formation'];
            $dataF = [
                'nomFormation' => $formation->getNomFormation(),
                'nom' => $formation->getFormateur(),
                'dateDebutFormation' => $formation->getDateDebutFormation()->format('Y-m-d H:i:s'),
                'dateFinFormation' => $formation->getDateFinFormation()->format('Y-m-d H:i:s'),
                'modaliteFormation' => $formation->getModaliteFormation(),
                'lienFormation' => $formation->getLienFormation()
                // ... autres données du formulaire
            ];
            $dataS= [
                'nom' => $stagiaire->getNom(),
                'Prenom' => $stagiaire->getPrenom(),
                'entreprise' => $stagiaire->getEntreprise(),
                
                // ... autres données du formulaire
            ];

            $mailStagiaire = $stagiaire->getEmail();

            $email = (new TemplatedEmail())
            ->from('seoulouaimeedaisy@gmail.com')
            ->to($mailStagiaire)
            ->subject('Thanks for signing up!')
        
            // path of the Twig template to render
            ->htmlTemplate('emails/convocation.html.twig')
        
            // pass variables (name => value) to the template
            ->context([
                'expiration_date' => new \DateTime('+7 days'),
                'username' => 'foo',
                'dataS' => $dataS,
                'dataF' => $dataF,
            ])
        ;

            $mailerInterface->send($email);
            
        }
        return $this->render('stagiaire/convocation-stagiaire.html.twig', [
            'convocationGenerate' => $forms->createView(),
            
        ]);
    }
}
