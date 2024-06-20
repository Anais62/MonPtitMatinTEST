<?php

namespace App\Command;

use App\Entity\DeliveryTime;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


#[AsCommand(
    name: 'app:supprimer-creneaux',
    description: 'Supprime les créneaux horaires dépassés.',
)]

class SupprimerCreneauxCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this->setDescription('Supprime les créneaux horaires dépassés.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //$this->supprimerCreneauxHorairesCommande();
        $this->supprimerCreneauxHoraires();

        return Command::SUCCESS;
    }

    private function supprimerCreneauxHoraires(): void
    {
        // Supprimer les créneaux horaires expirés
        $expiredSlots = $this->entityManager->getRepository(DeliveryTime::class)
            ->createQueryBuilder('d')
            ->where('d.statu = :statu')
            ->andWhere('d.date < :aujourd_hui')
            ->setParameter('statu', 1)
            ->setParameter('aujourd_hui', new \DateTime('today'))
            ->getQuery()
            ->getResult();

        foreach ($expiredSlots as $slot) {
            // Récupérer et supprimer les commandes associées
            $relatedOrders = $this->entityManager->getRepository(Order::class)->findBy(['delivery' => $slot]); 
        
            foreach ($relatedOrders as $order) {
                // Vérifier si la commande n'a pas été payée
                if ($order->isIsPaid() == false) {
                // Supprimer la commande non payée
                // Récupérer et supprimer les détails de commande associés
                $orderDetails = $order->getOrderDetails();
                foreach ($orderDetails as $orderDetail) {
                    $this->entityManager->remove($orderDetail);
                }

                $this->entityManager->remove($order);
                }
            }
            // Supprimer les créneaux horaires expirés
            $this->entityManager->remove($slot);
        }
        $this->entityManager->flush();
    }

}
