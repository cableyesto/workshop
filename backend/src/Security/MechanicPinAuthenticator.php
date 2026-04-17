<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Mechanic;
use App\Repository\GarageRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * Custom authenticator for Mechanic PIN-based authentication.
 * Handles authentication for mechanics using a 4-digit PIN and garage SIRET.
 *
 * @see https://symfony.com/doc/current/security/custom_authenticator.html
 */
class MechanicPinAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly GarageRepository $garageRepository,
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): ?bool
    {
        // Check if this is a POST request to the mechanic login endpoint
        // Pattern: /api/garage/{siret}/mechanic/login
        return $request->isMethod('POST')
            && 1 === preg_match('#^/api/garage/[^/]+/mechanic/login$#', $request->getPathInfo());
    }

    public function authenticate(Request $request): Passport
    {
        // 1. Extract SIRET from URL path (/api/garage/{siret}/mechanic/login)
        $pathInfo = $request->getPathInfo();
        if (!preg_match('#^/api/garage/([^/]+)/mechanic/login$#', $pathInfo, $matches)) {
            throw new CustomUserMessageAuthenticationException('Invalid login URL format.');
        }
        $siret = $matches[1];

        // 2. Parse JSON body
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            throw new CustomUserMessageAuthenticationException('Invalid request body.');
        }

        $pin = $data['pin'] ?? null;

        if (!$pin) {
            throw new CustomUserMessageAuthenticationException('Missing PIN.');
        }

        // 3. Find Garage by SIRET
        $garage = $this->garageRepository->findOneBy(['siretNumber' => $siret]);
        if (!$garage) {
            throw new CustomUserMessageAuthenticationException('Garage not found.');
        }

        // 4. Find all mechanics working at this garage
        $mechanics = $garage->getEmployees()->filter(
            fn ($employee) => $employee instanceof Mechanic
        );

        if ($mechanics->isEmpty()) {
            throw new CustomUserMessageAuthenticationException('Invalid credentials.');
        }

        // 5. Search for mechanic with matching PIN
        $authenticatedMechanic = null;
        foreach ($mechanics as $mechanic) {
            /** @var Mechanic $mechanic */
            if ($mechanic->verifyPin($pin)) {
                $authenticatedMechanic = $mechanic;
                break;
            }
        }

        if (!$authenticatedMechanic) {
            throw new CustomUserMessageAuthenticationException('Invalid credentials.');
        }

        // 6. Return SelfValidatingPassport with mechanic ID as identifier
        // Symfony will automatically load the user via the mechanic_provider configured in security.yaml
        return new SelfValidatingPassport(
            new UserBadge((string) $authenticatedMechanic->getId())
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        /** @var Mechanic $mechanic */
        $mechanic = $token->getUser();

        // Generate JWT token for the authenticated mechanic
        $jwt = $this->jwtManager->create($mechanic);

        // Return JWT token in the response (same format as lexik's success handler)
        return new JsonResponse([
            'token' => $jwt,
        ]);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            // you may want to customize or obfuscate the message first
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    // public function start(Request $request, ?AuthenticationException $authException = null): Response
    // {
    //     /*
    //      * If you would like this class to control what happens when an anonymous user accesses a
    //      * protected page (e.g. redirect to /login), uncomment this method and make this class
    //      * implement Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface.
    //      *
    //      * For more details, see https://symfony.com/doc/current/security/experimental_authenticators.html#configuring-the-authentication-entry-point
    //      */
    // }
}
