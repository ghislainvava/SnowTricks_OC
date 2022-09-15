<?php

namespace App\Form;

use App\Entity\Video;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VideoFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('frame', TextType::class, [
                'label' => " saissisez le lien 'iframe' d'une vidéo Youtube : ",
                'required' => false,
                'attr' => [
                    'class' => 'form-control mt-3 mb-3',
                    'placeholder' => 'lien Youtube'

                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Video::class,
        ]);
    }
}
