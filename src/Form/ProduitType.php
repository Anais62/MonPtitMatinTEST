<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Products;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', EntityType::class, [
                'class' => Products::class, // Spécifiez l'entité associée au champ
                'choice_label' => 'name', // Champ de l'entité à afficher dans le champ de formulaire
                'label' => 'Nom du produit',
                'placeholder' => 'Sélectionnez un produit', // Texte de l'option par défaut
                'required' => false, // Indiquez si le champ est requis ou non
                'multiple' => false, // Indiquez si l'utilisateur peut sélectionner plusieurs options
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Products::class,
        ]);
    }
}
