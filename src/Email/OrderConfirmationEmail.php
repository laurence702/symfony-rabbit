<?php

namespace App\Email;

use App\Entity\Order;
use Symfony\Component\Mime\Email;

class OrderConfirmationEmail
{
    public function create(Order $order): Email
    {
        $email = new Email();
        $email->from('orders@sandbox.smtp.mailtrap.io')
            ->to($order->getCustomerEmail())
            ->subject('Thank you for your order!')
            ->html($this->getHtmlContent($order))
            ->text($this->getTextContent($order));

        return $email;
    }

    private function getHtmlContent(Order $order): string
    {
        return <<<HTML
            <h1>Thank you for your order!</h1>
            <p>Dear customer,</p>
            <p>We have received your order #{$order->getId()} and are processing it.</p>
            <p>Order Details:</p>
            <ul>
                <li>Order ID: {$order->getId()}</li>
                <li>Amount: \${$order->getAmount()}</li>
                <li>Status: {$order->getStatus()}</li>
            </ul>
            <p>We will notify you once your order has been processed.</p>
            <p>Best regards,<br>Your Store Team</p>
        HTML;
    }

    private function getTextContent(Order $order): string
    {
        return <<<TEXT
            Thank you for your order!

            Dear customer,

            We have received your order #{$order->getId()} and are processing it.

            Order Details:
            - Order ID: {$order->getId()}
            - Amount: \${$order->getAmount()}
            - Status: {$order->getStatus()}

            We will notify you once your order has been processed.

            Best regards,
            Your Store Team
        TEXT;
    }
} 