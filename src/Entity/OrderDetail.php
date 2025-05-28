<?php

namespace App\Entity;

use App\Repository\OrderDetailRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderDetailRepository::class)]
class OrderDetail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orderDetails')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $orderRef = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $unitPriceHT = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $unitPriceTTC = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $totalPriceHT = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $totalPriceTTC = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $discountPercentage = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderRef(): ?Order
    {
        return $this->orderRef;
    }

    public function setOrderRef(?Order $orderRef): static
    {
        $this->orderRef = $orderRef;
        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;
        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getUnitPriceHT(): ?string
    {
        return $this->unitPriceHT;
    }

    public function setUnitPriceHT(string $unitPriceHT): static
    {
        $this->unitPriceHT = $unitPriceHT;
        return $this;
    }

    public function getUnitPriceTTC(): ?string
    {
        return $this->unitPriceTTC;
    }

    public function setUnitPriceTTC(string $unitPriceTTC): static
    {
        $this->unitPriceTTC = $unitPriceTTC;
        return $this;
    }

    public function getTotalPriceHT(): ?string
    {
        return $this->totalPriceHT;
    }

    public function setTotalPriceHT(string $totalPriceHT): static
    {
        $this->totalPriceHT = $totalPriceHT;
        return $this;
    }

    public function getTotalPriceTTC(): ?string
    {
        return $this->totalPriceTTC;
    }

    public function setTotalPriceTTC(string $totalPriceTTC): static
    {
        $this->totalPriceTTC = $totalPriceTTC;
        return $this;
    }

    public function getDiscountPercentage(): ?string
    {
        return $this->discountPercentage;
    }

    public function setDiscountPercentage(?string $discountPercentage): static
    {
        $this->discountPercentage = $discountPercentage;
        return $this;
    }
} 