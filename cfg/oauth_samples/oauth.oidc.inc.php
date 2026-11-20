<?php
//
// filesource oauth.oidc.inc.php
//
// Generic OpenID Connect provider — works with any spec-compliant
// IdP: Keycloak, Okta, Auth0, Authentik, Azure AD v2, ...
//
// HOW TO use this file ?
// 1. copy this file to
//     [TESTLINK_INSTALL]/cfg/
//
// 2. configure according to your IdP (see below)
//
// 3. add the following line to your custom_config.inc.php
//    require('oauth.oidc.inc.php');
//
// The user's TestLink login is taken from the 'email' claim
// (fallback: 'preferred_username'); first/last name from
// 'given_name'/'family_name'. Make sure your client has the
// openid, profile and email scopes granted.
// -------------------------------------------------------------

$tlCfg->OAuthServers['oidc'] = array();
$tlCfg->OAuthServers['oidc']['oauth_name'] = 'oidc'; // do not change this
$tlCfg->OAuthServers['oidc']['oauth_enabled'] = true;
$tlCfg->OAuthServers['oidc']['oauth_grant_type'] = 'authorization_code';

$tlCfg->OAuthServers['oidc']['redirect_uri'] =
  (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') .
  $_SERVER['HTTP_HOST'] . '/login.php';

$tlCfg->OAuthServers['oidc']['oauth_client_id'] = 'CHANGE_WITH_CLIENT_ID';
$tlCfg->OAuthServers['oidc']['oauth_client_secret'] = 'CHANGE_WITH_CLIENT_SECRET';

// EITHER: point at the discovery document and let TestLink resolve
// the endpoints automatically ...
//
// Keycloak: https://<host>/realms/<realm>/.well-known/openid-configuration
// Okta:     https://<org>.okta.com/.well-known/openid-configuration
// Auth0:    https://<tenant>.auth0.com/.well-known/openid-configuration
// Azure v2: https://login.microsoftonline.com/<tenant>/v2.0/.well-known/openid-configuration
$tlCfg->OAuthServers['oidc']['discovery_url'] =
  'https://CHANGE_WITH_IDP_HOST/.well-known/openid-configuration';

// ... OR configure the three endpoints explicitly (discovery wins
// only for keys left empty):
// $tlCfg->OAuthServers['oidc']['oauth_url'] = '';     // authorization_endpoint
// $tlCfg->OAuthServers['oidc']['token_url'] = '';     // token_endpoint
// $tlCfg->OAuthServers['oidc']['oauth_profile'] = ''; // userinfo_endpoint

$tlCfg->OAuthServers['oidc']['oauth_scope'] = 'openid profile email';

// optional: only allow users whose email is on this domain
// $tlCfg->OAuthServers['oidc']['oauth_domain'] = 'example.com';
