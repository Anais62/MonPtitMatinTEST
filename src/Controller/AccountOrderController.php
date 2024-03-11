<?php

namespace App\Controller;

use App\Entity\Formule;
use App\Entity\Order;
use App\Entity\OrderDetails;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AccountOrderController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/compte/mes-commandes', name: 'app_account_order')]
    public function index(): Response
    {
        $orders = $this->entityManager->getRepository(Order::class)->findSuccessOrders($this->getUser());

        
            
        return $this->render('account/order.html.twig', [
            'orders' => $orders
            //dd($orders);
        ]);
    }
    #[Route('/compte/mes-commandes/{order_id}', name: 'app_account_order_details')]
    public function commande(): Response
    {
        $orders = $this->entityManager->getRepository(Order::class)->findSuccessOrders($this->getUser());
        //dd($orders);
        $ordersDetails= [] ;
        foreach ($orders as $order) {
            dump($order->getId());
            //$test = $this->entityManager->getRepository(OrderDetails::class)->findByUser($order->getId() );
            // JE cherche mes orderDetails par rapport a l'id de mon order
            $ordersDetails[] = $this->entityManager->getRepository(OrderDetails::class)->findByUser($order->getId() );
            
        }
        
        dd($ordersDetails);
        return $this->render('account/order.html.twig', [
            'orders' => $orders
            //dd($orders);
        ]);
    }

}