<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Event\LogoutEvent;

/**
 * Handles logout events to revoke refresh tokens and return JSON response.
 * Works for all user types (Owner, Receptionist, Mechanic).
 */
final class LogoutEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => 'onLogout',
        ];
    }

    public function onLogout(LogoutEvent $event): void
    {
        // Get the authenticated user
        $token = $event->getToken();
        if (!$token) {
            return;
        }

        $user = $token->getUser();
        if (!$user) {
            return;
        }

        // Get username (email for Owner/Receptionist, ID for Mechanic)
        $username = $user->getUserIdentifier();

        // Revoke all refresh tokens for this user
        $this->revokeRefreshTokens($username);

        // Set custom JSON response (modern Symfony 7 way)
        $response = new JsonResponse([
            'message' => 'Logged out successfully',
        ], Response::HTTP_OK);

        $event->setResponse($response);
    }

    /**
     * Delete all refresh tokens for a user.
     * For Mechanics (no refresh tokens), this does nothing.
     */
    private function revokeRefreshTokens(string $username): void
    {
        $connection = $this->entityManager->getConnection();

        $connection->executeStatement(
            'DELETE FROM refresh_tokens WHERE username = :username',
            ['username' => $username]
        );
    }
}
