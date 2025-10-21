<?php

/*
 * Gitlab OAuth2 Provider
 * (c) Omines Internetbureau B.V. - https://omines.nl/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\OAuth2\Client\Provider\Exception;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\ResponseInterface;

/**
 * GitlabIdentityProviderException.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
final class GitlabIdentityProviderException extends IdentityProviderException
{
    /**
     * Creates identity exception from response.
     *
     * @param ResponseInterface $response Response received from upstream
     * @param ?string $message Parsed message
     */
    public static function fromResponse(ResponseInterface $response, string $message = null): IdentityProviderException
    {
        return new self($message ?? $response->getReasonPhrase() ?: self::class, $response->getStatusCode(), $response->getBody()->getContents());
    }
}
