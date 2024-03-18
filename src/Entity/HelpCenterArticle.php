<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\HasViewsTrait;
use App\Entity\Traits\HasContentTrait;
use App\Entity\Traits\HasIsOnlineTrait;
use App\Entity\Traits\HasDeletedAtTrait;
use App\Entity\Traits\HasTimestampTrait;
use App\Entity\Traits\HasIsFeaturedTrait;
use App\Entity\Traits\HasGedmoTimestampTrait;
use App\Repository\HelpCenterArticleRepository;
use App\Entity\Traits\HasIdTitleSlugAssertTrait;
use App\Entity\Traits\HasIdGedmoTitleSlugAssertTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: HelpCenterArticleRepository::class)]
#[UniqueEntity('title')]
#[UniqueEntity('slug')]
class HelpCenterArticle
{
    use HasIdTitleSlugAssertTrait;
    //use HasIdGedmoTitleSlugAssertTrait;
    use HasContentTrait;
    use HasViewsTrait;
    use HasIsOnlineTrait;
    use HasIsFeaturedTrait;
    use HasTimestampTrait;
    //use HasGedmoTimestampTrait;
    use HasDeletedAtTrait;

    #[ORM\Column(type: Types::STRING, length: 150, nullable: true)]
    #[Assert\Length(max: 150)]
    private ?string $tags = null;

    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?HelpCenterCategory $category = null;

    public function getTags(): ?string
    {
        return $this->tags;
    }

    public function setTags(?string $tags): static
    {
        $this->tags = $tags;

        return $this;
    }

    public function getCategory(): ?HelpCenterCategory
    {
        return $this->category;
    }

    public function setCategory(?HelpCenterCategory $category): static
    {
        $this->category = $category;

        return $this;
    }
}