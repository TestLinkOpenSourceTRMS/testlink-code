<?php

/*
 * Gitlab OAuth2 Provider
 * (c) Omines Internetbureau B.V. - https://omines.nl/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\OAuth2\Client\Test\Provider;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Mockery as m;
use Omines\OAuth2\Client\Provider\Gitlab;
use Omines\OAuth2\Client\Provider\GitlabResourceOwner;
use PHPUnit\Framework\TestCase;

class GitlabTest extends TestCase
{
    protected Gitlab $provider;

    protected function setUp(): void
    {
        $this->provider = new Gitlab([
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

    private function createSelfhostedProvider(string $domain): Gitlab
    {
        return new Gitlab([
            'domain' => $domain,
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ]);
    }

    public function testShorthandedSelfhostedConstructor(): void
    {
        $provider = $this->createSelfhostedProvider('https://gitlab.example.org');
        $this->assertSame('https://gitlab.example.org/oauth/authorize', $provider->getBaseAuthorizationUrl());
    }

    public function testAuthorizationUrl(): void
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'] ?? '', $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('approval_prompt', $query);
        $this->assertNotNull($this->provider->getState());
    }

    public function testScopes(): void
    {
        $options = ['scope' => [uniqid(), uniqid()]];
        $url = $this->provider->getAuthorizationUrl($options);
        $this->assertStringContainsString(rawurlencode(implode(Gitlab::SCOPE_SEPARATOR, $options['scope'])), $url);

        // Default scope
        $this->assertStringContainsString('&scope=api&', $this->provider->getAuthorizationUrl());
    }

    public function testGetAuthorizationUrl(): void
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);

        $this->assertEquals('/oauth/authorize', $uri['path'] ?? 'error on parsing');
    }

    public function testGetBaseAccessTokenUrl(): void
    {
        $params = [];

        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);

        $this->assertEquals('/oauth/token', $uri['path'] ?? 'error on parsing');
    }

    public function testGetAccessToken(): void
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->andReturn(Utils::streamFor('{"access_token":"mock_access_token", "scope":"repo,gist", "token_type":"bearer"}'));
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $response->shouldReceive('getStatusCode')->andReturn(200);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertInstanceOf(AccessToken::class, $token);
        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertNull($token->getExpires());
        $this->assertNull($token->getRefreshToken());
        $this->assertNull($token->getResourceOwnerId());
    }

    public function testSelfHostedGitlabDomainUrls(): void
    {
        $provider = $this->createSelfhostedProvider('https://gitlab.company.com');

        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->times(1)->andReturn(Utils::streamFor('access_token=mock_access_token&expires=3600&refresh_token=mock_refresh_token&otherKey={1234}'));
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'application/x-www-form-urlencoded']);
        $response->shouldReceive('getStatusCode')->andReturn(200);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $provider->setHttpClient($client);

        $token = $provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertInstanceOf(AccessToken::class, $token);
        $this->assertEquals($provider->domain . '/oauth/authorize', $provider->getBaseAuthorizationUrl());
        $this->assertEquals($provider->domain . '/oauth/token', $provider->getBaseAccessTokenUrl([]));
        $this->assertEquals($provider->domain . '/api/v4/user', $provider->getResourceOwnerDetailsUrl($token));
        // $this->assertEquals($provider->domain.'/api/v4/user/emails', $provider->urlUserEmails($token));
    }

    public function testUserData(): GitlabResourceOwner
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
            'external' => false,
        ];

        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn(Utils::streamFor('access_token=mock_access_token&expires=3600&refresh_token=mock_refresh_token&otherKey={1234}'));
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'application/x-www-form-urlencoded']);
        $postResponse->shouldReceive('getStatusCode')->andReturn(200);

        $userResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $userResponse->shouldReceive('getBody')->andReturn(Utils::streamFor(json_encode($userdata)));
        $userResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $userResponse->shouldReceive('getStatusCode')->andReturn(200);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(2)
            ->andReturn($postResponse, $userResponse);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $this->assertInstanceOf(AccessToken::class, $token);
        $user = $this->provider->getResourceOwner($token);
        $this->assertInstanceOf(GitlabResourceOwner::class, $user);

        $this->assertEquals($userdata, $user->toArray());
        $this->assertSame($userdata['id'], $user->getId());
        $this->assertSame($userdata['name'], $user->getName());
        $this->assertSame($userdata['username'], $user->getUsername());
        $this->assertSame($userdata['email'], $user->getEmail());
        $this->assertSame($userdata['avatar_url'], $user->getAvatarUrl());
        $this->assertSame($userdata['web_url'], $user->getProfileUrl());
        $this->assertSame('https://gitlab.com', $user->getDomain());
        $this->assertSame('mock_access_token', $user->getToken()->getToken());
        $this->assertTrue($user->isActive());
        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isExternal());

        return $user;
    }

    public function testBuggyResourceOwner(): void
    {
        /** @phpstan-ignore-next-line Violating type requirements on purpose */
        $owner = new GitlabResourceOwner([
            'id' => 'foo', // Should be an integer
            'is_admin' => 'bar', // Should be a bool
        ], new AccessToken([
            'access_token' => 'foobar',
        ]));

        $this->assertSame(0, $owner->getId());
        $this->assertTrue($owner->isAdmin());
    }

    public function testDefaultValuesForResourceOwner(): void
    {
        /** @phpstan-ignore-next-line Violating type requirements on purpose */
        $owner = new GitlabResourceOwner([
        ], new AccessToken([
            'access_token' => 'foobar',
        ]));

        $this->assertSame(0, $owner->getId());
        $this->assertFalse($owner->isAdmin());
        $this->assertFalse($owner->isActive());
        $this->assertTrue($owner->isExternal());
    }

    /**
     * @depends testUserData
     */
    public function testApiClient(GitlabResourceOwner $owner): void
    {
        $client = $owner->getApiClient();
        $this->assertInstanceOf(\Gitlab\Client::class, $client);
    }

    /**
     * @return int[][]
     */
    public static function provideErrorCodes(): array
    {
        return [
            [400],
            [404],
            [500],
            [rand(401, 600)],
        ];
    }

    /**
     * @dataProvider provideErrorCodes
     */
    public function testExceptionThrownWhenErrorObjectReceived(int $status): void
    {
        $response = new Response($status, ['content-type' => 'json'], '{"message": "Validation Failed","errors": [{"resource": "Issue","field": "title","code": "missing_field"}]}');

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(1)
            ->andReturn($response);
        $this->provider->setHttpClient($client);

        $this->expectException(IdentityProviderException::class);
        $this->expectExceptionMessage('Validation Failed');
        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    public function testExceptionThrownWhenOAuthErrorReceived(): void
    {
        $response = new Response(200, ['content-type' => 'json'], '{"error": "bad_verification_code","error_description": "The code passed is incorrect or expired.","error_uri": "https://developer.github.com/v4/oauth/#bad-verification-code"}');

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(1)
            ->andReturn($response);
        $this->provider->setHttpClient($client);

        $this->expectException(IdentityProviderException::class);
        $this->expectExceptionMessage('bad_verification_code');
        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    public function testExceptionThrownWhenUnknownErrorReceived(): void
    {
        $response = new Response(200, ['content-type' => 'json'], '684');

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(1)
            ->andReturn($response);
        $this->provider->setHttpClient($client);

        $this->expectException(IdentityProviderException::class);
        $this->expectExceptionMessage('Corrupted response');
        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }
}
