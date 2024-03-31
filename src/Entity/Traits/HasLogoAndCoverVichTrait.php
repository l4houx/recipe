<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

trait HasLogoAndCoverVichTrait
{
    use HasGedmoTimestampTrait;

    // NOTE: This is not a mapped field of entity metadata, just a simple property.
    #[Vich\UploadableField(mapping: 'restaurant_logo', fileNameProperty: 'logoName')]
    #[Assert\File(
        maxSize: '2M',
        mimeTypes: ['image/jpeg', 'image/jpg', 'image/gif', 'image/png'],
        mimeTypesMessage: 'The file should be an image'
    )]
    private ?File $logoFile = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $logoName = null;

    // NOTE: This is not a mapped field of entity metadata, just a simple property.
    #[Vich\UploadableField(mapping: 'restaurant_cover', fileNameProperty: 'coverName')]
    #[Assert\File(
        maxSize: '2M',
        mimeTypes: ['image/jpeg', 'image/jpg', 'image/gif', 'image/png'],
        mimeTypesMessage: 'The file should be an image'
    )]
    private ?File $coverFile = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $coverName = null;

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     */
    public function setLogoFile(File|UploadedFile|null $logoFile): static
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
        return '/images/restaurant/'.$this->logoName;
    }

    public function getLogoPlaceholder(string $size = 'default'): string
    {
        if ($size == "small") {
            return "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEYAAABGCAMAAABG8BK2AAAA4VBMVEUAAAD2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhFAEufoAAAASnRSTlMAAQIDBAUGBwkNDg8QERMUGx0fICUmKi04PT9BQ0ZHSUpMTVZXWVtiZGZndHV5e3+FiImMkZSanaOlsLK3ytPa3N7i6On19/n7/X4N0FUAAAFVSURBVFjD7ddpT8IwAAbglolDRQ4PwGMqHrTWA9AhTgERdWj//w9yCyauYe16LCTK3o99mydp2i4dAFmy/Mfkz4LUYopaWORlmSIN0o4p2mFRXFpm8wThTjh7iOczDIsORs5agnJD5XIpVK4kFbGzIa1QKljXsQLj8BmkwCA+gxUYrMb4g4eBb8qM6jAYh/WREdPL/TS5ngHzGOk8beYj+mlZ9XUZ9qQSXabClBVdpsCUBV3GZkpblykxZUmXaTJlU5d5gZEOjrWPH+Hvt9Jl+F3WKTVgaHe2W3aXGjGUei2n5cWMKzK8LD3zRBrV8nq52iDPuszn3Y4V6azd+y91ZnxgzdUrh6+KzDmMnQAvVJjpFnfK9lSe2eNPAfvSzBsQ5V2W6QuZvizjChl3wczEFWWyqDuV0msrpbdfSi9RcC2tECDKbSrK7J8hKego6Z8hS5Y/mG9fd7+lNzcPFwAAAABJRU5ErkJggg==";
        } else {
            return "data:image/png;base64,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";
        }
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     */
    public function setCoverFile(File|UploadedFile|null $coverFile): static
    {
        $this->coverFile = $coverFile;

        if (null !== $coverFile) {
            $this->setUpdatedAt(new \DateTime());
        }

        return $this;
    }

    public function getCoverFile(): ?File
    {
        return $this->coverFile;
    }

    public function getCoverName(): ?string
    {
        return $this->coverName;
    }

    public function setCoverName(?string $coverName): static
    {
        $this->coverName = $coverName;

        return $this;
    }

    public function getCoverPath(): string
    {
        return '/images/restaurant/covers/'.$this->coverName;
    }
}
