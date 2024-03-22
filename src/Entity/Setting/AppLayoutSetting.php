<?php

declare(strict_types=1);

namespace App\Entity\Setting;

use App\Entity\Traits\HasGedmoTimestampTrait;
use App\Entity\Traits\HasIdTrait;
use App\Repository\Setting\AppLayoutSettingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Entity(repositoryClass: AppLayoutSettingRepository::class)]
#[ORM\Table(name: 'app_layout_setting')]
#[Vich\Uploadable]
class AppLayoutSetting
{
    use HasIdTrait;
    use HasGedmoTimestampTrait;

    // NOTE: This is not a mapped field of entity metadata, just a simple property.
    #[Vich\UploadableField(mapping: 'app_layout', fileNameProperty: 'logoName')]
    #[Assert\File(
        maxSize: '5M',
        mimeTypes: ['image/jpeg', 'image/jpg', 'image/gif', 'image/png'],
        mimeTypesMessage: 'The file should be an image'
    )]
    private ?File $logoFile = null;

    #[Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $logoName = null;

    // NOTE: This is not a mapped field of entity metadata, just a simple property.
    #[Vich\UploadableField(mapping: 'app_layout', fileNameProperty: 'faviconName')]
    #[Assert\File(
        maxSize: '5M',
        mimeTypes: ['image/jpeg', 'image/jpg', 'image/gif', 'image/png', 'image/x-icon'],
        mimeTypesMessage: 'The file should be an image'
    )]
    private ?File $faviconFile = null;

    #[Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $faviconName = null;

    // NOTE: This is not a mapped field of entity metadata, just a simple property.
    #[Vich\UploadableField(mapping: 'app_layout', fileNameProperty: 'ogImageName')]
    #[Assert\File(
        maxSize: '5M',
        mimeTypes: ['image/jpeg', 'image/jpg', 'image/gif', 'image/png', 'image/x-icon'],
        mimeTypesMessage: 'The file should be an image'
    )]
    private ?File $ogImageFile = null;

    #[Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $ogImageName = null;

    public function __construct()
    {
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     */
    public function setLogoFile(File|UploadedFile|null $logoFile)
    {
        $this->logoFile = $logoFile;

        if (null !== $logoFile) {
            $this->setUpdatedAt(new \DateTime());
        }

        return $this;
    }

    public function getLogoFile(): ?File
    {
        return $this->logoFile;
    }

    public function getLogoName(): ?string
    {
        return $this->logoName;
    }

    public function setLogoName(?string $logoName): static
    {
        $this->logoName = $logoName;

        return $this;
    }

    public function getLogoPath(): string
    {
        return '/public/layout/'.$this->logoName;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     */
    public function setFaviconFile(File|UploadedFile|null $faviconFile)
    {
        $this->faviconFile = $faviconFile;

        if (null !== $faviconFile) {
            $this->setUpdatedAt(new \DateTime());
        }

        return $this;
    }

    public function getFaviconFile(): ?File
    {
        return $this->faviconFile;
    }

    public function getFaviconName(): ?string
    {
        return $this->faviconName;
    }

    public function setFaviconName(?string $faviconName): static
    {
        $this->faviconName = $faviconName;

        return $this;
    }

    public function getFaviconPath(): string
    {
        return '/public/layout/'.$this->faviconName;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     */
    public function setOgImageFile(File|UploadedFile|null $ogImageFile)
    {
        $this->ogImageFile = $ogImageFile;

        if (null !== $ogImageFile) {
            $this->setUpdatedAt(new \DateTime());
        }

        return $this;
    }

    public function getOgImageFile(): ?File
    {
        return $this->ogImageFile;
    }

    public function getOgImageName(): ?string
    {
        return $this->ogImageName;
    }

    public function setOgImageName(?string $ogImageName): static
    {
        $this->ogImageName = $ogImageName;

        return $this;
    }

    public function getOgImagePath(): string
    {
        return '/public/layout/'.$this->ogImageName;
    }
}
