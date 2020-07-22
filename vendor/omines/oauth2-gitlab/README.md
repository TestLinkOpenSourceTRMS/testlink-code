# GitLab Provider for OAuth 2.0 Client
[![Latest Version](https://img.shields.io/github/release/omines/oauth2-gitlab.svg?style=flat-square)](https://github.com/omines/oauth2-gitlab/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/omines/oauth2-gitlab/master.svg?style=flat-square)](https://travis-ci.org/omines/oauth2-gitlab)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/omines/oauth2-gitlab.svg?style=flat-square)](https://scrutinizer-ci.com/g/omines/oauth2-gitlab/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/omines/oauth2-gitlab.svg?style=flat-square)](https://scrutinizer-ci.com/g/omines/oauth2-gitlab)
[![Total Downloads](https://img.shields.io/packagist/dt/omines/oauth2-gitlab.svg?style=flat-square)](https://packagist.org/packages/omines/oauth2-gitlab)

This package provides GitLab OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

GitLab 8.17 or later is required as the V4 API is being used. If compatibility with older versions
of GitLab is required use version 2 of this library.

## Installation

To install, use composer:

```
composer require omines/oauth2-gitlab
```

## Usage

Usage is similar to the basic OAuth client, using `\Omines\OAuth2\Client\Provider\Gitlab` as the provider.

### Authorization Code Flow

```php
$provider = new Omines\OAuth2\Client\Provider\Gitlab([
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
$client = new \Gitlab\Client('https://my.gitlab.url/api/v4/');
$client->authenticate($token->getToken(), \Gitlab\Client::AUTH_OAUTH_TOKEN);
```
Or call the `getApiClient` method on `GitlabResourceOwner` which does the same implicitly.

## Testing

```bash
$ ./vendor/bin/phpunit
```

## Contributing

Please see [CONTRIBUTING](https://github.com/omines/oauth2-gitlab/blob/master/CONTRIBUTING.md) for details.


## Credits

This code is a modified fork from the [official Github provider](https://github.com/thephpleague/oauth2-github) adapted
for Gitlab use, so many credits go to [Steven Maguire](https://github.com/stevenmaguire).

## Legal

This software was developed for internal use at [Omines Full Service Internetbureau](https://www.omines.nl/)
in Eindhoven, the Netherlands. It is shared with the general public under the permissive MIT license, without
any guarantee of fitness for any particular purpose. Refer to the included `LICENSE` file for more details.
