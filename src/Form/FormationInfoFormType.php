<?php

namespace App\Form;

use App\Entity\Formation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormationInfoFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('infoTrainees',   TextareaType::class, [
                'label' => "Infos complémentaires pour les stagiaires",
                'required' => false,
                'empty_data' => '',
                'attr' => ['class' => 'tinymce-editor']
            ])
            ->add('infoFormateur',   TextareaType::class, [
                'label' => "Infos complémentaires pour le formateur",
                'required' => false,
                'empty_data' => '',
                'attr' => ['class' => 'tinymce-editor']
            ])
            ->add('infoCustomer',   TextareaType::class, [
                'label' => "Infos complémentaires pour le client",
                'required' => false,
                'empty_data' => '',
                'attr' => ['class' => 'tinymce-editor']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
        ]);
    }
}
