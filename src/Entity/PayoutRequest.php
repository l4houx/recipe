<?php

namespace App\Entity;

use App\Entity\Traits\HasDeletedAtTrait;
use App\Entity\Traits\HasGedmoTimestampTrait;
use App\Entity\Traits\HasIdTrait;
use App\Entity\Traits\HasReferenceTrait;
use App\Repository\PayoutRequestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PayoutRequestRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: true)]
class PayoutRequest
{
    use HasIdTrait;
    use HasReferenceTrait;
    use HasGedmoTimestampTrait;
    use HasDeletedAtTrait;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $payment = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 1000)]
    private ?string $note = null;

    /** -2: failed (by the restaurant) / -1: canceled (by the restaurant) / 0: pending / 1: approved (by the administrator) */
    #[ORM\Column(type: Types::INTEGER)]
    private int $status;

    #[ORM\ManyToOne(inversedBy: 'payoutRequests')]
    private ?Restaurant $restaurant = null;

    #[ORM\ManyToOne(inversedBy: 'payoutRequests')]
    private ?RecipeDate $recipeDate = null;

    #[ORM\ManyToOne(inversedBy: 'payoutRequests', fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: true)]
    private ?PaymentGateway $paymentGateway = null;

    public function __construct()
    {
        $this->status = 0;
        $this->reference = $this->generateReference(15);
    }

    public function getPayment(): ?array
    {
        return $this->payment;
    }

    public function setPayment(?array $payment): static
    {
        $this->payment = $payment;

        return $this;
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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;

        return $this;
    }

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
                return 'Pending';
                break;
            case 1:
                return 'Approved';
                break;
            default:
                return 'Unknown';
        }
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

    public function getRecipeDate(): ?RecipeDate
    {
        return $this->recipeDate;
    }

    public function setRecipeDate(?RecipeDate $recipeDate): static
    {
        $this->recipeDate = $recipeDate;

        return $this;
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
}
