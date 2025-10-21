<?php

/*
 * Gitlab OAuth2 Provider
 * (c) Omines Internetbureau B.V. - https://omines.nl/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Omines\OAuth2\Client\Provider\Exception\GitlabIdentityProviderException;
use Psr\Http\Message\ResponseInterface;

/**
 * Gitlab.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 *
 * @phpstan-import-type ResourceOwner from GitlabResourceOwner
 */
class Gitlab extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public const DEFAULT_DOMAIN = 'https://gitlab.com';
    public const DEFAULT_SCOPE = 'api';
    public const SCOPE_SEPARATOR = ' ';

    private const PATH_API_USER = '/api/v4/user';
    private const PATH_AUTHORIZE = '/oauth/authorize';
    private const PATH_TOKEN = '/oauth/token';

    public string $domain = self::DEFAULT_DOMAIN;

    /**
     * Get authorization url to begin OAuth flow.
     */
    public function getBaseAuthorizationUrl(): string
    {
        return $this->domain . self::PATH_AUTHORIZE;
    }

    /**
     * Get access token url to retrieve token.
     *
     * @param mixed[] $params
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->domain . self::PATH_TOKEN;
    }

    /**
     * Get provider url to fetch user details.
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return $this->domain . self::PATH_API_USER;
    }

    /**
     * Get the default scopes used by GitLab.
     * Current scopes are 'api', 'read_user', 'openid'.
     *
     * This returns an array with 'api' scope as default.
     *
     * @return string[]
     */
    protected function getDefaultScopes(): array
    {
        return [self::DEFAULT_SCOPE];
    }

    /**
     * GitLab uses a space to separate scopes.
     */
    protected function getScopeSeparator(): string
    {
        return self::SCOPE_SEPARATOR;
    }

    /**
     * Check a provider response for errors.
     *
     * @param ResponseInterface $response Parsed response data
     * @param array{error?: string, message?: string}|mixed $data
     * @throws IdentityProviderException
     */
    protected function checkResponse(ResponseInterface $response, mixed $data): void
    {
        if (!is_array($data)) {
            throw GitlabIdentityProviderException::fromResponse($response, 'Corrupted response');
        } elseif ($response->getStatusCode() >= 400) {
            throw GitlabIdentityProviderException::fromResponse($response, $data['message'] ?? $response->getReasonPhrase());
        } elseif (isset($data['error'])) {
            throw GitlabIdentityProviderException::fromResponse($response, $data['error']);
        }
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param ResourceOwner $response
     */
    protected function createResourceOwner(array $response, AccessToken $token): ResourceOwnerInterface
    {
        $user = new GitlabResourceOwner($response, $token);

        return $user->setDomain($this->domain);
    }
}
