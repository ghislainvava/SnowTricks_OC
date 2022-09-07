<?php

namespace App\Form;

use App\Entity\Figure;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class FigureFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => [
                    'class' => 'form-control mt-3 mb-3',
                ],
                'label' => 'Indiquez le nom de la figure',
                'required' => true
            ])
            ->add('pictures', FileType::class, [
                'attr' => [
                    'class' => 'form-control mt-3 mb-3',
                ],
                'label' => 'Choisissez vos images',
                'multiple' => true,
                'mapped' => false,
                'required' => true
            ])
            ->add('content', TextType::class, [
                'attr' => [
                    'class' => 'form-control mt-3 mb-3',
                ],
                'label' => 'Decrivez la figure',
                'required' => true
            ])
            ->add('groupe', EntityType::class, [
                 'attr' => [
                    'class' => 'form-control mt-3 mb-3'
                ],
                'class' => Category::class,
                'choice_label' => function (?Category $category) {
                    return $category ? strtoupper($category->getName()) : '';
                },
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Figure::class,
        ]);
    }
}
