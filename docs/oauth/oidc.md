# How to configurate oauth work with OIDC

## About Dex
Dex is an identity service that uses OpenID Connect to drive authentication for other apps.

https://github.com/dexidp/dex

## Configuration
config.inc.php example:

```
// OIDC
$tlCfg->OAuthServers[1]['oauth_enabled'] = true;
$tlCfg->OAuthServers[1]['oauth_name'] = 'oidc';
$tlCfg->OAuthServers[1]['oauth_client_id'] = 'CLIENT_ID';
$tlCfg->OAuthServers[1]['oauth_client_secret'] = 'CLIENT_SECRET';
$tlCfg->OAuthServers[1]['oauth_grant_type'] = 'authorization_code';
$tlCfg->OAuthServers[1]['oauth_url'] = 'OAUTH_URL';
$tlCfg->OAuthServers[1]['token_url'] = 'TOKEN_URL';
$tlCfg->OAuthServers[1]['redirect_uri'] = 'redirect_uri';
$tlCfg->OAuthServers[1]['oauth_scope'] = 'openid profile email groups ext offline_access';
$tlCfg->OAuthServers[1]['https'] = $_SERVER['HTTPS'];
```

oauth_enabled: enable this oauth configuration.

oauth_name: "oidc".

oauth_client_id: id of OAuth program

oauth_client_secret: secret code.

oauth_grant_type: authorization_code is default value.

oauth_url: url of OAuth server.

token_url: url for getting token.

redirect_uri: callback uri.

oauth_scope: openid profile email groups ext offline_access