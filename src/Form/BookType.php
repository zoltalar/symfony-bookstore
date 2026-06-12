<?php

namespace App\Form;

use App\Entity\Book;
use App\Form\AuthorNameType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('isbn', TextType::class, [
                'label' => 'ISBN'
            ])
            ->add('title')
            ->add('description', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'rows' => 8
                ]
            ])
            ->add('price', MoneyType::class, [
                'currency' => 'USD',
                'scale' => 2,
                'attr' => [
                    'placeholder' => '0.00',
                    'step' => '0.01',
                ],
            ])
            ->add('publicationDate', null, [
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
        ]);
    }
}
