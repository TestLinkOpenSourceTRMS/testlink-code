<?php

/*
 * Gitlab OAuth2 Provider
 * (c) Omines Internetbureau B.V. - https://omines.nl/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\OAuth2\Client\Test\Provider;

use GuzzleHttp\ClientInterface;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Mockery as m;
use Omines\OAuth2\Client\Provider\Gitlab;
use Omines\OAuth2\Client\Provider\GitlabResourceOwner;
use PHPUnit\Framework\TestCase;

class GitlabTest extends TestCase
{
    /** @var Gitlab */
    protected $provider;

    protected function setUp(): void
    {
        $this->provider = new \Omines\OAuth2\Client\Provider\Gitlab([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ]);
    }

    public function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function testShorthandedSelfhostedConstructor()
    {
        $provider = new \Omines\OAuth2\Client\Provider\Gitlab([
            'domain' => 'https://gitlab.example.org',
        ]);
        $this->assertSame('https://gitlab.example.org', $provider->domain);
    }

    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('approval_prompt', $query);
        $this->assertNotNull($this->provider->getState());
    }

    public function testScopes()
    {
        $options = ['scope' => [uniqid(), uniqid()]];

        $url = $this->provider->getAuthorizationUrl($options);

        $this->assertStringContainsString(rawurlencode(implode(Gitlab::SCOPE_SEPARATOR, $options['scope'])), $url);
    }

    public function testGetAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);

        $this->assertEquals('/oauth/authorize', $uri['path']);
    }

    public function testGetBaseAccessTokenUrl()
    {
        $params = [];

        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);

        $this->assertEquals('/oauth/token', $uri['path']);
    }

    public function testGetAccessToken()
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->andReturn('{"access_token":"mock_access_token", "scope":"repo,gist", "token_type":"bearer"}');
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $response->shouldReceive('getStatusCode')->andReturn(200);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertNull($token->getExpires());
        $this->assertNull($token->getRefreshToken());
        $this->assertNull($token->getResourceOwnerId());
    }

    public function testSelfHostedGitlabDomainUrls()
    {
        $this->provider->domain = 'https://gitlab.company.com';

        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->times(1)->andReturn('access_token=mock_access_token&expires=3600&refresh_token=mock_refresh_token&otherKey={1234}');
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'application/x-www-form-urlencoded']);
        $response->shouldReceive('getStatusCode')->andReturn(200);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals($this->provider->domain . '/oauth/authorize', $this->provider->getBaseAuthorizationUrl());
        $this->assertEquals($this->provider->domain . '/oauth/token', $this->provider->getBaseAccessTokenUrl([]));
        $this->assertEquals($this->provider->domain . '/api/v4/user', $this->provider->getResourceOwnerDetailsUrl($token));
        //$this->assertEquals($this->provider->domain.'/api/v4/user/emails', $this->provider->urlUserEmails($token));
    }

    public function testUserData()
    {
        $userdata = [
            'id' => rand(1000, 9999),
            'name' => uniqid('name'),
            'username' => uniqid('username'),
            'email' => uniqid('email'),
            'avatar_url' => 'https://example.org/' . uniqid('avatar'),
            'web_url' => 'https://example.org/' . uniqid('web'),
            'state' => 'active',
            'is_admin' => true,
            'external' => true,
        ];

        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn('access_token=mock_access_token&expires=3600&refresh_token=mock_refresh_token&otherKey={1234}');
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'application/x-www-form-urlencoded']);
        $postResponse->shouldReceive('getStatusCode')->andReturn(200);

        $userResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $userResponse->shouldReceive('getBody')->andReturn(json_encode($userdata));
        $userResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $userResponse->shouldReceive('getStatusCode')->andReturn(200);

        /** @var ClientInterface $client */
        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(2)
            ->andReturn($postResponse, $userResponse);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $user = $this->provider->getResourceOwner($token);

        /* @var GitlabResourceOwner $user */
        $this->assertSame($userdata, $user->toArray());
        $this->assertEquals($userdata['id'], $user->getId());
        $this->assertEquals($userdata['name'], $user->getName());
        $this->assertEquals($userdata['username'], $user->getUsername());
        $this->assertEquals($userdata['email'], $user->getEmail());
        $this->assertEquals($userdata['avatar_url'], $user->getAvatarUrl());
        $this->assertEquals($userdata['web_url'], $user->getProfileUrl());
        $this->assertEquals('https://gitlab.com', $user->getDomain());
        $this->assertEquals('mock_access_token', $user->getToken()->getToken());
        $this->assertTrue($user->isActive());
        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->isExternal());

        return $user;
    }

    /**
     * @depends testUserData
     */
    public function testApiClient(GitlabResourceOwner $owner)
    {
        $client = $owner->getApiClient();
        $this->assertInstanceOf(\Gitlab\Client::class, $client);
    }

    /* public function testUserEmails()
    {

        $userId = rand(1000,9999);
        $name = uniqid();
        $nickname = uniqid();
        $email = uniqid();

        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn('access_token=mock_access_token&expires=3600&refresh_token=mock_refresh_token&otherKey={1234}');
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'application/x-www-form-urlencoded']);

        $userResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $userResponse->shouldReceive('getBody')->andReturn('[{"email":"mock_email_1","primary":false,"verified":true},{"email":"mock_email_2","primary":false,"verified":true},{"email":"mock_email_3","primary":true,"verified":true}]');
        $userResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(2)
            ->andReturn($postResponse, $userResponse);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $emails = $this->provider->getUserEmails($token);

        $this->assertEquals($userId, $user->getUserId());
        $this->assertEquals($name, $user->getName());
        $this->assertEquals($nickname, $user->getNickname());
        $this->assertEquals($email, $user->getEmail());
        $this->assertContains($nickname, $user->getUrl());
    } */

    public function testExceptionThrownWhenErrorObjectReceived()
    {
        $status = rand(400, 600);
        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn('{"message": "Validation Failed","errors": [{"resource": "Issue","field": "title","code": "missing_field"}]}');
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $postResponse->shouldReceive('getStatusCode')->andReturn($status);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(1)
            ->andReturn($postResponse);
        $this->provider->setHttpClient($client);

        $this->expectException(IdentityProviderException::class);
        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    public function testExceptionThrownWhenOAuthErrorReceived()
    {
        $status = 200;
        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn('{"error": "bad_verification_code","error_description": "The code passed is incorrect or expired.","error_uri": "https://developer.github.com/v4/oauth/#bad-verification-code"}');
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $postResponse->shouldReceive('getStatusCode')->andReturn($status);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(1)
            ->andReturn($postResponse);
        $this->provider->setHttpClient($client);

        $this->expectException(IdentityProviderException::class);
        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }
}
