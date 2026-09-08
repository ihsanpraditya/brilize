<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class BlockDirectPublicAccessSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        // priority lebih tinggi dari RouterListener (32), biar jalan duluan
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 40],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $uri = $event->getRequest()->server->get('REQUEST_URI', '');

        if (str_starts_with($uri, '/brilize/public/')) {
            /* throw new NotFoundHttpException(); */
            // atau kalau mau redirect:
            $event->setResponse(new \Symfony\Component\HttpFoundation\RedirectResponse(
                '/brilize/' . substr($uri, strlen('/brilize/public/')),
                301
            ));
        }
    }
}
