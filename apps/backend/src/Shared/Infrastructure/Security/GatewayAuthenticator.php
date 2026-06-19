<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

final class GatewayAuthenticator extends AbstractAuthenticator
{
    public function supports(Request $request): bool
    {
        return $request->headers->has('X-User-Id');
    }

    public function authenticate(Request $request): \Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport
    {
        $userId = $request->headers->get('X-User-Id');

        if (null === $userId || '' === $userId) {
            throw new AuthenticationException('Missing X-User-Id header');
        }

        $roles = array_values(array_filter(
            explode(',', (string) $request->headers->get('X-User-Roles', ''))
        ));

        return new SelfValidatingPassport(
            new UserBadge($userId, static fn (): \App\Shared\Infrastructure\Security\GatewayUser => new GatewayUser($userId, $roles))
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): \Symfony\Component\HttpFoundation\JsonResponse
    {
        return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
    }
}
