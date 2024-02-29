<?php

namespace App\Entity;

use App\Repository\DeliveryTimeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeliveryTimeRepository::class)]
class DeliveryTime
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    private ?string $time = null;

    #[ORM\Column]
    private ?bool $statu = true;

    #[ORM\Column(length: 255)]
    private ?string $time_end = null;

    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'delivery')]
    private Collection $orders;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getTime(): ?string
    {
        return $this->time;
    }

    public function setTime(string $time): static
    {
        $this->time = $time;

        return $this;
    }

    public function isStatu(): ?bool
    {
        return $this->statu;
    }

    public function setStatu(bool $statu): static
    {
        $this->statu = $statu;

        return $this;
    }

    public function getTimeEnd(): ?string
    {
        return $this->time_end;
    }

    public function setTimeEnd(string $time_end): static
    {
        $this->time_end = $time_end;

        return $this;
    }

    public function __toString()
    {
        $jour = $this->getDate();
        $nomJour = '';
        $nomMois = '';

        switch ($jour->format('l')) {
            case 'Monday':
                $nomJour = 'Lundi';
                break;
            case 'Tuesday':
                $nomJour = 'Mardi';
                break;
            case 'Wednesday':
                $nomJour = 'Mercredi';
                break;
            case 'Thursday':
                $nomJour = 'Jeudi';
                break;
            case 'Friday':
                $nomJour = 'Vendredi';
                break;
            case 'Saturday':
                $nomJour = 'Samedi';
                break;
            case 'Sunday':
                $nomJour = 'Dimanche';
                break;
        }

        switch ($jour->format('F')) {
            case 'January':
                $nomMois = 'Janvier';
                break;
            case 'February':
                $nomMois = 'Février';
                break;
            case 'March':
                $nomMois = 'Mars';
                break;
            case 'April':
                $nomMois = 'Avril';
                break;
            case 'May':
                $nomMois = 'Mai';
                break;
            case 'June':
                $nomMois = 'Juin';
                break;
            case 'July':
                $nomMois = 'Juillet';
                break;
            case 'August':
                $nomMois = 'Août';
                break;
            case 'September':
                $nomMois = 'Septembre';
                break;
            case 'October':
                $nomMois = 'Octobre';
                break;
            case 'November':
                $nomMois = 'Novembre';
                break;
            case 'December':
                $nomMois = 'Décembre';
                break;
        }

        $dateChiffre = $jour->format('d');
        $MoisLettre = $nomMois;

        return $nomJour.' '.$dateChiffre.' '.$MoisLettre.' : '.$this->time.' - '.$this->time_end;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setDelivery($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getDelivery() === $this) {
                $order->setDelivery(null);
            }
        }

        return $this;
    }



}
