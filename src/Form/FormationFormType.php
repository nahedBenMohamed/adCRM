<?php

namespace App\Form;

use App\Entity\Customer;
use App\Entity\Financier;
use App\Entity\Formation;
use App\Entity\Link;
use App\Entity\User;
use App\Repository\LinkRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomFormation',   TextType::class,
                ['required' => true,
                 'label' => false,
                 'attr' => ['placeholder' => 'Intitulé de la formation']
                ]
            )
            ->add('timesheet',   TextareaType::class, [
                'label' => 'Dates et horaires',
                'required' => false,
                'attr' => ['class' => 'tinymce-editor', 'placeholder' => 'Dates et horaires']
            ])

            ->add('dateDebutFormation',   DateType::class, [
                'label' => false,
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'form-control js-datepicker', 'placeholder' => 'Date de début', 'autocomplete' => 'off'],
            ])

            ->add('dateFinFormation',   DateType::class, [
                'label' => false,
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'form-control js-datepicker', 'placeholder' => 'Date de fin', 'autocomplete' => 'off'],
            ])

            ->add('dureeFormation',   TextType::class, [
                'label' => false,
                'required' => true,
                'attr' => ['placeholder' => "Nombre total d'heures"],
            ])

            ->add('lieuFormation',   ChoiceType::class, [
                'label' => false,
                'required' => true,
                'choices'  => [
                    'Présentiel' => 'Présentiel',
                    'Distanciel' => 'Distanciel',
                    'Mixte' => 'Mixte',
                ],
                'expanded' => true,
                'multiple' => false,
                'empty_data'  => null,
                'attr' => ['placeholder' => "Modalité"]
            ])

            ->add('lieuSignature',   TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => "Lieu de Signature"]

            ])

            ->add('lienFormation',   TextType::class, [
                'label' => false,
                'required' => false,
                'empty_data' => '',
                'attr' => ['placeholder' => "Lien classe virtuelle"]

            ])

            ->add('linkType',   ChoiceType::class, [
                'label' => false,
                'required' => false,
                'choices'  => [
                    'Lien BBB' => 'LienBBB',
                    'Lien externe' => 'Lienexterne'
                ],
                'expanded' => true,
                'multiple' => false,
                'attr' => ['placeholder' => "Lien BBB/Lien externe"]
            ])

            ->add('formationAddress',   TextareaType::class, [
                'label' => 'Lieu de formation',
                'required' => false,
                'empty_data' => '',
                'attr'=> ['class' => 'tinymce-editor hidden', 'placeholder' => "Lieu de formation"]
            ])

            ->add('objective',   TextareaType::class, [
                'label' => "Objectifs de l'action de formation",
                'required' => false,
                'attr' => ['class' => 'tinymce-editor', 'placeholder' => "Objectifs de l'action de formation"]
            ])

            ->add('linkToProgram',   EntityType::class, [
                'class' => Link::class,
                'query_builder' => function (LinkRepository $er) {
                    return $er->createQueryBuilder('l')
                        ->where('l.linkStatus = 0');
                },
                'choice_label' => function($link) {
                if($link->getName() == 'lien statique')
                    return $link->getValue().'##'.$link->getName() ;
                    else return $link->getName();
                },
                'label' => false,
                'required' => false,
                'empty_data' => '',
                'placeholder' => "Lien vers le programme"
            ])

            ->add('linkToLivretAccueil',   EntityType::class, [
                'class' => Link::class,
                'query_builder' => function (LinkRepository $er) {
                    return $er->createQueryBuilder('l')
                        ->where('l.linkStatus = 0');
                },
                'choice_label' => function($link) {
                    return $link->getName();
                },
                'label' => false,
                'required' => false,
                'empty_data' => '',
                'placeholder' => "Lien vers le livret d'accueil"
            ])
            ->add('linkGuide',   EntityType::class, [
                'class' => Link::class,
                'query_builder' => function (LinkRepository $er) {
                    return $er->createQueryBuilder('l')
                        ->where('l.linkStatus = 0');
                },
                'choice_label' => function($link) {
                    return $link->getName();
                },
                'label' => false,
                'required' => false,
                'empty_data' => '',
                'placeholder' => "Guide de votre classe virtuelle"
            ])
            ->add('linkFormulaire',   EntityType::class, [
                'class' => Link::class,
                'query_builder' => function (LinkRepository $er) {
                    return $er->createQueryBuilder('l')
                        ->where('l.linkStatus = 0');
                },
                'choice_label' => function($link) {
                    return $link->getName();
                },
                'label' => false,
                'required' => false,
                'empty_data' => '',
                'placeholder' => "Formulaire de recueil des attentes"
            ])
            
            ->add('formateur', EntityType::class, [
                'class' => User::class,
                'choice_label' => function($user) {
                    return $user->getFirstName() . ' ' . $user->getLastName();
                },
                'choice_attr' => function ($object) {
                    return ['data-link' => $object->getLinkFormateur()];
                },
                'label' => false,
                'required' => true,
                'empty_data' => '',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                           ->andWhere('u.roles LIKE :val')
                            ->setParameter('val', '%ROLE_TEACHER%');
                },
                'placeholder' => "Formateur"
            ])
            ->add('financier', EntityType::class, [
                'class' => Financier::class,
                'choice_label' => function($customer) {
                    return $customer->getName();
                },
                'label' => false,
                'required' => false,
                'empty_data' => '',
                'placeholder' => 'Financeur'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
        ]);
    }
}
