<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\textType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [ 'required' => true])
            ->add('firstName',   textType::class,[
                'label' =>'Nom'
            ])

            ->add('lastName',   textType::class,[
                'label' =>'Prénom',
            ])

            ->add('phone',   textType::class,[
                'label' =>'Télephone',
                'mapped' => false,
                'required' => false
            ])

            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => true,
                'invalid_message' => 'Les champs du mot de passe doivent correspondre.',
                'attr' => ['class' => 'password-field'],
                'required' => true,

                'first_options'  => array(
                    'label' => 'Mot de passe'
                ),

                'second_options' => array(
                    'label' => 'Répéter le mot de passe'
                ),

                'constraints' => [
                    new NotBlank([
                        'message' => 'Entrez un mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit faire {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
