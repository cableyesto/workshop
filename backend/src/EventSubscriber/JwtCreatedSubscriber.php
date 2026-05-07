<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Owner;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Adds garage context to JWT payload:
 * - Receptionist: garage_id, garage_siret (single garage)
 * - Owner: garage_ids (array of all owned garage IDs)
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
        $payload = $event->getData();
        $user = $event->getUser();

        // Handle Owner: add all garage IDs
        if ($user instanceof Owner) {
            $garageIds = $user->getGarages()
                ->map(fn($garage) => $garage->getId())
                ->toArray();

            $payload['garage_ids'] = array_values($garageIds);
            $event->setData($payload);
            return;
        }

        // Handle Receptionist: add single garage context
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        $garageContext = $request->attributes->get('garage_context');
        if (!$garageContext) {
            return;
        }

        $payload['garage_id'] = $garageContext['garage_id'];
        $payload['garage_siret'] = $garageContext['garage_siret'];

        $event->setData($payload);
    }
}
