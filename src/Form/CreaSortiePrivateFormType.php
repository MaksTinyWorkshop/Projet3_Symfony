<?php

namespace App\Form;

use App\Entity\Etat;
use App\Entity\GroupePrive;
use App\Entity\Lieu;
use App\Entity\Participants;
use App\Entity\Site;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreaSortiePrivateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('dateHeureDebut', null, [
                'widget' => 'single_text',
            ])
            ->add('duree')
            ->add('infosSortie')
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'nom',
            ])
            ->add('nbInscriptionsMax', HiddenType::class, [
                'data' => $options['nbParticipants'],
            ])
            ->add('dateLimiteInscription', HiddenType::class);

        $builder->get('dateLimiteInscription')
            ->addModelTransformer(new DateTimeToStringTransformer(null, null, 'Y-m-d H:i'));

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if(!isset($data['nbInscriptionsMax'])) {
                $data['nbInscriptionsMax'] = $form->getConfig()->getOption('nbParticipants');
            }
            if(!isset($data['dateLimiteInscription'])) {
                $data['dateLimiteInscription'] = (new \DateTime())->format('Y-m-d H:i');
            }

            $event->setData($data);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
            'nbParticipants' => null,
        ]);
    }
}
