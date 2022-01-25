<?php

declare(strict_types=1);

namespace Pinnacle\OpenIdConnect\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Utils;
use InvalidArgumentException;
use Pinnacle\OpenIdConnect\Models\Provider;
use Pinnacle\OpenIdConnect\Models\UserInfo;
use Pinnacle\OpenIdConnect\Exceptions\OpenIdConnectException;
use Psr\Log\LoggerInterface;
use stdClass;

class RequestUserInfo
{
    /**
     * @throws OpenIdConnectException
     */
    public static function execute(
        Provider         $provider,
        string           $accessToken,
        ?LoggerInterface $logger = null
    ): UserInfo {
        $jsonResponse = self::requestUserInfo($provider, $accessToken, $logger);

        return UserInfo::createWithJson($jsonResponse);
    }

    /**
     * @throws OpenIdConnectException
     */
    private static function requestUserInfo(
        Provider         $provider,
        string           $accessToken,
        ?LoggerInterface $logger = null
    ): stdClass {
        try {
            $client = new Client();

            $logger?->debug(sprintf('OIDC: Sending GET to %s.', $provider->getUserInfoEndpoint()));

            $request = $client
                ->request(
                    'GET',
                    $provider->getUserInfoEndpoint(),
                    [
                        RequestOptions::HEADERS => [
                            'Authorization' => 'Bearer ' . $accessToken,
                        ],
                        RequestOptions::TIMEOUT => 15, // in seconds
                    ]
                );
        } catch (GuzzleException $exception) {
            throw new OpenIdConnectException('Unable to retrieve UserInfo from USERINFO endpoint.', 0, $exception);
        }

        try {
            $response = $request->getBody()->getContents();

            $logger?->debug(sprintf('OIDC: Received OAuth USERINFO endpoint response: %s.', $response));

            $jsonObject = Utils::jsonDecode($response);
            assert($jsonObject instanceof stdClass);

            return $jsonObject;
        } catch (InvalidArgumentException $exception) {
            throw new OpenIdConnectException('Unable to parse JSON response from USERINFO endpoint.', 0, $exception);
        }
    }
}
