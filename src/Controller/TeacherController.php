<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Formation;
use App\Entity\Link;
use App\Entity\Trainee;
use App\Entity\TraineeFormation;
use App\Form\CompanyFormType;
use App\Form\FormationFormType;
use App\Form\FormationInfoFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class TeacherController extends AbstractController
{
    #[Route('/trainer', name: 'app_trainer')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $trainer = $this->getUser();
        $courses = $entityManager->getRepository(Formation::class)->findBy(['formateur' => $trainer],['id' => 'DESC']);
        return $this->render('teacher/index.html.twig', [
            'courses' => $courses
        ]);
    }
    #[Route('/trainer/course/{idCompany}/{idFormation}', name: 'app_trainer_course', requirements: ['idCompany' => '\d+'])]
    public function courseForTrainer(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, $idCompany = null, $idFormation = null ): Response
    {
        if ($idCompany && $idFormation) {
            //get company
            $company = $entityManager->getRepository(Company::class)->findOneBy(['id' => $idCompany]);
            $formCompany = $this->createForm(CompanyFormType::class, $company);
            $formCompany->handleRequest($request);

            $course2 =  $entityManager->getRepository(Formation::class)->find($idFormation);
            $form = $this->createForm(FormationFormType::class, $course2, ['allow_extra_fields' =>true]);
            $form->handleRequest($request);

            $formInfo = $this->createForm(FormationInfoFormType::class, $course2, ['allow_extra_fields' =>true]);
            $formInfo->handleRequest($request);
            $formationUser = $entityManager->getRepository(TraineeFormation::class)->findBy(['formation'=>$course2]);
            $trainees = [];
            $AllTrainees = $entityManager->getRepository(Trainee::class)->findBy([],['id' => 'DESC']);
            foreach ($formationUser as $item) {
                array_push($trainees, $item->getTrainee() );
            }
            $defaultData = ['mailFormateurText' => $course2->getMailFormateurText()];
            $formMail = $this->createFormBuilder($defaultData)
                ->add('mailFormateurText', TextareaType::class,[
                    'label' =>"Contenu de l'email",
                    'required' => false
                ])
                ->getForm();

            $formMail->handleRequest($request);
            if ($formMail->isSubmitted() && $formMail->isValid()) {
                $data = $formMail->getData();
                if($data['mailFormateurText'] !== null) {
                    $course2->setMailFormateurText($data['mailFormateurText']);
                } else {
                    $course2->setMailFormateurText('');
                }

                $entityManager->persist($course2);
                $entityManager->flush();
                return $this->redirectToRoute('app_trainer_course', ['idCompany' => $course2->getCompany()->getId(), 'idFormation' => $idFormation]);
            }
            return $this->render('teacher/showFormation.html.twig', [
                'formationForm' => $form->createView(),
                'companyForm' => $formCompany->createView(),
                'formation' => $course2,
                'trainees' => $trainees,
                'formInfo' => $formInfo->createView(),
                'allTrainees' => $AllTrainees,
                'idCompany' => $idCompany,
                'idFormation' =>  $course2->getId(),
                'formMail' => $formMail->createView()
            ]);
        }

    }
}
