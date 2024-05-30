<?php

namespace App\Form;

use App\Entity\Participants;
use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Formulaire de création de compte
 */
class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Les contraintes de validations sont déplacées ici
        $passwordOptions = [
            'mapped' => false,
            'attr' => ['autocomplete' => 'new-password', 'class' => 'form-control'],
            'label' => 'Mot de Passe',
            'constraints' => [
                new Assert\NotBlank([
                    'message' => 'Veuillez entre un mot de passe',
                ]),
                new Assert\Length([
                    'min' => 8,
                    'minMessage' => 'Mot de passe d\'au moins {{ limit }} caractères',
                    'max' => 20,
                    'maxMessage' => 'Mot de passe d\'au plus {{ limit }} caractères',
                ]),
                new Assert\Regex([
                    'pattern' => '/(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&#])/',
                    'message' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule et un caractère spécial',
                ])
            ],
        ];
        // Si on est déjà inscrit, ajout d'un placeholder dans le champ MDP
        if($options['is_edit']) {
            $passwordOptions['attr'] = [
                'placeholder' => 'Mot de passe courant ou nouveau mot de passe',
            ];
        }


        $builder
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Email',
            ])
            ->add('nom', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Nom',
            ])
            ->add('prenom', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Prénom',
            ])
            ->add('telephone', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Téléphone',
            ])
            ->add('pseudo', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Pseudo',
            ])
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisissez un site',
            ])
            ->add('plainPassword', PasswordType::class, $passwordOptions)
            ->add('photo', FileType::class, [
                'label' => 'Photo de profil (JPEG, PNG, GIF)',

                // unmapped pour qu'il ne soit pas associé à une  entity property
                'mapped' => false,

                // pour le rendre optionnel
                'required' => false,

                // Contraintes de validations de fichier
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Veuillez sélectionner une image valide',
                    ])
                ],
            ]);
        // Si on est déjà inscrit, ce champ est coché automatiquement
        if (!$options['is_edit']) {
            $builder->add('RGPD', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new Assert\IsTrue([
                        'message' => 'Vous devez accepter les conditions pour pouvoir continuer',
                    ]),
                ],
            ]);
        }

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participants::class,
            'is_edit' => false, // Défaut à false pour l'inscription
        ]);
    }
}
