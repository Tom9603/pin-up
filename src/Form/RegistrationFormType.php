<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
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
            ->add('name', null, [
                'label' => 'Pseudo',
                'attr' => [
                    'placeholder' => 'Entrez votre pseudo',
                ]
            ])
            ->add('email', null, [
                'label' => 'Adresse e-mail',
                'attr' => [
                    'placeholder' => 'Entrez votre mail',
                ]
                ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => "J'accepte les termes et conditions",
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les termes et conditions.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'label' => 'Mot de passe',
                'mapped' => false,
                'attr' =>
                ['autocomplete' => 'Nouveau mot de passe',
                'placeholder' => 'Entrez votre mot de passe'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Entez un mot de passe',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Il manque {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                    new \Symfony\Component\Validator\Constraints\Regex([
                        'pattern' => '/[A-Z]/',
                        'message' => 'Il manque une majuscule.',
                    ]),
                    new \Symfony\Component\Validator\Constraints\Regex([
                        'pattern' => '/[a-z]/',
                        'message' => 'Il manque une minuscule.',
                    ]),
                    new \Symfony\Component\Validator\Constraints\Regex([
                        'pattern' => '/\d/',
                        'message' => 'Il manque un chiffre.',
                    ]),
                    new \Symfony\Component\Validator\Constraints\Regex([
                        'pattern' => '/[\W_]/',
                        'message' => 'Il manque un caractère spécial.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
