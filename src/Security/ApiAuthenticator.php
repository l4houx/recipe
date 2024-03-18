<?php

namespace App\Security;

use App\Repository\ApplicationRepository;
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
use Symfony\Contracts\Translation\TranslatorInterface;

class ApiAuthenticator extends AbstractAuthenticator
{
    final public const API_ROUTE = '/api/';

    public function __construct(
        private readonly ApplicationRepository $applicationRepository,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return str_starts_with($request->getPathInfo(), self::API_ROUTE);
    }

    public function authenticate(Request $request): Passport
    {
        $apiToken = $request->headers->get('Authorization');
        if (null === $apiToken) {
            throw new CustomUserMessageAuthenticationException($this->translator->trans('Required token !'));
        }

        $bearerPlain = explode(' ', $apiToken)[1];
        $websiteSecuritySalt = $_ENV['WEBSITE_SECURITY_SALT'];
        $bearer = crypt($bearerPlain, $websiteSecuritySalt);

        return new SelfValidatingPassport(
            new UserBadge($bearer, function ($bearer) {
                $application = $this->applicationRepository->findOneBy(['token' => $bearer]);
                if (!$application) {
                    throw new CustomUserMessageAuthenticationException($this->translator->trans('Invalid token !'));
                }

                return $application;
            })
        );
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName
    ): ?Response {
        return null;
    }

    public function onAuthenticationFailure(
        Request $request,
        AuthenticationException $exception
    ): ?Response {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
