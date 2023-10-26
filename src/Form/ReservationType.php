<?php

namespace App\Form;

use App\Entity\Chambre;
use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('chambre', EntityType::class, [
                'class' => Chambre::class
            ])
            ->add('intitule', TextType::class, [
                'label' => 'Intitulé',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Saisir un intitulé pour votre réservation'
                ]
            ])
            ->add('dateStart', DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'attr' => ['min' => date('Y-m-d')]
            ])
            ->add('dateEnd', DateType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'attr' => ['min' => date('Y-m-d')]
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'placeholder' => 'Saisir une description de la réservation',
                    'rows' => 10
                ]
            ])
            ->add('pro', ChoiceType::class, [
                'label' => 'Type de réservation',
                'expanded' => true,
                'multiple' => false,
                'choices' => [
                    'Professionnel' => 1,
                    'Personnel' => 0
                ],
            ])
            ->add('nbPersonnes', IntegerType::class, [        
                'label' => 'Nombre de personnes',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Saisir un nombre de personnes'
                ]
            ])
            ->add('codeReservation', PasswordType::class, [
                'label' => 'Code de réservation',
                'attr' => [
                    'placeholder' => 'Saisir un code de réservation',
                    'value' => 'Aaa12345@'
                ]
            ])
            ->add('confirmCodeReservation', PasswordType::class, [          
                'label' => 'Confirmation du code de réservation',
                'attr' => [
                    'placeholder' => 'Confirmer votre code de réservation',
                    'value' => 'Aaa12345@'
                ]
            ])
            ->add('ajouter', SubmitType::class, [
                'attr' => [
                    'class' => 'btn-success',
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
