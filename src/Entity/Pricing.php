<?php

namespace App\Entity;

use App\Entity\Traits\HasGedmoTimestampTrait;
use App\Entity\Traits\HasIdTrait;
use App\Entity\Traits\HasStripeEntityTrait;
use App\Repository\PricingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PricingRepository::class)]
class Pricing implements \Stringable
{
    use HasIdTrait;
    use HasStripeEntityTrait;
    use HasGedmoTimestampTrait;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    #[Assert\NotBlank]
    private string $title = '';

    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    #[Assert\NotBlank]
    private string $subtitle = '';

    #[ORM\Column(type: Types::STRING, length: 50, nullable: false)]
    #[Assert\NotBlank]
    private string $btn = '';

    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    #[Assert\NotBlank]
    private string $btntitle = '';

    #[ORM\Column(type: Types::STRING, length: 50, nullable: false)]
    #[Assert\NotBlank]
    private string $border = '';

    #[ORM\Column(type: Types::STRING, length: 50, nullable: false)]
    #[Assert\NotBlank]
    private string $monthly = '';

    #[ORM\Column(type: Types::FLOAT, nullable: false)]
    private float $price = 0;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(min: 4, max: 255)]
    private ?string $pricetitle = null;

    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    /** Duration of the subscription (in months). */
    private int $duration = 1;

    public function __toString(): string
    {
        return $this->title ?: '';
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSubtitle(): string
    {
        return $this->subtitle;
    }

    public function setSubtitle(string $subtitle): static
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    public function getBtn(): string
    {
        return $this->btn;
    }

    public function setBtn(string $btn): static
    {
        $this->btn = $btn;

        return $this;
    }

    public function getBtnTitle(): string
    {
        return $this->btntitle;
    }

    public function setBtnTitle(string $btntitle): static
    {
        $this->btntitle = $btntitle;

        return $this;
    }

    public function getBorder(): string
    {
        return $this->border;
    }

    public function setBorder(string $border): static
    {
        $this->border = $border;

        return $this;
    }

    public function getMonthly(): string
    {
        return $this->monthly;
    }

    public function setMonthly(string $monthly): static
    {
        $this->monthly = $monthly;

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

    public function getPriceTitle(): ?string
    {
        return $this->pricetitle;
    }

    public function setPriceTitle(?string $pricetitle): static
    {
        $this->pricetitle = $pricetitle;

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

    public function getStripeId(): ?string
    {
        return $this->stripeId;
    }

    public function setStripeId(?string $stripeId): static
    {
        $this->stripeId = $stripeId;

        return $this;
    }
}
