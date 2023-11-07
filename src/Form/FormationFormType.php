<?php

namespace App\Form;

use App\Entity\Formateur;
use App\Entity\Formation;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
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
                'label' => 'Nom de la formation',
                'required' => true,
            ])

            ->add('domaineFormation',   TextType::class, [
                'label' => 'Domaine de la formation',
                'required' => true,
            ])

            ->add('lienFormation',   TextType::class, [
                'label' => 'Lien de la formation',
                'required' => true,
            ])

            ->add('adresseFormation',   TextType::class, [
                'label' => 'Adresse de la formation',
                'required' => true,
            ])

            ->add('dureeFormation',   TextType::class, [
                'label' => 'Durée de la formation',
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
            
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => function($user) {
                    return $user->getFirstName() . ' ' . $user->getLastName();
                },
                'label' => 'Formateur',
                'required' => true, 
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.roles = :roles')
                        ->setParameter('roles', '["ROLE_TEACHER"]');
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
        ]);
    }
}
