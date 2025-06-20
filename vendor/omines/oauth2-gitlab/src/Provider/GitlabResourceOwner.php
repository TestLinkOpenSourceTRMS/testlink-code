<?php

/*
 * Gitlab OAuth2 Provider
 * (c) Omines Internetbureau B.V. - https://omines.nl/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\OAuth2\Client\Provider;

use Gitlab\Client;
use Gitlab\HttpClient\Builder;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;

/**
 * GitlabResourceOwner.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 *
 * @phpstan-type ResourceOwner array{id: int, is_admin: bool, name: string, username: string, email: string, avatar_url: string, web_url: string, state: string, external: bool}
 */
class GitlabResourceOwner implements ResourceOwnerInterface
{
    public const PATH_API = '/api/v4/';

    /** @var ResourceOwner */
    private array $data;

    private string $domain;
    private AccessToken $token;

    /**
     * Creates new resource owner.
     *
     * @param ResourceOwner $response
     */
    public function __construct(array $response, AccessToken $token)
    {
        $this->data = $response;
        $this->token = $token;
    }

    /**
     * Returns the identifier of the authorized resource owner.
     */
    public function getId(): int
    {
        return (int) ($this->data['id'] ?? 0);
    }

    /**
     * Returns an authenticated API client.
     *
     * Requires optional Gitlab API client to be installed.
     *
     * @infection-ignore-all Cannot be tested for infection due to external dependency
     */
    public function getApiClient(Builder $builder = null): Client
    {
        if (!class_exists('\\Gitlab\\Client')) {
            throw new \LogicException(__METHOD__ . ' requires package m4tthumphrey/php-gitlab-api to be installed and autoloaded'); // @codeCoverageIgnore
        }
        $client = new Client($builder);
        $client->setUrl(rtrim($this->domain, '/') . self::PATH_API);
        $client->authenticate($this->token->getToken(), Client::AUTH_OAUTH_TOKEN);

        return $client;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * The full name of the owner.
     */
    public function getName(): string
    {
        return $this->data['name'];
    }

    /**
     * Username of the owner.
     */
    public function getUsername(): string
    {
        return $this->data['username'];
    }

    /**
     * Email address of the owner.
     */
    public function getEmail(): string
    {
        return $this->data['email'];
    }

    /**
     * URL to the user's avatar.
     */
    public function getAvatarUrl(): ?string
    {
        return $this->data['avatar_url'];
    }

    /**
     * URL to the user's profile page.
     */
    public function getProfileUrl(): ?string
    {
        return $this->data['web_url'];
    }

    public function getToken(): AccessToken
    {
        return $this->token;
    }

    /**
     * Whether the user is active.
     */
    public function isActive(): bool
    {
        return 'active' === ($this->data['state'] ?? null);
    }

    /**
     * Whether the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->data['is_admin'] ?? false;
    }

    /**
     * Whether the user is external.
     */
    public function isExternal(): bool
    {
        return $this->data['external'] ?? true;
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return ResourceOwner
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
