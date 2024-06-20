<?php

namespace App\Form;

use App\Entity\Formule;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FavoriteType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Ajout de l'événement pour gérer la dynamique des champs de produit
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $form = $event->getForm();
                $formule = $event->getData();

                // Vérification si les données représentent une instance de Formule
                if ($formule instanceof Formule) {
                    // Récupération du nombre de produits pour cette formule
                    $nombreProduits = $formule->getNbProduct();

                    // Boucle pour ajouter les champs de produit en fonction du nombre requis
                    for ($i = 1; $i <= $nombreProduits; $i++) {
                        // Ajout dynamique du champ de type ProduitType
                        $form->add('produit_' . $i, ProduitType::class, [
                            'label' => 'Produit ' . $i, // Étiquette du champ
                        ]);
                    }
                }
            })
            
            ->add('title', ChoiceType::class, [
                'label' => 'Choix du nom de la formule',
                'choices' => $this->getFormulesChoices(),
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'Mettre en favoris',
                'attr' => [
                    'class' => 'btn btn-dark'
                ],
                'row_attr' => [
                    'class' => 'text-center',
                ],
            ]);    
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formule::class,
        ]);
    }

    private function getFormulesChoices()
    {
        // Récupérer tous les noms de formules depuis la base de données
        $formules = $this->entityManager->getRepository(Formule::class)->findAll();

        $choices = [];
        // Construire un tableau avec les noms de formules pour le champ "choice"
        foreach ($formules as $formule) {
            $choices[$formule->getTitle()] = $formule->getTitle();
        }
        return $choices;
    }
}
