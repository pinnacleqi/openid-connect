<?php

declare(strict_types=1);

namespace Pinnacle\OpenIdConnect\UserInfo\Models;

use Pinnacle\CommonValueObjects\EmailAddress;
use Pinnacle\OpenIdConnect\Support\Exceptions\OpenIdConnectException;
use stdClass;

class UserInfo
{
    private SubjectIdentifier $subjectIdentifier;

    private FullName          $fullName;

    private EmailAddress      $emailAddress;

    private bool              $emailAddressVerified;

    public function __construct(
        SubjectIdentifier $subjectIdentifier,
        FullName          $fullName,
        EmailAddress      $emailAddress,
        bool              $emailAddressVerified
    ) {
        $this->subjectIdentifier    = $subjectIdentifier;
        $this->fullName             = $fullName;
        $this->emailAddress         = $emailAddress;
        $this->emailAddressVerified = $emailAddressVerified;
    }

    /**
     * @throws OpenIdConnectException
     */
    public static function createWithJson(stdClass $json): self
    {
        if (!isset($json->sub) || !is_string($json->sub)) {
            throw new OpenIdConnectException('The subject identifier of the user was not found.');
        }
        if (!isset($json->name) || !is_string($json->name)) {
            throw new OpenIdConnectException('The name of the user was not found.');
        }
        if (!isset($json->email) || !is_string($json->email)) {
            throw new OpenIdConnectException('The email address of the user was not found.');
        }

        // The email_verified value is not always returned. Handle as a special case.
        if (isset($json->email_verified)) {
            // Sometimes it's passed as a string value, even though the spec says it should be a boolean.
            if (!is_string($json->email_verified) && !is_bool($json->email_verified)) {
                throw new OpenIdConnectException(
                    'The email verification value of the user was set, but is not a string or boolean value.'
                );
            }

            $emailVerified = (is_string($json->email_verified) && $json->email_verified === 'true') ||
                             $json->email_verified;
        } else {
            // No email_verified value found, set to false.
            $emailVerified = false;
        }

        return new self(
            new SubjectIdentifier($json->sub),
            new FullName($json->name),
            new EmailAddress($json->email),
            $emailVerified
        );
    }

    public function getSubjectIdentifier(): SubjectIdentifier
    {
        return $this->subjectIdentifier;
    }

    public function getFullName(): FullName
    {
        return $this->fullName;
    }

    public function getEmailAddress(): EmailAddress
    {
        return $this->emailAddress;
    }

    public function isEmailAddressVerified(): bool
    {
        return $this->emailAddressVerified;
    }
}