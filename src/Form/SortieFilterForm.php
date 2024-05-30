<?php
namespace App\Form;

use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieFilterForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionnez un site',
                'required' => false,
                'label' => 'Site :'
            ])
            ->add('name', SearchType::class, [
                'required' => false,
                'label' => 'Le nom de la sortie contient :'
            ])
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Entre'
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'et'
            ])
            ->add('checkbox1', CheckboxType::class, [
                'required' => false,
                'label' => "Sorties dont je suis l'organisateur/trice"
            ])
            ->add('checkbox2', CheckboxType::class, [
                'required' => false,
                'label' => "Sorties auxquelles je suis inscrit/e"
            ])
            ->add('checkbox3', CheckboxType::class, [
                'required' => false,
                'label' => "Sorties auxquelles je ne suis pas inscrit/e"
            ])
            ->add('checkbox4', CheckboxType::class, [
                'required' => false,
                'label' => 'Sorties passées'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
