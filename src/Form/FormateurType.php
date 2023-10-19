<?php

namespace App\Form;

use App\Entity\Formateur;
use App\Entity\Formation;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormateurType extends AbstractType
{
  
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('formateur',EntityType::class,[
                'class'=> Formateur::class,
                'label' =>'-- Formateur --',
                'mapped' => false,
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionner le formateur',
                'required'=> 'false'
            ])
            ->add('formation', EntityType::class,[
                'class' => Formation::class,
                'label' => '-- Formation --',
                'choice_label' => 'nomFormation',
                'placeholder' => 'Choisissez un formateur AVANT',
                'required'=> 'false'

            ])
            ->add('message',   TextareaType::class,[
                'label' =>'Message',
                'mapped' => false,
            ])
            ->add('valider', SubmitType::class)
            ;
            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formateur::class,
        ]);
    }
}