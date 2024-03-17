<?php

namespace App\Entity\Setting;

use App\Entity\Traits\HasIdTrait;
use App\Repository\Setting\CurrencyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CurrencyRepository::class)]
#[UniqueEntity(fields: ['ccy'])]
class Currency
{
    use HasIdTrait;

    #[ORM\Column(type: Types::STRING, length: 3, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 3)]
    private ?string $ccy = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 50)]
    private ?string $symbol = null;

    public function getCcy(): ?string
    {
        return $this->ccy;
    }

    public function setCcy(string $ccy): static
    {
        $this->ccy = $ccy;

        return $this;
    }

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(?string $symbol): static
    {
        $this->symbol = $symbol;

        return $this;
    }
}
