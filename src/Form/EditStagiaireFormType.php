<?php

namespace App\Form;

use App\Entity\Stagiaire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class EditStagiaireFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Prénom *',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir le prénom.'
                    ]),
                ],
            ])
            ->add('lastname', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Nom *',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir le nom.'
                    ]),
                ],
            ])
            ->add('status', ChoiceType::class, [
                'label' => false,
                'choices'  => [
                    'Non défini' => 'Non défini',
                    'Présent' => 'Présent',
                    'Absent' => 'Absent',
                    'En démarche' => 'En démarche',
                    'En stage' => 'En stage',
                    'FOAD' => 'FOAD',
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Modifier',
                'attr' => [
                    'class' => 'link0 mx-auto btn-save d-flex justify-content-center',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Stagiaire::class,
            'attr' => [
                'novalidate' => 'novalidate',
            ],
        ]);
    }
}
