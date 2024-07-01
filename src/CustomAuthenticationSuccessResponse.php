<?php

namespace App;

use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationSuccessResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Symfony\Component\Security\Core\User\UserInterface;

class CustomAuthenticationSuccessResponse extends AuthenticationSuccessHandler
{
    public function handleAuthenticationSuccess(UserInterface $user, $jwt = null)
    {
        $response = parent::handleAuthenticationSuccess($user, $jwt);

        if (!$response instanceof JWTAuthenticationSuccessResponse) {
            return $response;
        }

        $decoded = json_decode($response->getContent(), true);

        return new JWTAuthenticationSuccessResponse(
            token: $decoded['token'],
            data: [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'password' => $user->getPassword(),
            ],
        );
    }
}
