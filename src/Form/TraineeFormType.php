<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Customer;
use App\Entity\Trainee;
use http\Client;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\textType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TraineeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [ 'required' => true])
            ->add('firstName',   textType::class,[
                'label' =>'Nom',
                'required' => false,
            ])

            ->add('firstName',   textType::class,[
                'label' =>'Prénom',
                'required' => false,
            ])

            ->add('lastName',   textType::class,[
                'label' =>'Nom',
                'required' => false,
            ])
            ->add('civility', ChoiceType::class, [
                'label' =>'Civilité',
                'required' => false,
                'choices'  => [
                    'Monsieur' => 'Monsieur',
                    'Madame' => 'Madame'
                ]
            ])

            ->add('position',   textType::class,[
                'label' =>'Fonction',
                'required' => false,
            ])
            ->add('tel',   textType::class,[
                'label' =>'Téléphone',
                'required' => false,
            ])
            ->add('customer',   EntityType::class, [
                'class' => Customer::class,
                'choice_label' => function($client) {
                    return $client->getName();
                },
                'label' => 'Entreprise/Organisme',
                'required' => false,
                'empty_data' => ''
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trainee::class,
        ]);
    }
}
