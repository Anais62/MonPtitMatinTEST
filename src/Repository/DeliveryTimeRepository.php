<?php

namespace App\Repository;

use App\Entity\DeliveryTime;
use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DeliveryTime>
 *
 * @method DeliveryTime|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeliveryTime|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeliveryTime[]    findAll()
 * @method DeliveryTime[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeliveryTimeRepository extends ServiceEntityRepository
{

    private $entityManager;
    private $orderRepository;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager, OrderRepository $orderRepository)
    {
        parent::__construct($registry, DeliveryTime::class);
        $this->entityManager = $entityManager;
        $this->orderRepository = $orderRepository;
    }


    public function findByAvailableDeliveryTimes()
    {
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('+1 day');

        return $this->createQueryBuilder('deliveryTime')
            ->andWhere('deliveryTime.date >= :today')
            ->andWhere('deliveryTime.date < :tomorrow')
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->getQuery()
            ->getResult();
    }


public function veriTenMinutesBis()
{
    $currentTime = new \DateTime();
    $currentTime->sub(new \DateInterval('PT10M')); // Soustraire 10 minutes de l'heure actuelle

    $currentDate = $currentTime->format('Y-m-d');

    $orders = $this->orderRepository
        ->createQueryBuilder('o')
        ->select('o.id', 'o.createdAt', 'o.isPaid', 'dt.id as deliveryId', 'dt.statu as deliveryStatus') 
        ->leftJoin('o.delivery', 'dt') 
        ->andWhere('o.createdAt >= :startDate')
        ->andWhere('o.createdAt < :endDate')
        ->setParameter('startDate', $currentDate . ' 00:00:00')
        ->setParameter('endDate', $currentDate . ' 23:59:59')
        ->getQuery()
        ->getResult();

    foreach ($orders as $order) {
        $orderTime = $order['createdAt'];

        if ($orderTime < $currentTime && $order['isPaid'] == 0) {
           // var_dump($order['id']);
            //var_dump($order['createdAt']);
            //var_dump($order['deliveryId']);
            //var_dump($order['deliveryStatus']);

            // Mise à jour du statut de livraison dans la base de données
            $deliveryTime = $this->entityManager->getRepository(DeliveryTime::class)->find($order['deliveryId']);

            if ($deliveryTime) {
               //dd($deliveryTime);
                $deliveryTime->setStatu(1);
                $this->entityManager->flush();
            }
        }
    }
}
}
