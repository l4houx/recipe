<?php

namespace App\Twig\Components\Tickets;

use App\Entity\Ticket;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'ticket', template: 'components/tickets/ticket.html.twig')]
class TicketComponent
{
    public Ticket $ticket;
    public bool $hidden = false;
}
