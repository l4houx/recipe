<?php

namespace App\Entity;

use App\Entity\Traits\HasIdTrait;
use App\Repository\TransactionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Intl\Countries;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{
    use HasIdTrait;
    final public const PAYPAL = 'paypal';
    final public const STRIPE = 'stripe';

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $firstname = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $lastname = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $postalCode = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $countryCode = null;

    #[ORM\Column(type: Types::FLOAT, options: ['default' => 0])]
    private float $fee = 0;

    #[ORM\Column(type: Types::INTEGER)]
    private int $duration;

    /** Purchase price (VAT included). */
    #[ORM\Column(type: Types::FLOAT)]
    private float $price;

    #[ORM\Column(type: Types::FLOAT)]
    private float $tax;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $method;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $methodRef = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $refunded = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $author;

    public function getDescription(): string
    {
        return "Premium account {$this->duration} month";
    }

    public function getFullName(): string
    {
        return $this->lastname.' '.$this->firstname;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCountry(): ?string
    {
        return Countries::getNames()[$this->countryCode] ?? null;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(?string $countryCode): static
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function getFee(): float
    {
        return $this->fee;
    }

    public function setFee(float $fee): static
    {
        $this->fee = $fee;

        return $this;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getTax(): float
    {
        return $this->tax;
    }

    public function setTax(float $tax): static
    {
        $this->tax = $tax;

        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): static
    {
        $this->method = $method;

        return $this;
    }

    public function getMethodRef(): ?string
    {
        return $this->methodRef;
    }

    public function setMethodRef(?string $methodRef): static
    {
        $this->methodRef = $methodRef;

        return $this;
    }

    public function isRefunded(): bool
    {
        return $this->refunded;
    }

    public function setRefunded(bool $refunded): static
    {
        $this->refunded = $refunded;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function setAuthor(User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function isPaypal(): bool
    {
        return static::PAYPAL === $this->method;
    }

    public function isStripe(): bool
    {
        return static::STRIPE === $this->method;
    }
}
