<?php

namespace App\Entity;

use App\Entity\Traits\HasDeletedAtTrait;
use App\Entity\Traits\HasGedmoTimestampTrait;
use App\Entity\Traits\HasIdTrait;
use App\Entity\Traits\HasReferenceTrait;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: true)]
#[ORM\Table(name: '`order`')]
class Order
{
    use HasIdTrait;
    use HasReferenceTrait;
    use HasGedmoTimestampTrait;
    use HasDeletedAtTrait;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 1000)]
    private ?string $note = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $subscriptionFee = '';

    #[ORM\Column(type: Types::INTEGER)]
    private int $subscriptionPricePercentageCut;

    /** -2: failed / -1: cancel / 0: waiting for payment / 1: paid */
    #[ORM\Column(type: Types::INTEGER)]
    private int $status;

    #[ORM\Column(length: 10)]
    #[Assert\Length(max: 10)]
    private ?string $currencyCcy = null;

    #[ORM\Column(length: 10)]
    #[Assert\Length(max: 10)]
    private ?string $currencySymbol = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?User $user = null;

    /**
     * @var collection<int, OrderElement>
     */
    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderElement::class, cascade: ['persist', 'remove'], fetch: 'EAGER')]
    private Collection $orderelements;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: true)]
    private ?PaymentGateway $paymentGateway = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'], fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Payment $payment = null;

    public function __construct()
    {
        $this->status = 0;
        $this->orderelements = new ArrayCollection();
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getSubscriptionFee(): ?string
    {
        return (float) $this->subscriptionFee;
    }

    public function setSubscriptionFee(string $subscriptionFee): static
    {
        $this->subscriptionFee = $subscriptionFee;

        return $this;
    }

    public function getSubscriptionPricePercentageCut(): ?int
    {
        return $this->subscriptionPricePercentageCut;
    }

    public function setSubscriptionPricePercentageCut(int $subscriptionPricePercentageCut): static
    {
        $this->subscriptionPricePercentageCut = $subscriptionPricePercentageCut;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;

        return $this;
    }

    // -2: failed / -1: cancel / 0: waiting for payment / 1: paid
    public function getStatusClass(): string
    {
        switch ($this->status) {
            case -2:
                return 'danger';
                break;
            case -1:
                return 'danger';
                break;
            case 0:
                return 'warning';
                break;
            case 1:
                return 'success';
                break;
            default:
                return 'danger';
        }
    }

    public function stringifyStatus(): string
    {
        switch ($this->status) {
            case -2:
                return 'Failed';
                break;
            case -1:
                return 'Canceled';
                break;
            case 0:
                return 'Awaiting payment';
                break;
            case 1:
                return 'Paid';
                break;
            default:
                return 'Unknown';
        }
    }

    public function getPaymentStatusClass(string $status): string
    {
        if ('new' == $status) {
            return 'info';
        } elseif ('pending' == $status) {
            return 'warning';
        } elseif ('authorized' == $status) {
            return 'success';
        } elseif ('captured' == $status) {
            return 'success';
        } elseif ('canceled' == $status) {
            return 'danger';
        } elseif ('suspended' == $status) {
            return 'danger';
        } elseif ('failed' == $status) {
            return 'danger';
        } elseif ('unknown' == $status) {
            return 'danger';
        }
    }

    public function getCurrencyCcy(): ?string
    {
        return $this->currencyCcy;
    }

    public function setCurrencyCcy(string $currencyCcy): static
    {
        $this->currencyCcy = $currencyCcy;

        return $this;
    }

    public function getCurrencySymbol(): ?string
    {
        return $this->currencySymbol;
    }

    public function setCurrencySymbol(string $currencySymbol): static
    {
        $this->currencySymbol = $currencySymbol;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, OrderElement>
     */
    public function getOrderElements(): Collection
    {
        return $this->orderelements;
    }

    public function addOrderElement(OrderElement $orderelement): static
    {
        if (!$this->orderelements->contains($orderelement)) {
            $this->orderelements->add($orderelement);
            $orderelement->setOrder($this);
        }

        return $this;
    }

    public function removeOrderElement(OrderElement $orderelement): static
    {
        if ($this->orderelements->removeElement($orderelement)) {
            // set the owning side to null (unless already changed)
            if ($orderelement->getOrder() === $this) {
                $orderelement->setOrder(null);
            }
        }

        return $this;
    }

    public function containsRecipe(mixed $recipe): bool
    {
        foreach ($this->orderelements as $orderElement) {
            if ($orderElement->getRecipeSubscription()->getRecipeDate()->getRecipe() == $recipe) {
                return true;
            }
        }

        return false;
    }

    public function getOrderElementsQuantitySum(string $status = 'all', string $restaurant = 'all'): mixed
    {
        $count = 0;
        if ('all' == $status || $this->status === $status) {
            foreach ($this->orderelements as $orderelement) {
                if ('all' == $restaurant || $orderelement->getRecipeSubscription()->getRecipeDate()->getRecipe()->getRestaurant()->getSlug() == $restaurant) {
                    $count += $orderelement->getQuantity();
                }
            }
        }

        return $count;
    }

    public function getOrderElementsPriceSum(bool $includeFees = false): float
    {
        $sum = 0;
        foreach ($this->orderelements as $orderelement) {
            $sum += $orderelement->getPrice();
        }
        if ($includeFees) {
            $sum += $this->getTotalFees();
        }

        return (float) $sum;
    }

    public function getTotalSubscriptionFees()
    {
        if (!$this->getSubscriptionFee()) {
            return 0;
        }

        return $this->getNotFreeOrderElementsQuantitySum() * $this->getSubscriptionFee();
    }

    public function getTotalFees(): mixed
    {
        $sum = 0;
        $sum += $this->getTotalSubscriptionFees();

        return $sum;
    }

    public function getNotFreeOrderElementsQuantitySum(): mixed
    {
        $sum = 0;
        foreach ($this->orderelements as $orderelement) {
            if (!$orderelement->getRecipeSubscription()->getIsFree()) {
                $sum += $orderelement->getQuantity();
            }
        }

        return $sum;
    }

    public function getPaymentGateway(): ?PaymentGateway
    {
        return $this->paymentGateway;
    }

    public function setPaymentGateway(?PaymentGateway $paymentGateway): static
    {
        $this->paymentGateway = $paymentGateway;

        return $this;
    }

    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    public function setPayment(?Payment $payment): static
    {
        $this->payment = $payment;

        return $this;
    }
}
