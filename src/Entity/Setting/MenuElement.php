<?php

declare(strict_types=1);

namespace App\Entity\Setting;

use App\Entity\Traits\HasIconTrait;
use App\Entity\Traits\HasIdGedmoLabelSlugAssertTrait;
use App\Repository\Setting\MenuElementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MenuElementRepository::class)]
class MenuElement
{
    use HasIdGedmoLabelSlugAssertTrait;
    use HasIconTrait;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $link;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $customLink;

    #[ORM\ManyToOne(targetEntity: Menu::class, inversedBy: 'menuElements')]
    private Menu $menu;

    #[ORM\Column(name: 'position')]
    private int $position;

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): static
    {
        $this->link = $link;

        return $this;
    }

    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): static
    {
        $this->menu = $menu;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getCustomLink(): ?string
    {
        return $this->customLink;
    }

    public function setCustomLink(?string $customLink): static
    {
        $this->customLink = $customLink;

        return $this;
    }
}
