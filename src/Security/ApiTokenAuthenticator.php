<?php

namespace App\Security;

use App\Repository\ApiTokenRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class ApiTokenAuthenticator extends AbstractGuardAuthenticator
{
    private ApiTokenRepository $apiTokenRepo;

    public function __construct(ApiTokenRepository $apiTokenRepo)
    {
        $this->apiTokenRepo = $apiTokenRepo;
    }

    public function supports(Request $request)
    {
       return $request->headers->has('Authorization')
           && 0 === strpos($request->headers->get('Authorization'), 'Bearer');
    }

    public function getCredentials(Request $request)
    {
       $authorizationHeader = $request->headers->get('Authorization');

       // skip beyond "Bearer "
        return substr($authorizationHeader, 7);
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
       $token = $this->apiTokenRepo->findOneBy([
           'token' => $credentials
       ]);
       if (! $token) {
           throw new CustomUserMessageAuthenticationException('Invalid API token');
       }
       if ($token->isExpired()){
           throw new CustomUserMessageAuthenticationException('Token expired');
       }
       return $token->getUser();
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
       return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse([
           'messsage' => $exception->getMessageKey()
        ], 401);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // allow the request to continue
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
       throw new \Exception('Not used : entry_point from other authenticator');
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
