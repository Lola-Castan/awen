<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class CreatorProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du produit',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un nom pour votre produit']),
                    new Length([
                        'min' => 3,
                        'max' => 100,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('shortDescription', TextareaType::class, [
                'label' => 'Description courte',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer une description courte']),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'La description courte ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('longDescription', TextareaType::class, [
                'label' => 'Description détaillée',
                'attr' => ['rows' => 6],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer une description détaillée'])
                ]
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Stock disponible',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez indiquer le stock disponible']),
                    new GreaterThanOrEqual([
                        'value' => 0,
                        'message' => 'Le stock ne peut pas être négatif'
                    ])
                ]
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix (en €)',
                'divisor' => 100, // Pour convertir les centimes en euros
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez indiquer un prix']),
                    new GreaterThanOrEqual([
                        'value' => 0,
                        'message' => 'Le prix ne peut pas être négatif'
                    ])
                ]
            ])
            ->add('weight', IntegerType::class, [
                'label' => 'Poids (en grammes)',
                'required' => false,
            ])
            ->add('width', IntegerType::class, [
                'label' => 'Largeur (en mm)',
                'required' => false,
            ])
            ->add('depth', IntegerType::class, [
                'label' => 'Profondeur (en mm)',
                'required' => false,
            ])
            ->add('height', IntegerType::class, [
                'label' => 'Hauteur (en mm)',
                'required' => false,
            ])
            ->add('showcaseProduct', CheckboxType::class, [
                'label' => 'Mettre en avant ce produit',
                'required' => false,
            ])
            ->add('categories', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'label' => 'Catégories',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner au moins une catégorie'])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
