<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  OAuth2Call.php
 *
 *
 */
$where = explode('lib',__DIR__);
require($where[0] . '/config.inc.php');
require_once('common.php');
require_once('oauth_api.php');
require('autoload.php');

$oauth2Name = trim($_GET['oauth2']);

// validate getting the config
$cfg = getOAuthProviderCfg($oauth2Name);
if ($cfg == null) {
  throw new Exception("Error Processing Request", 1);
}

// Go ahead
if (!isset($_GET['code'])) {

  if (isset($_SERVER['HTTPS'])) {
    $cfg['redirect_uri'] = str_replace('http://'
                                       ,'https://' 
                                       ,$cfg['redirect_uri']);  
  }  

  $clientType = 'ThePHPLeague';
  switch ($oauth2Name) {
    case 'gitlab':
    case 'github':
    case 'google':
      $providerCfg = ['clientId' => $cfg['oauth_client_id'],
                      'clientSecret' => $cfg['oauth_client_secret'],
                      'redirectUri' => $cfg['redirect_uri'] ]; 
    break;
  }
  
  session_start();
  switch ($oauth2Name) {
    case 'gitlab':
      $provider = new Omines\OAuth2\Client\Provider\Gitlab($providerCfg);
      $urlOpt = [];
    break;

    case 'github':
      $provider = new \League\OAuth2\Client\Provider\Github($providerCfg);
      $urlOpt = ['scope' => ['user','user:email','public_profile']];
    break;

    case 'google':
      $provider = new League\OAuth2\Client\Provider\Google($providerCfg);
      $urlOpt = [];
    break;

    case 'microsoft':
    case 'azuread';
      $clientType = 'testLink';
      $_SESSION['oauth2state'] = $oauth2Name . '$$$' . 
                                 bin2hex(random_bytes(32));

      // see https://docs.microsoft.com/en-us/azure/
      //             active-directory/develop/v1-protocols-oauth-code 
      // for details
      $oap = [];
      $oap['state'] = $_SESSION['oauth2state'];
      $oap['redirect_uri'] = $cfg['redirect_uri'];
      $oap['client_id'] = $cfg['oauth_client_id'];
      $oap['scope'] = $cfg['oauth_scope'];

      $oap['response_type'] = 'code';

      if ($oauth2Name == 'azuread') {
        if (!is_null($oauthCfg['oauth_domain'])) {
          $oap['domain_hint'] = $oauthCfg['oauth_domain'];
        }
      } else {
        if ($oauthCfg['oauth_force_single']) {
          $oap['prompt'] = 'consent';
        }
      }

      // http_build_query — Generate URL-encoded query string
      $authUrl = $cfg['oauth_url'] . '?' . http_build_query($oap);
    break;
  
    default:
      $clientType = 'testLink';
    break;
  }

  
  switch ($clientType) {
    case 'ThePHPLeague':
      // Give a look to
      // https://github.com/omines/oauth2-gitlab#managing-scopes
      // 
      $authUrl = $provider->getAuthorizationUrl($urlOpt);

      // We are setting this to be able to check given state 
      // against previously stored one (this one!!) 
      // to mitigate CSRF attack
      // This check will be done in method oauth_get_token()
      // 
      $_SESSION['oauth2state'] = $provider->getState();
      header('Location: ' . $authUrl);
      exit;
    break;

    default:
      header('Location: ' . $authUrl);
    break;
  }  
}