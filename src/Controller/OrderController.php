<?php

namespace App\Controller;

use App\Entity\Order;
use App\Event\OrderCreatedEvent;
use App\Message\ProcessOrderMessage;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly MessageBusInterface $messageBus
    ) {
    }

    #[Route('/api/orders', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return new JsonResponse(
                    ['error' => 'Invalid JSON format'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            if (!isset($data['email']) || !isset($data['amount'])) {
                return new JsonResponse(
                    ['error' => 'Missing required fields: email and amount'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $order = new Order();
            $order->setCustomerEmail($data['email']);
            $order->setAmount($data['amount']);

            $this->orderRepository->save($order, true);

            // Dispatch event
            $this->eventDispatcher->dispatch(
                new OrderCreatedEvent($order),
                OrderCreatedEvent::NAME
            );

            // Send message to queue
            $this->messageBus->dispatch(
                new ProcessOrderMessage($order->getId())
            );

            return new JsonResponse(
                [
                    'id' => $order->getId(),
                    'email' => $order->getCustomerEmail(),
                    'amount' => $order->getAmount(),
                    'status' => $order->getStatus(),
                    'created_at' => $order->getCreatedAt()->format('Y-m-d H:i:s')
                ],
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'An error occurred while processing your request'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/api/orders/{id}', methods: ['GET'])]
    public function get(int $id): JsonResponse
    {
        try {
            $order = $this->orderRepository->find($id);

            if (!$order) {
                return new JsonResponse(
                    ['error' => 'Order not found'],
                    Response::HTTP_NOT_FOUND
                );
            }

            return new JsonResponse([
                'id' => $order->getId(),
                'email' => $order->getCustomerEmail(),
                'amount' => $order->getAmount(),
                'status' => $order->getStatus(),
                'created_at' => $order->getCreatedAt()->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'An error occurred while retrieving the order'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
} 