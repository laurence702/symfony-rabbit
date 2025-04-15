<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException'
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        
        // Only handle API routes
        if (str_starts_with($request->getPathInfo(), '/api/')) {
            $exception = $event->getThrowable();
            
            $response = new JsonResponse([
                'error' => $exception->getMessage(),
                'code' => $exception->getCode()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
            
            $event->setResponse($response);
        }
    }
} 