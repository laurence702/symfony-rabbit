<?php

namespace App\Event;

use App\Entity\Order;
use Symfony\Contracts\EventDispatcher\Event;

class OrderCreatedEvent extends Event
{
    public const NAME = 'order.created';

    public function __construct(
        private readonly Order $order
    ) {
    }

    public function getOrder(): Order
    {
        return $this->order;
    }
} 