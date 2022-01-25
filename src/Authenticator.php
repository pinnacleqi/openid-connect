<?php

namespace Pinnacle\OpenIdConnect;

use GuzzleHttp\Psr7\Uri;
use Pinnacle\OpenIdConnect\Exceptions\AccessTokenNotFoundException;
use Pinnacle\OpenIdConnect\Exceptions\AuthorizationCodeCallbackException;
use Pinnacle\OpenIdConnect\Exceptions\InsecureUriException;
use Pinnacle\OpenIdConnect\Exceptions\ChallengeMismatchException;
use Pinnacle\OpenIdConnect\Exceptions\MissingRequiredQueryParametersException;
use Pinnacle\OpenIdConnect\Exceptions\OpenIdConnectException;
use Pinnacle\OpenIdConnect\Exceptions\StatePersisterMissingValueException;
use Pinnacle\OpenIdConnect\Models\AuthenticationTokensResponse;
use Pinnacle\OpenIdConnect\Models\AuthorizationCodeCallbackData;
use Pinnacle\OpenIdConnect\Models\AuthenticationUriBuilder;
use Pinnacle\OpenIdConnect\Models\AuthorizationCodeResponse;
use Pinnacle\OpenIdConnect\Models\Contracts\ProviderConfigurationInterface;
use Pinnacle\OpenIdConnect\Models\Contracts\StatePersisterInterface;
use Pinnacle\OpenIdConnect\Models\UserInfo;
use Pinnacle\OpenIdConnect\Services\TokenRequestor;
use Pinnacle\OpenIdConnect\Services\RequestUserInfo;
use Pinnacle\OpenIdConnect\Services\StatePersisterWrapper;
use Psr\Log\LoggerInterface;

class Authenticator
{
    public function __construct(
        private StatePersisterInterface $statePersister,
        private ?LoggerInterface        $logger = null
    ) {
    }

    /**
     * @throws InsecureUriException
     * @throws OpenIdConnectException
     */
    public function beginAuthentication(
        Uri                            $redirectUri,
        ProviderConfigurationInterface $provider,
    ): AuthenticationUriBuilder {
        if ($redirectUri->getScheme() !== 'https') {
            throw new InsecureUriException('Redirect URI must use https.');
        }

        $authenticationUriBuilder = new AuthenticationUriBuilder($provider, $redirectUri);

        $state     = $authenticationUriBuilder->getState();
        $challenge = $authenticationUriBuilder->getCodeChallenge();

        $statePersisterWrapper = new StatePersisterWrapper($this->statePersister, $state);

        $statePersisterWrapper->storeChallenge($challenge);
        $statePersisterWrapper->storeProvider($provider);
        $statePersisterWrapper->storeRedirectUri($redirectUri);

        return $authenticationUriBuilder;
    }

    /**
     * @throws MissingRequiredQueryParametersException
     * @throws AuthorizationCodeCallbackException
     * @throws ChallengeMismatchException
     * @throws StatePersisterMissingValueException
     */
    public function handleAuthorizationCodeCallback(Uri $callbackUri): AuthorizationCodeResponse
    {
        $callbackData = new AuthorizationCodeCallbackData($callbackUri);

        $responseState = $callbackData->getState();

        $statePersisterWrapper = new StatePersisterWrapper($this->statePersister, $responseState);

        $challenge   = $statePersisterWrapper->getChallenge();
        $provider    = $statePersisterWrapper->getProvider();
        $redirectUri = $statePersisterWrapper->getRedirectUri();

        if ($callbackData->getChallenge() !== $challenge) {
            throw new ChallengeMismatchException(
                sprintf(
                    'Response challenge %s does not match the original request %s.',
                    $callbackData->getChallenge(),
                    $challenge
                )
            );
        }

        return new AuthorizationCodeResponse(
            $callbackData->getAuthorizationCode(),
            $provider,
            $redirectUri,
            $challenge
        );
    }

    /**
     * @throws OpenIdConnectException
     * @throws AccessTokenNotFoundException
     */
    public function fetchTokensWithAuthorizationCode(
        AuthorizationCodeResponse $authorizationCodeResponse
    ): AuthenticationTokensResponse {
        $tokenRequestor = new TokenRequestor(
            $authorizationCodeResponse->getProvider(),
            $authorizationCodeResponse->getRedirectUri(),
            $authorizationCodeResponse->getChallenge(),
            $this->logger
        );

        $accessToken = $tokenRequestor->getAccessTokenForAuthorizationCode(
            $authorizationCodeResponse->getAuthorizationCode()
        );

        return new AuthenticationTokensResponse($accessToken, $authorizationCodeResponse->getProvider());
    }

    public function fetchUserInformationWithAccessToken(AuthenticationTokensResponse $authenticationTokensResponse): UserInfo
    {
        // TODO:: We will be replacing this call and instead be parsing the JWT.
        return RequestUserInfo::execute(
            $authenticationTokensResponse->getProvider(),
            $authenticationTokensResponse->getAccessToken(),
            $this->logger
        );
    }
}
