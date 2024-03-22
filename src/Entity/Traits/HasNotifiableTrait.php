<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait HasNotifiableTrait
{
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $notificationsReadAt = null;

    public function getNotificationsReadAt(): ?\DateTime
    {
        return $this->notificationsReadAt;
    }

    public function setNotificationsReadAt(?\DateTime $notificationsReadAt): void
    {
        $this->notificationsReadAt = $notificationsReadAt;
    }
}
