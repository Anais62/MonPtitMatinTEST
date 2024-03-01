<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * Trouve les commandes impayées créées il y a plus d'un certain nombre de minutes.
     *
     * @param int $minutes Nombre de minutes seuil
     *
     * @return Order[] Retourne un tableau d'objets Order qui correspondent aux critères
     */
    public function findUnpaidOrdersOlderThanMinutes($minutes)
    {
        // Calcul du seuil temporel basé sur l'heure actuelle et le nombre de minutes spécifié
        $timeThreshold = new \DateTime();
        $timeThreshold->sub(new \DateInterval('PT' . $minutes . 'M'));

        // Crée un constructeur de requête pour l'entité Order
        return $this->createQueryBuilder('o')
            // Ajoute des conditions pour filtrer les commandes impayées et les commandes créées avant le seuil
            ->andWhere('o.isPaid = false')
            ->andWhere('o.createdAt < :timeThreshold')
            // Définit le paramètre pour le seuil temporel dans la requête
            ->setParameter('timeThreshold', $timeThreshold)
            // Obtient la requête finale
            ->getQuery()
            // Exécute la requête et retourne le résultat sous forme d'un tableau d'objets Order
            ->getResult();
    }

    // Ajoutez d'autres méthodes personnalisées si nécessaire
}
