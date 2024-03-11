<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\DeliveryTime;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Entity\WorkSchedule;
use App\Form\OrderType;
use App\Repository\DeliveryTimeRepository;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use SebastianBergmann\CodeCoverage\Report\Xml\Totals;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{

    private $requestStack;
    private $entityManager;

    public function __construct(RequestStack $requestStack, EntityManagerInterface $entityManager)
    {
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
    }

    #[Route('/commande', name: 'app_order')]
    public function index(Request $request, Cart $cart, DeliveryTimeRepository $repo): Response
    {
        $repo -> veriTenMinutesBis();
        $request = $this->requestStack->getCurrentRequest();
        $session = $request->getSession();

        // Récupérer l'ID du créneau horaire depuis la requête
        $deliveryTimeSlotId = $request->query->get('deliveryTimeSlotId');

        // Stocker l'ID du créneau horaire dans la session
        $session->set('selected_delivery_time_slot_id', $deliveryTimeSlotId);


       if (!$this->getUser()->getAddresses()->getValues()) 
       {
            return $this->redirectToRoute('app_account_address_add');
       }
        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);


        // Partie date de livraison

        $allDeliveryTimeSlots = $this->entityManager->getRepository(DeliveryTime::class)->findAll();
        // Récupérer le nom du jour actuel en français le jour +1 
        $dayName = date("l", strtotime('+1 day'));
        $dayName2 = date("l", strtotime('today'));

        $dayNamesInFrench = [
            "Monday" => "Lundi",
            "Tuesday" => "Mardi",
            "Wednesday" => "Mercredi",
            "Thursday" => "Jeudi",
            "Friday" => "Vendredi",
            "Saturday" => "Samedi",
            "Sunday" => "Dimanche"
        ];
        $dayName2French = $dayNamesInFrench[$dayName2];
        $today2 = $this->entityManager->getRepository(WorkSchedule::class)->findByDay($dayName2French)[0];

        $todaySchedules2 = $this->getAvailableTimeSlots2($today2);


        $dayNameFrench = $dayNamesInFrench[$dayName];
        $workDays = $this->entityManager->getRepository(WorkSchedule::class)->findAll();

        // Récupérer les horaires pour aujourd'hui
        $today = $this->entityManager->getRepository(WorkSchedule::class)->findByDay($dayNameFrench)[0];
        $todaySchedules = $this->getAvailableTimeSlots($today);

        // Récupérer tous les créneaux horaires de la table delivery_time
        $allDeliveryTimeSlots = $this->entityManager->getRepository(DeliveryTime::class)->findAll();
        
        return $this->render('order/index.html.twig', [
            'form' => $form->createView(),
            'cart' => $cart->getFull(),
            'workDays' => $workDays,
            'todaySchedules' => $todaySchedules,
            'todaySchedules2' => $todaySchedules2,
            'allDeliveryTimeSlots' => $allDeliveryTimeSlots,        
        ]);
        
    }

    // Fonction pour obtenir les plages horaires disponibles pour une journée donnée
    private function getAvailableTimeSlots2($day)
    {
        $availableTimeSlots = [];
        if ($day->isWork()) {
                   // dd($day->isWork());

            $startTime = $day->getHeureDebut();
            $endTime = $day->getHeureFin();
            $endTimeLimit = clone $endTime;
            $endTimeLimit->sub(new DateInterval('PT30M'));
    
            $currentHour = clone $startTime;
            $interval = new DateInterval('PT30M');
    
            $currentDate = new \DateTime('today');
            $currentDateFormatted = $currentDate->format('Y-m-d');

            $currentDate->setTime(0, 0, 0); // Réinitialise l'heure à 00:00:00
    
            while ($currentHour <= $endTimeLimit) {
                        $startSlot = $currentHour->format('H:i');
                        $currentHour->add($interval);
                        $endSlot = $currentHour->format('H:i');
                        $availableTimeSlots[] = "$startSlot - $endSlot";  
                // Vérifier si le créneau existe déjà en base de données
                $existingSlot = $this->entityManager->getRepository(DeliveryTime::class)->findBy([
                    'date' => $currentDate,
                    'time' => $startSlot,  // Ajoutez cette condition
                    'time_end' => $endSlot // Ajoutez cette condition
                ]);                
                //dd($existingSlot);


                //SI SA MARCHE PAS IF DANS IF

                if ($existingSlot === []) {
                    // Le créneau n'existe pas encore en base de données, on peut l'ajouter               
                        $deliveryTime = new DeliveryTime();
                        $deliveryTime->setTime($startSlot);
                        $deliveryTime->setTimeEnd($endSlot);
                        $deliveryTime->setDate($currentDate);
                
                        $this->entityManager->persist($deliveryTime);
              
                    }else {
                    continue;
                }       
                    //$this->entityManager->flush();
            }
        } else {
            $startTime = $day->getHeureDebut()->format('H:i');
            $endTime = $day->getHeureFin()->format('H:i');
            $currentDate = new \DateTime('today');
            $statuts = false;
            $currentDate->setTime(0, 0, 0); // Réinitialise l'heure à 00:00:00

            $deliveryTime = new DeliveryTime();
            $deliveryTime->setTime($startTime);
            $deliveryTime->setTimeEnd($endTime);
            $deliveryTime->setDate($currentDate);
            $deliveryTime->setStatu($statuts);

            $existingSlot = $this->entityManager->getRepository(DeliveryTime::class)->findBy([
                'date' => $currentDate,
                'time' => $startTime,
                'time_end' => $endTime 
            ]);
            if ($existingSlot == []) {
                $this->entityManager->persist($deliveryTime);
                $this->entityManager->flush();
            }
        }
    
        return $availableTimeSlots;
    }

    private function getAvailableTimeSlots($day)
    {
        $availableTimeSlots = [];

        if ($day->isWork()) {
            $startTime = $day->getHeureDebut();
            $endTime = $day->getHeureFin();
            $endTimeLimit = clone $endTime;
            $endTimeLimit->sub(new DateInterval('PT30M'));
    
            $currentHour = clone $startTime;
            $interval = new DateInterval('PT30M');
    
            $currentDate = new \DateTime('+1 day');
            $currentDateFormatted = $currentDate->format('Y-m-d');

            $currentDate->setTime(0, 0, 0); // Réinitialise l'heure à 00:00:00
    
            while ($currentHour <= $endTimeLimit) {
                        $startSlot = $currentHour->format('H:i');
                        $currentHour->add($interval);
                        $endSlot = $currentHour->format('H:i');
                        $availableTimeSlots[] = "$startSlot - $endSlot";  
                // Vérifier si le créneau existe déjà en base de données
                $existingSlot = $this->entityManager->getRepository(DeliveryTime::class)->findBy([
                    'date' => $currentDate,
                    'time' => $startSlot,  // Ajoutez cette condition
                    'time_end' => $endSlot // Ajoutez cette condition
                ]);

                //SI SA MARCHE PAS IF DANS IF

                if ($existingSlot === []) {
                    // Le créneau n'existe pas encore en base de données, on peut l'ajouter               
                        $deliveryTime = new DeliveryTime();
                        $deliveryTime->setTime($startSlot);
                        $deliveryTime->setTimeEnd($endSlot);
                        $deliveryTime->setDate($currentDate);
                
                        $this->entityManager->persist($deliveryTime);
                        dump($deliveryTime);
                    }else {
                    continue;
                }       
                    $this->entityManager->flush();
            }
        } else {
            $startTime = $day->getHeureDebut()->format('H:i');
            $endTime = $day->getHeureFin()->format('H:i');
            $currentDate = new \DateTime('+1 day');
            $statuts = false;
            $currentDate->setTime(0, 0, 0); // Réinitialise l'heure à 00:00:00

            $deliveryTime = new DeliveryTime();
            $deliveryTime->setTime($startTime);
            $deliveryTime->setTimeEnd($endTime);
            $deliveryTime->setDate($currentDate);
            $deliveryTime->setStatu($statuts);

            $existingSlot = $this->entityManager->getRepository(DeliveryTime::class)->findBy([
                'date' => $currentDate,
                'time' => $startTime,
                'time_end' => $endTime 
            ]);
            if ($existingSlot == []) {
                $this->entityManager->persist($deliveryTime);
                $this->entityManager->flush();
            }
        }
    
        return $availableTimeSlots;
    }
    

    #[Route('/commande/recapitulatif', name: 'app_order_recap', methods:['POST'] )]
    public function add(Cart $cart, Request $request, DeliveryTimeRepository $repo): Response
    {
        $repo -> veriTenMinutesBis();
        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);
        $nb_formule = 0 ;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $date = new DateTime();
            $deliveryTimeSlotId = $form->get('deliveryTimeSlot')->getData();
            $delivery = $form->get('delivery')->getData();
            $addresses = $form->get('addresses')->getData();
            $delivery_content = $addresses->getfirstname().' '.$addresses->getLastname();
            $delivery_content .= '<br/>'.$addresses->getphone();
            $delivery_content .= '<br/>'.$addresses->getaddress();
            $delivery_content .= '<br/>'.$addresses->getPostal();
            $delivery_content .= ' '.$addresses->getCity();
            

            $order = new Order();
            $reference = $date->format('dmY').'-'.uniqid();
            $order->setReference($reference);
            $order->setUser($this->getUser());
            $order->setCreatedAt($date);
            $order->setDeliveryName($delivery->getName());
            $order->setDeliveryPrice($delivery->getPrice());
            $order->setAddressDelivery($delivery_content);
            $order->setIsPaid(0);
            $order->setDelivery($deliveryTimeSlotId);

            $deliverystatu = $this->entityManager->getRepository(DeliveryTime::class)->findOneById($deliveryTimeSlotId->getId());
                   
            $deliverystatu->setStatu(false);

            
            foreach($cart->getFull() as $formule) {
                $nb_formule = $nb_formule + 1 ;
               $uniqid = uniqid();

                     
               foreach ($formule['product']['product'] as $produit) {
           
                $orderDetails = new OrderDetails();
                $orderDetails->setMyorder($order);
                $orderDetails->setFormule($formule['formule']['formule']->getTitle());
                $orderDetails->setQuantity($formule['formule']['quantity']);
                $orderDetails->setFormuleId($uniqid);
                // for ($i=0; $i <$produit ; $i++) { 
                //     dump($formule['formule']['quantity']);

                // }
                
                $orderDetails->setPrice($formule['formule']['formule']->getPrice());
                $total = $formule['formule']['formule']->getPrice() * $formule['formule']['quantity'] ;
                $orderDetails->setTotal($total);
                $orderDetails->setProduct($produit->getName()); 
              
                $this->entityManager->persist($orderDetails);
                    

               }             
               $session = $request->getSession();
               $carts = $session->get('cart', []);
               $totalOrder = 0 ;

               foreach ($carts as $test) {
                $totalOrder = $totalOrder + $test['formule']['formule']->getPrice() * $test['formule']['quantity'];
               // dump($totalOrder);
               }
              // dd($totalOrder);
               $order->setTotal($totalOrder);
               $nb_formule = $session->get('nb-cart');
               $order->setNumberFormule($nb_formule);

               $this->entityManager->persist($order);

                $this->entityManager->flush();
            }
            //dd('test');

            return $this->render('order/add.html.twig', [
            'cart' => $cart->getFull(),
            'delivery' => $delivery,
            'addresses' => $delivery_content,
            'horaire' => $deliveryTimeSlotId,
            'reference' => $order->getReference()
        ]);

         }
         return $this->redirectToRoute('app_cart');
        
    }
}
