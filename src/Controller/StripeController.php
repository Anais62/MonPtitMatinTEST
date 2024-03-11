<?php

namespace App\Controller;

use App\Entity\Formule;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StripeController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/commande/create-session/{reference}', name: 'app_stripe_create_session')]
    public function index($reference): Response
    {
        $formuleStripe = [];
        $YOUR_DOMAIN = 'http://178.32.107.35:8001';

        $order = $this->entityManager->getRepository(Order::class)->findOneBy(['reference' => $reference]);

        if(!$order){
            return $this->redirectToRoute('app_cart');
        }
        // Initialisation de la variable pour la premiere itération dans le if
        $previousFormuleId = null;

        foreach ($order->getOrderDetails()->getValues() as $formule) {
            // dd($order->getOrderDetails());
            $formuleData = $this->entityManager->getRepository(Formule::class)->findOneBy(['title' => $formule->getFormule()]);
            $formuleId = $formule->getFormuleId();
            
            //dump($formuleData);
           // On compare la previous valeur avec la valeur actuel de formule ID
            if ($previousFormuleId != $formuleId || $previousFormuleId == null) {

                $formuleStripe[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'unit_amount' => $formuleData->getPrice(),
                        'product_data' => [
                            'name' => $formule->getFormule(),
                            'images' => [$YOUR_DOMAIN."/uploads/".$formuleData->getIllustration()],
                           // dd($YOUR_DOMAIN)
                        ]
                    ],
                    'quantity' => $formule->getQuantity()
                
                ];
               // Si previousFormuleId = $formuleId;
               $previousFormuleId = $formuleId;
            }
        }
            //dd($order);

            $formuleStripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $order->getDeliveryPrice(),
                    'product_data' => [
                        'name' => $order->getDeliveryName()
                    ]
                ],
                'quantity' => 1
            ];

       
        //dd($formuleStripe);

        Stripe::setApiKey('sk_test_51Ok1sWL5LqnIeHACWKl9ip5e9qeT0Sngo1QRcWVEIMHV2F9pZquW6E8TjAPwi7Ogmni8DyELQIquurz8WQE3452900FQi9bG0a');
        
        $checkout_session = Session::create([
            'payment_method_types' => ['card','paypal'],
            'line_items' => [[
                $formuleStripe
            ]],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/commande/merci/{CHECKOUT_SESSION_ID}',
            'cancel_url' => $YOUR_DOMAIN . '/commande/erreur/{CHECKOUT_SESSION_ID}',
        ]);

        $order->setStripeSessionId($checkout_session->id);
        $this->entityManager->flush();
        
        return new RedirectResponse($checkout_session->url);
    }

}
