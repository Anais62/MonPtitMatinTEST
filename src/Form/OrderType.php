<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\Delivery;
use App\Entity\DeliveryTime;
use App\Repository\DeliveryTimeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    private $deliveryTimeRepository;

    public function __construct(DeliveryTimeRepository $deliveryTimeRepository)
    {
        $this->deliveryTimeRepository = $deliveryTimeRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];

        $builder

        ->add('deliveryTimeSlot', EntityType::class, [
            'label' => 'Créneaux horaires disponibles',
            'required' => true,
            'class' => DeliveryTime::class,
            'choices' => $this->deliveryTimeRepository->findByAvailableDeliveryTimes(),
            'choice_label' => function ($deliveryTime) {
                return $deliveryTime instanceof DeliveryTime ?
                    ($deliveryTime->isStatu() ? $deliveryTime->__toString() : $deliveryTime->__toString().' '.'Indisponible')  :
                    'Indisponible';
            },
            'choice_attr' => function($choice, $key, $value) {
                if ($choice instanceof DeliveryTime && !$choice->isStatu()) {
                    return ['disabled' => 'disabled' , 
                    'title' =>  "Indisponible" ];
                }
                return [];
            },
            'placeholder' => 'Sélectionnez un créneau horaire',
        ])
        
        
        ->add('addresses', EntityType::class, [
            'label' => 'Choisissez votre adresse de livraison',
            'required' => true,
            'class' => Address::class,
            'choices' => $user->getAddresses(),
            'multiple' => false,
            'expanded' => true
        ])

        ->add('delivery', EntityType::class, [
            'label' => 'Livraison',
            'required' => true,
            'class' => Delivery::class,
            
        ])
        
        ->add('submit', SubmitType::class, [
            'label' => 'Valider ma commande',
            'attr' => [

                'class' => 'btn-success w-100'
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'user' => array()
        ]);
    }
}
