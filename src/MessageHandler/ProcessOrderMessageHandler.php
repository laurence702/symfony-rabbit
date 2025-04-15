<?php

namespace App\MessageHandler;

use App\Message\ProcessOrderMessage;
use App\Repository\OrderRepository;
use App\Service\OrderMailer;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ProcessOrderMessageHandler
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly OrderMailer $orderMailer
    ) {
    }

    public function __invoke(ProcessOrderMessage $message): void
    {
        $order = $this->orderRepository->find($message->getOrderId());
        
        if (!$order) {
            throw new \RuntimeException('Order not found');
        }

        // Simulate processing time
        sleep(10);

        // Send confirmation email
        $this->orderMailer->sendOrderConfirmation($order);

        $order->setStatus('processed');
        $this->orderRepository->save($order, true);
    }
} 