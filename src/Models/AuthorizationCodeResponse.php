<?php

namespace Pinnacle\OpenIdConnect\Models;

use GuzzleHttp\Psr7\Uri;

class AuthorizationCodeResponse
{
    public function __construct(
        private string   $authorizationCode,
        private Provider $provider,
        private Uri      $redirectUri,
        private string   $challenge
    ) {
    }

    public function getProvider(): Provider
    {
        return $this->provider;
    }

    public function getAuthorizationCode(): string
    {
        return $this->authorizationCode;
    }

    public function getRedirectUri(): Uri
    {
        return $this->redirectUri;
    }

    public function getChallenge(): string
    {
        return $this->challenge;
    }
}