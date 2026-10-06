<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource  oidc.php
 *
 * Generic OpenID Connect provider (Keycloak, Okta, Auth0, Authentik,
 * Azure AD v2, any spec-compliant IdP).
 *
 * Endpoints can be configured explicitly (oauth_url, token_url,
 * oauth_profile) or resolved automatically from the issuer's
 * .well-known/openid-configuration document via 'discovery_url'.
 *
 * User identity is taken from the userinfo endpoint response —
 * fetched server-to-server over TLS with the access token — so no
 * local JWT signature verification is required.
 *
 * @see cfg/oauth_samples/oauth.oidc.inc.php for configuration
 */

/**
 * Fill oauth_url/token_url/oauth_profile from the OIDC discovery
 * document when 'discovery_url' is configured.
 *
 * @param array $authCfg provider configuration
 * @return array configuration with endpoint keys populated
 */
function oidc_resolve_endpoints($authCfg)
{
  if (empty($authCfg['discovery_url'])) {
    return $authCfg;
  }

  $ch = curl_init($authCfg['discovery_url']);
  curl_setopt_array($ch, array(
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_TIMEOUT => 10,
  ));
  $doc = json_decode((string)curl_exec($ch), true);
  curl_close($ch);

  if (is_array($doc)) {
    $map = array('authorization_endpoint' => 'oauth_url',
                 'token_endpoint' => 'token_url',
                 'userinfo_endpoint' => 'oauth_profile');
    foreach ($map as $docKey => $cfgKey) {
      if (empty($authCfg[$cfgKey]) && isset($doc[$docKey])) {
        $authCfg[$cfgKey] = $doc[$docKey];
      }
    }
  }
  return $authCfg;
}

/**
 * Exchange the authorization code for tokens and resolve the user's
 * identity through the userinfo endpoint.
 *
 * @param array  $authCfg provider configuration
 * @param string $code authorization code sent back by the IdP
 * @return stdClass ->status array(status,msg), ->options on success
 */
function oauth_get_token($authCfg, $code)
{
  $result = new stdClass();
  $result->status = array('status' => tl::OK, 'msg' => null);

  $authCfg = oidc_resolve_endpoints($authCfg);
  if (empty($authCfg['token_url']) || empty($authCfg['oauth_profile'])) {
    $result->status['msg'] = 'TestLink OIDC - token/userinfo endpoints ' .
      'not configured and discovery failed';
    $result->status['status'] = tl::ERROR;
    return $result;
  }

  $redirectUri = trim($authCfg['redirect_uri']);
  if (isset($_SERVER['HTTPS'])) {
    $redirectUri = str_replace('http://', 'https://', $redirectUri);
  }

  $tokenParams = array(
    'code' => $code,
    'grant_type' => 'authorization_code',
    'client_id' => $authCfg['oauth_client_id'],
    'client_secret' => $authCfg['oauth_client_secret'],
    'redirect_uri' => $redirectUri,
  );

  $ch = curl_init($authCfg['token_url']);
  curl_setopt_array($ch, array(
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($tokenParams),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_TIMEOUT => 10,
  ));
  $tokenInfo = json_decode((string)curl_exec($ch), true);
  curl_close($ch);

  if (!isset($tokenInfo['access_token'])) {
    $detail = isset($tokenInfo['error']) ?
      $tokenInfo['error'] . ' ' . ($tokenInfo['error_description'] ?? '') : 'no access_token';
    $result->status['msg'] = 'TestLink OIDC - token exchange failed: ' . $detail;
    $result->status['status'] = tl::ERROR;
    return $result;
  }

  // identity comes from the userinfo endpoint (TLS, server-to-server)
  $ch = curl_init($authCfg['oauth_profile']);
  curl_setopt_array($ch, array(
    CURLOPT_HTTPHEADER => array('Authorization: Bearer ' . $tokenInfo['access_token']),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_TIMEOUT => 10,
  ));
  $userInfo = json_decode((string)curl_exec($ch), true);
  curl_close($ch);

  $login = $userInfo['email'] ?? $userInfo['preferred_username'] ?? null;
  if (null == $login) {
    $result->status['msg'] = 'TestLink OIDC - userinfo carries neither ' .
      'email nor preferred_username claim';
    $result->status['status'] = tl::ERROR;
    return $result;
  }

  if (!empty($authCfg['oauth_domain']) && strpos($login, '@') !== false) {
    $domain = substr(strrchr($login, '@'), 1);
    if ($domain !== $authCfg['oauth_domain']) {
      $result->status['msg'] = "TestLink OIDC policy - user domain " .
        "'$domain' does not match configured oauth_domain " .
        "'{$authCfg['oauth_domain']}'";
      $result->status['status'] = tl::ERROR;
      return $result;
    }
  }

  $options = new stdClass();
  $options->givenName = $userInfo['given_name'] ?? $login;
  $options->familyName = $userInfo['family_name'] ?? '';
  $options->user = $login;
  $options->auth = 'oauth';

  $result->options = $options;
  return $result;
}
