<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Adds garage context to JWT payload for Receptionist users.
 * Owner users do not get garage context (multi-garage access).
 */
final class JwtCreatedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::JWT_CREATED => 'onJwtCreated',
        ];
    }

    public function onJwtCreated(JWTCreatedEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        // Check if garage context was set (only for Receptionist)
        $garageContext = $request->attributes->get('garage_context');
        if (!$garageContext) {
            return;
        }

        // Add garage context to JWT payload
        $payload = $event->getData();
        $payload['garage_id'] = $garageContext['garage_id'];
        $payload['garage_siret'] = $garageContext['garage_siret'];

        $event->setData($payload);
    }
}
