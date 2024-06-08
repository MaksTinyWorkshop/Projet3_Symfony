<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\GroupePrive;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire de creation et de mofification d'une sortie
 * Contient des options en cas de modifiaction
 * @param bool $is_edit
 * et deux boutons : 'Enregistrer' et 'Publier' qui conditionnent l'Etat de la sortie au submit
 */
class CreaSortieFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('dateHeureDebut', null, [
                'widget' => 'single_text',
            ])
            ->add('duree')
            ->add('dateLimiteInscription', null, [
                'widget' => 'single_text',
            ])
            ->add('infosSortie', TextareaType::class, [])
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'nom',
            ])
            ->add('enregistrer', SubmitType::class,[
                'label' => 'Enregistrer',
                'attr' => ['class' => 'btn btn-warning'],
            ])
            ->add('publier', SubmitType::class,[
                'label' => 'Publier',
                'attr' => ['class' => 'btn btn-success'],
            ]);

        if ($options['is_edit']) {
            $builder->add('site', EntityType::class, [
                'label' => 'Campus Organisateur',
                'class' => Site::class,
                'choice_label' => 'nom',
            ]);
        }

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
            'is_edit' => false,
        ]);
    }
}