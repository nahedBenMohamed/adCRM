<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Customer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CustomerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label'=>'Nom',
                'required' => true
            ])
            ->add('lastName', TextType::class, [
                'label'=>'Prénom',
                'required' => true
            ])
            ->add('email',   EmailType::class,[
                'label' =>'Email',
                'required' => false,
            ])
            ->add('position',   textType::class,[
                'label' =>'Fonction',
                'required' => false,
            ])
            ->add('company',   EntityType::class, [
                'class' => Company::class,
                'choice_label' => function($company) {
                    return $company->getName();
                },
                'label' => 'Entreprise/Organisme',
                'required' => false,
                'empty_data' => ''
            ])
            ->add('infoFilename', FileType::class, [
                'label' => 'Fiche de liaison (PDF file)',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using attributes
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '1024M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un document PDF valide',
                    ])
                ],
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Customer::class,
        ]);
    }
}
