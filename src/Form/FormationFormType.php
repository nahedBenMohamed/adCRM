<?php

namespace App\Form;

use App\Entity\Customer;
use App\Entity\Financier;
use App\Entity\Formation;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
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
                'required' => true,
            ])

            ->add('dateFinFormation',   DateType::class, [
                'label' => 'Date de fin',
                'required' => true,
            ])

            ->add('dureeFormation',   TextType::class, [
                'label' => "Nombre total d'heures",
                'required' => true,
            ])

            ->add('adresseFormation',   TextType::class, [
                'label' => 'Lieu ou modalité',
                'required' => true,
            ])

            ->add('lienFormation',   TextType::class, [
                'label' => 'Lien ZOOM',
                'required' => false,
            ])

            ->add('zoomAccount',   TextType::class, [
                'label' => 'Compte ZOOM utilisé',
                'required' => false,
            ])

            ->add('signatureAddress',   TextType::class, [
                'label' => "Lieu de signature de l'attestation par le formateur",
                'required' => false,
            ])

            ->add('objective',   TextareaType::class, [
                'label' => "Objectifs de l'action de formation",
                'required' => false,
                'attr' => ['rows' => '10']
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
