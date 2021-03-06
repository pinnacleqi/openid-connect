<?php

namespace Pinnacle\OpenIdConnect\Authorization\Constants;

use MyCLabs\Enum\Enum;

/**
 * Response error codes as described in @see https://openid.net/specs/openid-connect-core-1_0.html#AuthError
 *
 * @method static AuthenticationErrorCode INVALID_REQUEST
 * @method static AuthenticationErrorCode INTERACTION_REQUIRED
 * @method static AuthenticationErrorCode LOGIN_REQUIRED
 * @method static AuthenticationErrorCode ACCOUNT_SELECTION_REQUIRED
 * @method static AuthenticationErrorCode CONSENT_REQUIRED
 * @method static AuthenticationErrorCode INVALID_REQUEST_URI
 * @method static AuthenticationErrorCode INVALID_REQUEST_OBJECT
 * @method static AuthenticationErrorCode REQUEST_NOT_SUPPORTED
 * @method static AuthenticationErrorCode REQUEST_URI_NOT_SUPPORTED
 * @method static AuthenticationErrorCode REGISTRATION_NOT_SUPPORTED
 */
class AuthenticationErrorCode extends Enum
{
    protected const INVALID_REQUEST            = 'invalid_request';

    protected const INTERACTION_REQUIRED       = 'interaction_required';

    protected const LOGIN_REQUIRED             = 'login_required';

    protected const ACCOUNT_SELECTION_REQUIRED = 'account_selection_required';

    protected const CONSENT_REQUIRED           = 'consent_required';

    protected const INVALID_REQUEST_URI        = 'invalid_request_uri';

    protected const INVALID_REQUEST_OBJECT     = 'invalid_request_object';

    protected const REQUEST_NOT_SUPPORTED      = 'request_not_supported';

    protected const REQUEST_URI_NOT_SUPPORTED  = 'request_uri_not_supported';

    protected const REGISTRATION_NOT_SUPPORTED = 'registration_not_supported';

    public static function getDescription(string $errorCodeValue): string
    {
        return match ($errorCodeValue) {
            self::INVALID_REQUEST => 'The request is missing a required parameter, includes an invalid parameter value, includes a parameter more than once, or is otherwise malformed.',
            self::INTERACTION_REQUIRED => 'The Authorization Server requires End-User interaction of some form to proceed. This error MAY be returned when the prompt parameter value in the Authentication Request is none, but the Authentication Request cannot be completed without displaying a user interface for End-User interaction.',
            self::LOGIN_REQUIRED => 'The Authorization Server requires End-User authentication. This error MAY be returned when the prompt parameter value in the Authentication Request is none, but the Authentication Request cannot be completed without displaying a user interface for End-User authentication.',
            self::ACCOUNT_SELECTION_REQUIRED => 'The End-User is REQUIRED to select a session at the Authorization Server. The End-User MAY be authenticated at the Authorization Server with different associated accounts, but the End-User did not select a session. This error MAY be returned when the prompt parameter value in the Authentication Request is none, but the Authentication Request cannot be completed without displaying a user interface to prompt for a session to use.',
            self::CONSENT_REQUIRED => 'The Authorization Server requires End-User consent. This error MAY be returned when the prompt parameter value in the Authentication Request is none, but the Authentication Request cannot be completed without displaying a user interface for End-User consent.',
            self::INVALID_REQUEST_URI => 'The request_uri in the Authorization Request returns an error or contains invalid data.',
            self::INVALID_REQUEST_OBJECT => 'The request parameter contains an invalid Request Object.',
            self::REQUEST_NOT_SUPPORTED => 'The OP does not support use of the request parameter.',
            self::REQUEST_URI_NOT_SUPPORTED => 'The OP does not support use of the request_uri parameter.',
            self::REGISTRATION_NOT_SUPPORTED => 'The OP does not support use of the registration parameter.',
            default => sprintf('An unknown error code %s was sent with the authentication request.', $errorCodeValue),
        };
    }
}
