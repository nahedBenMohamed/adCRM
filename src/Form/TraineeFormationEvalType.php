<?php

namespace App\Form;

use App\Entity\Trainee;
use App\Entity\TraineeFormation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TraineeFormationEvalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
            $builder
                ->add('knowledge',   ChoiceType::class, [
                    'label' => 'Connaissances',
                    'required' => false,
                    'choices'  => [
                        'Peu ou pas maîtrisé' => '0' ,
                        'Maîtrisé partiellement' => '1',
                        'Maîtrisé totalement' =>'2',
                        'N. C.' => '3',
                    ],
                    'expanded' => true,
                    'multiple' => false,
                    'empty_data'  => '',
                    'placeholder' => false
                ])
                ->add('skills',   ChoiceType::class, [
                    'label' => 'Compétences',
                    'required' => false,
                    'choices'  => [
                        'Peu ou pas maîtrisé' => '0' ,
                        'Maîtrisé partiellement' => '1',
                        'Maîtrisé totalement' =>'2',
                        'N. C.' => '3',
                    ],
                    'expanded' => true,
                    'multiple' => false,
                    'empty_data'  => '',
                    'placeholder' => false
                ])
                ->add('comments',   TextareaType::class, [
                    'label' => 'Commentaires',
                    'required' => false,
                ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TraineeFormation::class,
        ]);
    }
}
