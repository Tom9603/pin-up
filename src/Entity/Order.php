<?php

namespace App\Entity;

use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\Column(length: 20, enumType: OrderStatus::class)]
    private OrderStatus $status = OrderStatus::Pending;

    #[ORM\Column]
    private ?int $total = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripeSessionId = null;

    #[ORM\Column(length: 20, nullable: true, unique: true)]
    private ?string $invoiceNumber = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shippingName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shippingLine1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shippingLine2 = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $shippingPostalCode = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $shippingCity = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $shippingCountry = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $shippingPhone = null;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'orderRef')]
    private Collection $orderItems;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->orderItems = new ArrayCollection();
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

    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    public function setStatus(OrderStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function isPaid(): bool
    {
        return in_array($this->status, [
            OrderStatus::Paid,
            OrderStatus::Shipped,
            OrderStatus::Delivered,
        ], true);
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function setTotal(int $total): static
    {
        $this->total = $total;

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

    public function getInvoiceNumber(): ?string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(?string $invoiceNumber): static
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getShippingName(): ?string
    {
        return $this->shippingName;
    }

    public function setShippingName(?string $shippingName): static
    {
        $this->shippingName = $shippingName;
        return $this;
    }

    public function getShippingLine1(): ?string
    {
        return $this->shippingLine1;
    }

    public function setShippingLine1(?string $shippingLine1): static
    {
        $this->shippingLine1 = $shippingLine1;
        return $this;
    }

    public function getShippingLine2(): ?string
    {
        return $this->shippingLine2;
    }

    public function setShippingLine2(?string $shippingLine2): static
    {
        $this->shippingLine2 = $shippingLine2;
        return $this;
    }

    public function getShippingPostalCode(): ?string
    {
        return $this->shippingPostalCode;
    }

    public function setShippingPostalCode(?string $shippingPostalCode): static
    {
        $this->shippingPostalCode = $shippingPostalCode;
        return $this;
    }

    public function getShippingCity(): ?string
    {
        return $this->shippingCity;
    }

    public function setShippingCity(?string $shippingCity): static
    {
        $this->shippingCity = $shippingCity;
        return $this;
    }

    public function getShippingCountry(): ?string
    {
        return $this->shippingCountry;
    }

    public function setShippingCountry(?string $shippingCountry): static
    {
        $this->shippingCountry = $shippingCountry;
        return $this;
    }

    public function getShippingPhone(): ?string
    {
        return $this->shippingPhone;
    }

    public function setShippingPhone(?string $shippingPhone): static
    {
        $this->shippingPhone = $shippingPhone;
        return $this;
    }

    public function hasShippingAddress(): bool
    {
        return $this->shippingLine1 !== null && $this->shippingCity !== null;
    }

    public function getFormattedShippingAddress(): string
    {
        if (!$this->hasShippingAddress()) {
            return '';
        }

        $lines = array_filter([
            $this->shippingName,
            $this->shippingLine1,
            $this->shippingLine2,
            trim(($this->shippingPostalCode ?? '') . ' ' . ($this->shippingCity ?? '')),
            $this->shippingCountry,
        ]);

        return implode("\n", $lines);
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setOrderRef($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            // set the owning side to null (unless already changed)
            if ($orderItem->getOrderRef() === $this) {
                $orderItem->setOrderRef(null);
            }
        }

        return $this;
    }
}
