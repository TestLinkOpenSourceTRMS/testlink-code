# GitLab Provider for OAuth 2.0 Client
[![Latest Version](https://img.shields.io/github/release/omines/oauth2-gitlab.svg?style=flat-square)](https://github.com/omines/oauth2-gitlab/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/omines/oauth2-gitlab.svg?style=flat-square)](https://packagist.org/packages/omines/oauth2-gitlab)
[![test suite](https://github.com/omines/oauth2-gitlab/actions/workflows/ci.yaml/badge.svg)](https://github.com/omines/oauth2-gitlab/actions/workflows/ci.yaml)
[![codecov](https://codecov.io/gh/omines/oauth2-gitlab/graph/badge.svg?token=sAqu9IFaYQ)](https://codecov.io/gh/omines/oauth2-gitlab)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fomines%2Foauth2-gitlab%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/omines/oauth2-gitlab/master)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

This package provides GitLab OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Installation

To install, use composer:

```
composer require omines/oauth2-gitlab
```

## Usage

Usage is similar to the basic OAuth client, using `\Omines\OAuth2\Client\Provider\Gitlab` as the provider.

### Authorization Code Flow

```php
$provider = new \Omines\OAuth2\Client\Provider\Gitlab([
    'clientId'          => '{gitlab-client-id}',
    'clientSecret'      => '{gitlab-client-secret}',
    'redirectUri'       => 'https://example.com/callback-url',
    'domain'            => 'https://my.gitlab.example',      // Optional base URL for self-hosted
]);

if (!isset($_GET['code'])) {

    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: '.$authUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    exit('Invalid state');

} else {

    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code'],
    ]);

    // Optional: Now you have a token you can look up a users profile data
    try {

        // We got an access token, let's now get the user's details
        $user = $provider->getResourceOwner($token);

        // Use these details to create a new profile
        printf('Hello %s!', $user->getName());

    } catch (Exception $e) {

        // Failed to get user details
        exit('Oh dear...');
    }

    // Use this to interact with an API on the users behalf
    echo $token->getToken();
}
```

### Managing Scopes

When creating your GitLab authorization URL, you can specify the state and scopes your application may authorize.

```php
$options = [
    'state' => 'OPTIONAL_CUSTOM_CONFIGURED_STATE',
    'scope' => ['read_user','openid'] // array or string
];

$authorizationUrl = $provider->getAuthorizationUrl($options);
```
If neither are defined, the provider will utilize internal defaults ```'api'```.


### Performing API calls

Install [`m4tthumphrey/php-gitlab-api`](https://packagist.org/packages/m4tthumphrey/php-gitlab-api) to interact with the
Gitlab API after authentication. Either connect manually:

```php
$client = new \Gitlab\Client();
$client->setUrl('https://my.gitlab.url/api/v4/');
$client->authenticate($token->getToken(), \Gitlab\Client::AUTH_OAUTH_TOKEN);
```
Or call the `getApiClient` method on `GitlabResourceOwner` which does the same implicitly.

## Contributing

Please see [CONTRIBUTING](https://github.com/omines/oauth2-gitlab/blob/master/CONTRIBUTING.md) for details.

## Credits

This code is a modified fork from the [official Github provider](https://github.com/thephpleague/oauth2-github) adapted
for Gitlab use, so many credits go to [Steven Maguire](https://github.com/stevenmaguire).

## Legal

This software was developed for internal use at [Omines Full Service Internetbureau](https://www.omines.nl/)
in Eindhoven, the Netherlands. It is shared with the general public under the permissive MIT license, without
any guarantee of fitness for any particular purpose. Refer to the included `LICENSE` file for more details.
