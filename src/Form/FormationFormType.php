<?php

namespace App\Form;

use App\Entity\Customer;
use App\Entity\Financier;
use App\Entity\Formation;
use App\Entity\User;
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
            ->add('nomFormation',   TextType::class, [
                'label' => 'Intitulé de la formation',
                'required' => true,
            ])
            ->add('timesheet',   TextType::class, [
                'label' => 'Dates et horaires',
                'required' => true,
            ])

            ->add('dateDebutFormation',   DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'html5' => false
            ])

            ->add('dateFinFormation',   DateType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'html5' => false
            ])

            ->add('dureeFormation',   TextType::class, [
                'label' => "Nombre total d'heures",
                'required' => true,
            ])

            ->add('lieuFormation',   ChoiceType::class, [
                'label' => 'Modalité',
                'required' => true,
                'choices'  => [
                    'Présentiel' => 'Présentiel',
                    'Distanciel' => 'Distanciel',
                    'Mixte' => 'Mixte',
                ],
                'expanded' => true,
                'multiple' => false
            ])

            ->add('lienFormation',   TextType::class, [
                'label' => 'Lien classe virtuelle',
                'required' => false,
            ])

            ->add('linkType',   ChoiceType::class, [
                'label' => 'Lien BBB/Lien externe',
                'required' => false,
                'choices'  => [
                    'Lien BBB' => 'LienBBB',
                    'Lien externe' => 'Lienexterne'
                ],
                'expanded' => true,
                'multiple' => false
            ])

            ->add('formationAddress',   TextType::class, [
                'label' => "Lieu de formation",
                'required' => false,
                'attr'=> ['class' => 'hidden']
            ])

            ->add('objective',   TextareaType::class, [
                'label' => "Objectifs de l'action de formation",
                'required' => false,
                'attr' => ['class' => 'tinymce-editor']
            ])
            ->add('infoTrainees',   TextareaType::class, [
                'label' => "Infos complémentaires pour les stagiaires",
                'required' => false,
                'attr' => ['class' => 'tinymce-editor']
            ])
            ->add('infoFormateur',   TextareaType::class, [
                'label' => "Infos complémentaires pour le formateur",
                'required' => false,
                'attr' => ['class' => 'tinymce-editor']
            ])
            ->add('infoCustomer',   TextareaType::class, [
                'label' => "Infos complémentaires pour le client",
                'required' => false,
                'attr' => ['class' => 'tinymce-editor']
            ])

            ->add('linkToProgram',   TextType::class, [
                'label' => 'Lien vers le programme',
                'required' => false,
            ])

            ->add('linkToLivretAccueil',   TextType::class, [
                'label' => "Lien vers le livret d'accueil",
                'required' => false,
            ])
            ->add('linkGuide',   TextType::class, [
                'label' => 'Guide de votre classe virtuelle',
                'required' => false,
            ])
            ->add('linkFormulaire',   TextType::class, [
                'label' => 'Formulaire de recueil des attentes',
                'required' => false,
            ])
            
            ->add('formateur', EntityType::class, [
                'class' => User::class,
                'choice_label' => function($user) {
                    return $user->getFirstName() . ' ' . $user->getLastName();
                },
                'label' => 'Formateur',
                'required' => false,
                'empty_data' => '',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                           ->andWhere('u.roles LIKE :val')
                            ->setParameter('val', '%ROLE_TEACHER%');
                },
            ])

            ->add('customer', EntityType::class, [
                'class' => Customer::class,
                'choice_label' => function($customer) {
                    return $customer->getFirstName() . ' ' . $customer->getLastName();
                },
                'label' => 'Client',
                'required' => false,
                'empty_data' => '',
            ])
            ->add('financier', EntityType::class, [
                'class' => Financier::class,
                'choice_label' => function($customer) {
                    return $customer->getName();
                },
                'label' => 'Financeur',
                'required' => false,
                'empty_data' => '',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
        ]);
    }
}
