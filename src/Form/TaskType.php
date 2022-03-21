<?php

namespace App\Form;

use App\Entity\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Libellé'
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'RDV' => 'RDV',
                    'Prestation' => 'Prestation',
                    'DeadLine' => 'DeadLine'
                ]
            ])
            ->add('description', TextType::class, [
                'label' => 'description'
            ])
            ->add('image', FileType::class, [
                'label' => 'illustration',
                'constraints' => [
                    new NotBlank([
                        'message' => 'renseigner le champ'
                    ]),
                    new File([
                        'maxSize' => '3M',
                        'mimeTypesMessage' => 'Format invalide'

                    ])
                ]
            ])
            ->add('taskLimit', DateType::class, [
                'label' => 'A faire pour le'
            ])

            ->add('envoyer', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
