<?php

namespace App\Form;

use App\Entity\Author;
use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookEditType extends AbstractType
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $authors = $this
            ->entityManager
            ->getRepository(Author::class)
            ->findAll();
        
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
            ->add('uploadImages', FileType::class, [
                'label' => 'Upload Images',
                'data_class' => null,
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'attr' => [
                    'accept' => 'image/*',
                    'multiple' => 'multiple'
                ]
            ]);
        
        if (count($authors) > 0) {
            $builder->add('authors', ChoiceType::class, [
                'label' => 'Select Existing Authors',
                'choices' => $authors,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'mapped' => false,
                'data' => $options['selected_authors'],
                'attr' => ['size' => 8],
            ]);
        }
            
        $builder
            ->add('newAuthorName', TextType::class, [
                'label' => 'New Author Name',
                'mapped' => false,
                'required' => count($authors) === 0
            ])
            ->add('addNewAuthor', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-sm btn-outline-secondary'
                ]
            ])
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
        ]);
        
        $resolver->setDefault('selected_authors', []);
    }
}
