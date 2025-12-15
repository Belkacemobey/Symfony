<?php

namespace App\Form;

use App\Entity\Meubles;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class MeublesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomM', TextType::class, [
                'label' => 'Nom du meuble'
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix'
            ])
            ->add('since_At', DateType::class, [
                'label' => 'Date d\'ajout',
                'widget' => 'single_text'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description'
            ])
            ->add('image', FileType::class, [
    'label' => 'Image du meuble (optionnel)',
    'mapped' => false,
    'required' => false,
])

            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'nomCategory',
                'label' => 'Catégorie'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Meubles::class,
        ]);
    }
}