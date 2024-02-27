<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Products;
use App\Entity\Formule;
use App\Entity\Delivery;
use App\Entity\DeliveryTime; // Ajout de l'import pour l'entité DeliveryTime
use App\Entity\WorkSchedule;
use DateInterval;
use DateTimeInterface;
use App\Repository\DeliveryTimeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class CartController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/mon-panier', name: 'app_cart')]
    public function index(Cart $cart, Request $request): Response
    {
        $delivery = $this->entityManager->getRepository(Delivery::class)->findAll();
        $workDays = $this->entityManager->getRepository(WorkSchedule::class)->findNextWorkDays();
        $allDeliveryTimeSlots = $this->entityManager->getRepository(DeliveryTime::class)->findAll();
        // Récupérer le nom du jour actuel en français le jour +1 
        $dayName = date("l", strtotime('+1 day'));
        $dayNamesInFrench = [
            "Monday" => "Lundi",
            "Tuesday" => "Mardi",
            "Wednesday" => "Mercredi",
            "Thursday" => "Jeudi",
            "Friday" => "Vendredi",
            "Saturday" => "Samedi",
            "Sunday" => "Dimanche"
        ];
        $dayNameFrench = $dayNamesInFrench[$dayName];

        // Récupérer les horaires pour aujourd'hui
        $today = $this->entityManager->getRepository(WorkSchedule::class)->findByDay($dayNameFrench)[0];
        $todaySchedules = $this->getAvailableTimeSlots($today);

        // Récupérer les horaires pour demain
       

        // Récupérer tous les créneaux horaires de la table delivery_time
        $allDeliveryTimeSlots = $this->entityManager->getRepository(DeliveryTime::class)->findAll();


        return $this->render('cart/index.html.twig', [
            'cart' => $cart->get(),
            'delivery' => $delivery,
            'workDays' => $workDays,
            'todaySchedules' => $todaySchedules,
            'allDeliveryTimeSlots' => $allDeliveryTimeSlots, // Ajout de cette variable
        ]);
    }

    // Fonction pour obtenir les plages horaires disponibles pour une journée donnée
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
                        $startSlot = $currentHour->format('H:i:s');
                        $currentHour->add($interval);
                        $endSlot = $currentHour->format('H:i:s');
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
                        dump($deliveryTime);
                    }else {
                    continue;
                }
                    
                    $this->entityManager->flush();
                }
                
          
                                //dd('test');

           
                   
            // dd($deliveryTime);

        } else {
            $availableTimeSlots = ["Aucun créneau horaire disponible"];
        }
    
        return $availableTimeSlots;
    }
    #[Route('/cart/add/{id}/{productIdsArray}', name: 'app_add_to_cart')]
    public function add(Cart $cart, $id, $productIdsArray) : Response
    {
        $productIds = explode(',', $productIdsArray);

        $cart->add($id, $productIds);

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/remove', name: 'app_remove_my_cart')]
    public function remove(Cart $cart) : Response
    {
        $cart->remove();

        return $this->redirectToRoute('app_product');
    }

    #[Route('/cart/delete/{orderId}', name: 'app_delete_to_cart')]
    public function delete(Cart $cart, $orderId) : Response
    {
        $cart->delete($orderId);

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/add_quantity/{orderId}', name: 'app_add_quantity_to_cart')]
    public function add_quantity(Cart $cart, $orderId) : Response
    {
        $cart->add_quantity($orderId);

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/decrease_quantity/{orderId}', name: 'app_decrease_quantity_to_cart')]
    public function decrease_quantity(Cart $cart, $orderId) : Response
    {
        $cart->decrease_quantity($orderId);

        return $this->redirectToRoute('app_cart');
    }

    
}