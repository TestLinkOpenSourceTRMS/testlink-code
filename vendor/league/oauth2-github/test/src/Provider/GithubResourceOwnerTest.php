<?php

namespace League\OAuth2\Client\Test\Provider;

use League\OAuth2\Client\Provider\GithubResourceOwner;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class GithubResourceOwnerTest extends TestCase
{
    public function testUrlIsNullWithoutDomainOrNickname(): void
    {
        $user = new GithubResourceOwner();

        $url = $user->getUrl();

        $this->assertNull($url);
    }

    public function testUrlIsDomainWithoutNickname(): void
    {
        $domain = uniqid();
        $user = new GithubResourceOwner();
        $user->setDomain($domain);

        $url = $user->getUrl();

        $this->assertEquals($domain, $url);
    }

    public function testUrlIsNicknameWithoutDomain(): void
    {
        $nickname = uniqid();
        $user = new GithubResourceOwner(['login' => $nickname]);

        $url = $user->getUrl();

        $this->assertEquals($nickname, $url);
    }
}
