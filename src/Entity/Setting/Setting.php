<?php

namespace App\Entity\Setting;

use App\Entity\Traits\HasIdTrait;
use App\Repository\Setting\SettingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SettingRepository::class)]
class Setting
{
    use HasIdTrait;

    public const WEBSITE_NAME = 'website_name';
    public const HEADER_NAME = 'header_name';
    public const WEBSITE_COPYRIGHT = 'website_copyright';
    public const GOOGLE_RECAPTCHA_ENABLED = 'google_recaptcha_enabled';
    public const MAINTENANCE_MODE_CUSTOM_MESSAGE = 'maintenance_mode_custom_message';

    // Limit
    public const WEBSITE_POSTS_SEARCH_LIMIT = 'website_posts_search_limit'; // 10
    public const WEBSITE_POSTS_LIMIT = 'website_posts_limit'; // 9
    public const WEBSITE_COMMENTS_LIMIT = 'website_comments_limit'; // 4
    public const WEBSITE_RECIPES_LIMIT = 'website_recipes_limit'; // 5

    // Limit Homepage
    public const HOMEPAGE_POSTS_NUMBER = 'homepage_posts_number'; // 5
    public const HOMEPAGE_TESTIMONIALS_NUMBER = 'homepage_testimonials_number'; // 5
    public const HOMEPAGE_CATEGORIES_NUMBER = 'homepage_categories_number'; // 12
    public const HOMEPAGE_RECIPES_NUMBER = 'homepage_recipes_number'; // 12

    // Newsletter
    public const MAILCHIMP_LIST_ID = 'mailchimp_list_id';

    #[ORM\Column(type: Types::STRING, length: 255)]
    private $label = '';

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    private $name = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private $value = '';

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $type = null;

    public function __construct(string $label, string $name, string $value, string $type = null)
    {
        $this->label = $label;
        $this->name = $name;
        $this->value = $value;
        $this->type = $type;
    }

    public function __toString(): string
    {
        return $this->value ?? '';
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }
}
