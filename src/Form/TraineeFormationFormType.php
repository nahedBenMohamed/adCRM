<?php

namespace App\Form;

use App\Entity\Trainee;
use App\Entity\TraineeFormations;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TraineeFormationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('formation',   TextType::class, [
                'label' => 'Nom de la formation',
                'required' => true,
                'disabled' => true,
            ])
            ->add('trainee', EntityType::class, [
                'class' => Trainee::class,
                'choice_label' => function($trainee) {
                    return $trainee->getFirstName() . ' ' . $trainee->getLastName();
                },
                'label' => 'Stagiaires',
                'required' => true,
                'multiple' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TraineeFormations::class,
        ]);
    }
}
