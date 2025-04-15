<?php

namespace App\Service;

use App\Email\OrderConfirmationEmail;
use App\Entity\Order;
use Symfony\Component\Mailer\MailerInterface;
use Psr\Log\LoggerInterface;

class OrderMailer
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly OrderConfirmationEmail $orderConfirmationEmail,
        private readonly LoggerInterface $logger
    ) {
    }

    public function sendOrderConfirmation(Order $order): void
    {
        try {
            $email = $this->orderConfirmationEmail->create($order);
            $this->mailer->send($email);
            
            $this->logger->info('Order confirmation email sent', [
                'order_id' => $order->getId(),
                'customer_email' => $order->getCustomerEmail()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to send order confirmation email', [
                'order_id' => $order->getId(),
                'customer_email' => $order->getCustomerEmail(),
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
} 