<?php

namespace App\Controller;

use App\Entity\Documents;
use App\Entity\Formation;
use App\Entity\Link;
use App\Entity\Trainee;
use App\Entity\TraineeFormation;
use App\Form\DocumentType;
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

    #[Route('/documentsTrainer', name: 'app_trainer_documents')]
    public function documentsTrainer(EntityManagerInterface $entityManager): Response
    {
        $nbdocQualite = $entityManager->getRepository(Documents::class)->count(['category' => 'qualite']);
        $nbdocCheckLisFormateur = $entityManager->getRepository(Documents::class)->count(['category' => 'checkLisFormateur']);
        $nbdocLivretAccueil = $entityManager->getRepository(Documents::class)->count(['category' => 'livretAccueil']);
        $nbdocProcessusInterne = $entityManager->getRepository(Documents::class)->count(['category' => 'processusInterne']);
        $nbdocProcessusAmelioration = $entityManager->getRepository(Documents::class)->count(['category' => 'processusAmelioration']);
        $nbdocAutre = $entityManager->getRepository(Documents::class)->count(['category' => 'autre']);

        return $this->render('teacher/documents.html.twig', [
            'nbdocQualite'=>$nbdocQualite,
            'nbdocCheckLisFormateur' => $nbdocCheckLisFormateur,
            'nbdocLivretAccueil'=> $nbdocLivretAccueil,
            'nbdocProcessusInterne' => $nbdocProcessusInterne,
            'nbdocProcessusAmelioration' =>$nbdocProcessusAmelioration,
            'nbdocAutre' => $nbdocAutre
        ]);
    }

    #[Route('/documentsAdmin', name: 'app_admin_documents')]
    public function documentsAdmin(EntityManagerInterface $entityManager, Request $request): Response
    {

        $document = new Documents();

        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);
        $nbdocQualite = $entityManager->getRepository(Documents::class)->count(['category' => 'qualite']);
        $nbdocCheckLisFormateur = $entityManager->getRepository(Documents::class)->count(['category' => 'checkLisFormateur']);
        $nbdocLivretAccueil = $entityManager->getRepository(Documents::class)->count(['category' => 'livretAccueil']);
        $nbdocProcessusInterne = $entityManager->getRepository(Documents::class)->count(['category' => 'processusInterne']);
        $nbdocProcessusAmelioration = $entityManager->getRepository(Documents::class)->count(['category' => 'processusAmelioration']);
        $nbdocAutre = $entityManager->getRepository(Documents::class)->count(['category' => 'autre']);
        if ($form->isSubmitted() && $form->isValid()) {
            // gérer l'upload de fichier ici si nécessaire
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form->get('filePath')->getData();

            if ($uploadedFile) {
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();

                try {
                    $uploadedFile->move(
                        $this->getParameter('company_file_directory'), // define this in services.yaml
                        $newFilename
                    );
                } catch (FileException $e) {
                    throw new \Exception('File upload failed');
                }

                // SET the filePath on the entity manually:
                $document->setFilePath($newFilename);
            }

            $entityManager->persist($document);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_documents');
        }

        return $this->render('views/documents.html.twig', [
            'form' => $form->createView(),
            'nbdocQualite'=>$nbdocQualite,
            'nbdocCheckLisFormateur' => $nbdocCheckLisFormateur,
            'nbdocLivretAccueil'=> $nbdocLivretAccueil,
            'nbdocProcessusInterne' => $nbdocProcessusInterne,
            'nbdocProcessusAmelioration' =>$nbdocProcessusAmelioration,
            'nbdocAutre' => $nbdocAutre
        ]);
    }

    #[Route('/getDocuments/{category}', name: 'app_admin_get_documents')]
    public function customerFormations(EntityManagerInterface $entityManager, $category): Response
    {
        $documents = $entityManager->getRepository(Documents::class)->findBy(['category' => $category],['id' => 'DESC']);
        $isTeacher = false;
        if (in_array('ROLE_TEACHER', $this->getUser()->getRoles(), true)) {
            $isTeacher = true;
        }
        return $this->render('views/documentsByCategory.html.twig', [
            'documents' => $documents,
            'base_template' => $isTeacher ? 'baseTeacher.html.twig' : 'baseAdmin.html.twig',
            'isTeacher' => $isTeacher
        ]);
    }

    #[Route('/document/delete/{id}', name: 'app_delete_document')]
    public function deleteDocument(EntityManagerInterface $entityManager, $id): Response
    {
        $document = $entityManager->getRepository(Documents::class)->findOneBy(['id' => $id]);
        //delete all formation that use this link before
        $object = new \stdClass();
        $entityManager->remove($document);
        $entityManager->flush();
        $object->status = true;
        $object->message ="Ce document est supprimé avec succes";
        return new Response(json_encode($object));
    }
}
