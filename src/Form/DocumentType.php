<?php

namespace App\Form;

use App\Entity\Documents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => [
                    'Guide Référentiel National Qualité QUALIOPI' => 'qualite',
                    'Check-list des incontournables des formateurs·trices' => 'checkLisFormateur',
                    'Livret d’accueil des stagiaires et règlement intérieur' => 'livretAccueil',
                    'Processus interne de mise en œuvre des formations' => 'processusInterne',
                    'Processus d’amélioration continue des formations professionnelles' => 'processusAmelioration',
                    'Autre' => 'autre',
                ],
                'placeholder' => '-- Sélectionner --',
                'required' => true,
            ])
            ->add('filePath', FileType::class, [
                'label' => 'Fichier',
                'mapped' => false, // important if the file is not automatically saved to the entity
                'required' => false,
            ])
            ->add('fileLink', UrlType::class, [
                'label' => 'Lien de document',
                'mapped' => false, // ✅ Not mapped to entity
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Documents::class,
        ]);
    }
}
