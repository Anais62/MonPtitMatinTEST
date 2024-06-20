<?php

namespace App\Controller;

use App\Entity\Formule;
use App\Entity\Order;
use App\Entity\OrderDetails;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Snappy\Pdf;

class AccountOrderController extends AbstractController
{

    private $entityManager;
    private $pdf;

    public function __construct(EntityManagerInterface $entityManager, Pdf $pdf)
    {
        $this->entityManager = $entityManager;
        $this->pdf = $pdf;

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
    // #[Route('/compte/mes-commandes/{order_id}', name: 'app_account_order_details')]
    // public function commande(): Response
    // {
    //     $orders = $this->entityManager->getRepository(Order::class)->findSuccessOrders($this->getUser());
    //     //dd($orders);
    //     $ordersDetails= [] ;
    //     foreach ($orders as $order) {
    //         dump($order->getId());
    //         //$test = $this->entityManager->getRepository(OrderDetails::class)->findByUser($order->getId() );
    //         // JE cherche mes orderDetails par rapport a l'id de mon order
    //         $ordersDetails[] = $this->entityManager->getRepository(OrderDetails::class)->findByUser($order->getId() );
            
    //     }
        
    //     dd($ordersDetails);
    //     return $this->render('account/order.html.twig', [
    //         'orders' => $orders ,
    //         'ordersDetails' => $ordersDetails
    //         //dd($orders);
    //     ]);
    // }

    #[Route('/compte/mes-commandes/{reference}', name: 'app_account_order_show')]
    public function show($reference): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneByReference($reference);

        if (!$order || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('app_account_order');
        }

       
        return $this->render('account/order_show.html.twig', [
            'order' => $order
        ]);
    }
    #[Route('/compte/mes-commandes/genpdf/{reference}', name: 'app_account_order_pdf')]
    public function generatePdf($reference): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneByReference($reference);
    
        if (!$order || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('app_account_order');
        }
    
        
        // Construire les données nécessaires pour le template HTML du PDF
        $data = [
            'order' => $order,
            'user' => $order->getUser(),
            'adress' => $order->getAddressDelivery(),
            'items' => $order->getOrderDetails(),


            // Autres données nécessaires...
        ];
        //dd($order);
        // Rendre la vue du template HTML du PDF avec les données
        $html = $this->renderView('pdf/facture.html.twig', [
            'data' => $data,
            'order' => $order
        ]);
    
        // Générer le PDF
        $pdf = $this->pdf->getOutputFromHtml($html);
    
        // Retourner une réponse PDF
        return new Response(
            $pdf,
            200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="facture.pdf"'
            )
        );
    }
    

}