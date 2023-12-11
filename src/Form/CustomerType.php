<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Customer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Customer::class,
        ]);
    }
}
