<?php

declare(strict_types=1);

namespace App\Entity\Setting;

use App\Entity\Traits\HasIdGedmoNameSlugAssertTrait;
use App\Repository\Setting\MenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MenuRepository::class)]
class Menu
{
    use HasIdGedmoNameSlugAssertTrait;

    #[ORM\Column(type: Types::STRING, length: 128, nullable: true)]
    #[Assert\Length(min: 1, max: 128)]
    private ?string $header = null;

    #[ORM\OneToMany(mappedBy: 'menu', targetEntity: MenuElement::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['position' => 'ASC'])]
    #[Assert\Valid]
    private Collection $menuElements;

    public function __construct()
    {
        $this->menuElements = new ArrayCollection();
    }

    public function getHeader(): ?string
    {
        return $this->header;
    }

    public function setHeader(?string $header): static
    {
        $this->header = $header;

        return $this;
    }

    /**
     * @return Collection<int, MenuElement>
     */
    public function getMenuElements(): Collection
    {
        return $this->menuElements;
    }

    public function addMenuElement(MenuElement $menuElement): static
    {
        $menuElement->setMenu($this);
        $this->menuElements->add($menuElement);

        return $this;
    }

    public function removeMenuElement(MenuElement $menuElement): static
    {
        $menuElement->setMenu(null);
        $this->menuElements->removeElement($menuElement);

        return $this;
    }
}
