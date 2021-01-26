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
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;

/**
 * GitlabResourceOwner.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class GitlabResourceOwner implements ResourceOwnerInterface
{
    const PATH_API = '/api/v4/';

    /** @var array */
    private $data;

    /** @var string */
    private $domain;

    /** @var AccessToken */
    private $token;

    /**
     * Creates new resource owner.
     */
    public function __construct(array $response, AccessToken $token)
    {
        $this->data = $response;
        $this->token = $token;
    }

    /**
     * Returns the identifier of the authorized resource owner.
     *
     * @return int
     */
    public function getId()
    {
        return (int) $this->get('id');
    }

    /**
     * Returns an authenticated API client.
     *
     * Requires optional Gitlab API client to be installed.
     *
     * @return Client
     */
    public function getApiClient()
    {
        if (!class_exists('\\Gitlab\\Client')) {
            throw new \LogicException(__METHOD__ . ' requires package m4tthumphrey/php-gitlab-api to be installed and autoloaded'); // @codeCoverageIgnore
        }
        $client = \Gitlab\Client::create(rtrim($this->domain, '/') . self::PATH_API);

        return $client->authenticate($this->token->getToken(), Client::AUTH_OAUTH_TOKEN);
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param  string $domain
     * @return $this
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * The full name of the owner.
     *
     * @return string
     */
    public function getName()
    {
        return $this->get('name');
    }

    /**
     * Username of the owner.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->get('username');
    }

    /**
     * Email address of the owner.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->get('email');
    }

    /**
     * URL to the user's avatar.
     *
     * @return string|null
     */
    public function getAvatarUrl()
    {
        return $this->get('avatar_url');
    }

    /**
     * URL to the user's profile page.
     *
     * @return string
     */
    public function getProfileUrl()
    {
        return $this->get('web_url');
    }

    /**
     * @return AccessToken
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Whether the user is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return 'active' === $this->get('state');
    }

    /**
     * Whether the user is an admin.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return (bool) $this->get('is_admin', false);
    }

    /**
     * Whether the user is external.
     *
     * @return bool
     */
    public function isExternal()
    {
        return (bool) $this->get('external', true);
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * @param  string     $key
     * @param  mixed|null $default
     * @return mixed|null
     */
    protected function get($key, $default = null)
    {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }
}
