<?php

namespace App\Entity;

use App\Entity\Traits\HasGedmoTimestampTrait;
use App\Entity\Traits\HasIdGedmoNameSlugAssertTrait;
use App\Entity\Traits\HasIsOnlineTrait;
use App\Repository\PaymentGatewayRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: PaymentGatewayRepository::class)]
#[Vich\Uploadable]
class PaymentGateway
{
    use HasIdGedmoNameSlugAssertTrait;
    use HasIsOnlineTrait;
    use HasGedmoTimestampTrait;

    // NOTE: This is not a mapped field of entity metadata, just a simple property.
    #[Vich\UploadableField(mapping: 'payment_gateway', fileNameProperty: 'gatewayLogoName')]
    #[Assert\File(
        maxSize: '2M',
        mimeTypes: ['image/jpeg', 'image/jpg', 'image/gif', 'image/png'],
        mimeTypesMessage: 'The file should be an image'
    )]
    #[Assert\NotNull(groups: ['create'])]
    private ?File $gatewayLogoFile = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $gatewayLogoName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $instructions = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Assert\NotBlank(groups: ['create', 'update'])]
    private ?int $number = null;

    #[ORM\ManyToOne(inversedBy: 'paymentGateways')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Restaurant $restaurant = null;

    #[ORM\OneToMany(mappedBy: 'paymentGateway', targetEntity: PayoutRequest::class)]
    private Collection $payoutRequests;

    #[ORM\OneToMany(mappedBy: 'paymentGateway', targetEntity: Order::class)]
    private Collection $orders;

    public function __construct()
    {
        $this->payoutRequests = new ArrayCollection();
        $this->orders = new ArrayCollection();
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     */
    public function setGatewayLogoFile(File|UploadedFile|null $gatewayLogoFile): static
    {
        $this->gatewayLogoFile = $gatewayLogoFile;

        if (null !== $gatewayLogoFile) {
            $this->setUpdatedAt(new \DateTime());
        }

        return $this;
    }

    public function getGatewayLogoFile(): ?File
    {
        return $this->gatewayLogoFile;
    }

    public function getGatewayLogoName(): ?string
    {
        return $this->gatewayLogoName;
    }

    public function setGatewayLogoName(?string $gatewayLogoName): static
    {
        $this->gatewayLogoName = $gatewayLogoName;

        return $this;
    }

    public function getLogoPath(): string
    {
        return 'uploads/payment/gateways/'.$this->gatewayLogoName;
    }

    public function getInstructions(): ?string
    {
        return $this->instructions;
    }

    public function setInstructions(?string $instructions): static
    {
        $this->instructions = $instructions;

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(?int $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getRestaurant(): ?Restaurant
    {
        return $this->restaurant;
    }

    public function setRestaurant(?Restaurant $restaurant): static
    {
        $this->restaurant = $restaurant;

        return $this;
    }

    /**
     * @return Collection<int, PayoutRequest>
     */
    public function getPayoutRequests(): Collection
    {
        return $this->payoutRequests;
    }

    public function addPayoutRequest(PayoutRequest $payoutRequest): static
    {
        if (!$this->payoutRequests->contains($payoutRequest)) {
            $this->payoutRequests->add($payoutRequest);
            $payoutRequest->setPaymentGateway($this);
        }

        return $this;
    }

    public function removePayoutRequest(PayoutRequest $payoutRequest): static
    {
        if ($this->payoutRequests->removeElement($payoutRequest)) {
            // set the owning side to null (unless already changed)
            if ($payoutRequest->getPaymentGateway() === $this) {
                $payoutRequest->setPaymentGateway(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setPaymentGateway($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getPaymentGateway() === $this) {
                $order->setPaymentGateway(null);
            }
        }

        return $this;
    }
}
