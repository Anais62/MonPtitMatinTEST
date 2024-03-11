<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(length: 255)]
    private ?string $deliveryName = null;

    #[ORM\Column]
    private ?float $deliveryPrice = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $addressDelivery = null;

    #[ORM\OneToMany(targetEntity: OrderDetails::class, mappedBy: 'myOrder')]
    private Collection $orderDetails;

    #[ORM\Column]
    private ?bool $isPaid = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?DeliveryTime $delivery = null;

    #[ORM\Column(length: 255)]
    private ?string $reference = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripeSessionId = null;

    #[ORM\Column]
    private ?int $numberFormule = null;

    #[ORM\Column]
    private ?float $total = null;


    public function __construct()
    {
        $this->orderDetails = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getDeliveryName(): ?string
    {
        return $this->deliveryName;
    }

    public function setDeliveryName(string $deliveryName): static
    {
        $this->deliveryName = $deliveryName;

        return $this;
    }

    public function getDeliveryPrice(): ?float
    {
        return $this->deliveryPrice;
    }

    public function setDeliveryPrice(float $deliveryPrice): static
    {
        $this->deliveryPrice = $deliveryPrice;

        return $this;
    }

    public function getAddressDelivery(): ?string
    {
        return $this->addressDelivery;
    }

    public function setAddressDelivery(string $addressDelivery): static
    {
        $this->addressDelivery = $addressDelivery;

        return $this;
    }

    /**
     * @return Collection<int, OrderDetails>
     */
    public function getOrderDetails(): Collection
    {
        return $this->orderDetails;
    }

    public function addOrderDetail(OrderDetails $orderDetail): static
    {
        if (!$this->orderDetails->contains($orderDetail)) {
            $this->orderDetails->add($orderDetail);
            $orderDetail->setMyOrder($this);
        }

        return $this;
    }

    public function removeOrderDetail(OrderDetails $orderDetail): static
    {
        if ($this->orderDetails->removeElement($orderDetail)) {
            // set the owning side to null (unless already changed)
            if ($orderDetail->getMyOrder() === $this) {
                $orderDetail->setMyOrder(null);
            }
        }

        return $this;
    }

    public function isIsPaid(): ?bool
    {
        return $this->isPaid;
    }

    public function setIsPaid(bool $isPaid): static
    {
        $this->isPaid = $isPaid;

        return $this;
    }

    public function getDelivery(): ?DeliveryTime
    {
        return $this->delivery;
    }

    public function setDelivery(?DeliveryTime $delivery): static
    {
        $this->delivery = $delivery;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getStripeSessionId(): ?string
    {
        return $this->stripeSessionId;
    }

    public function setStripeSessionId(?string $stripeSessionId): static
    {
        $this->stripeSessionId = $stripeSessionId;

        return $this;
    }

    public function getNumberFormule(): ?int
    {
        return $this->numberFormule;
    }

    public function setNumberFormule(int $numberFormule): static
    {
        $this->numberFormule = $numberFormule;

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): static
    {
        $this->total = $total;

        return $this;
    }

   
}
