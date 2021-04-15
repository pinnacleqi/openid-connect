<?php

declare(strict_types=1);

namespace Pinnacle\OpenidConnect\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Utils;
use InvalidArgumentException;
use Pinnacle\OpenidConnect\Dtos\ProviderDto;
use Pinnacle\OpenidConnect\Exceptions\OAuthFailedException;
use stdClass;

class RequestAccessToken
{
    public static function execute(
        ProviderDto $providerDto,
        Uri $redirectUri,
        string $authorizationCode,
        ?string $codeVerifier = null
    ): string {
        $response = self::requestTokens($providerDto, $redirectUri, $authorizationCode, $codeVerifier);

        return self::accessTokenFromJsonResponse($response);
    }

    /**
     * @param Uri $redirectUri Must be the same as the initial redirect uri
     */
    private static function requestTokens(
        ProviderDto $provider,
        Uri $redirectUri,
        string $authorizationCode,
        ?string $codeVerifier = null
    ): stdClass {
        try {
            $client = new Client();

            $formParams = [
                'grant_type'   => 'authorization_code',
                'client_id'    => $provider->getClientId(),
                'redirect_uri' => (string)$redirectUri,
                'code'         => $authorizationCode,
            ];

            if ($codeVerifier !== null) {
                $formParams['code_verifier'] = $codeVerifier;
            }

            $request = $client
                ->request(
                    'POST',
                    $provider->getTokenEndpoint(),
                    [
                        // Authenticate with TOKEN endpoint using client ID and secret
                        RequestOptions::AUTH        => [
                            $provider->getClientId(),
                            $provider->getClientSecret(),
                        ],
                        RequestOptions::FORM_PARAMS => $formParams,
                    ]
                );
        } catch (GuzzleException $exception) {
            throw new OAuthFailedException('Unable to retrieve OAuth tokens from IdP endpoint.', 0, $exception);
        }

        try {
            $jsonObject = Utils::jsonDecode($request->getBody()->getContents());
            assert($jsonObject instanceof stdClass);

            return $jsonObject;
        } catch (InvalidArgumentException $exception) {
            throw new OAuthFailedException('Unable to parse JSON response from TOKENS endpoint.', 0, $exception);
        }
    }

    /**
     * @throws OAuthFailedException
     */
    private static function accessTokenFromJsonResponse(stdClass $jsonResponse): string
    {
        if (!isset($jsonResponse->access_token) || !is_string($jsonResponse->access_token)) {
            throw new OAuthFailedException('access_token not found in JSON response.');
        }

        return $jsonResponse->access_token;
    }
}