<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  OAuth2Call.php
 *
 * Gitlab OAUTH API (authentication)
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
  switch ($oauth2Name) {
    case 'gitlab':
      if( isset($_SERVER['HTTPS']) ) {
        $cfg['redirect_uri'] = str_replace('http://'
                                           ,'https://' 
                                           ,$cfg['redirect_uri']);  
      }  
      $providerCfg = ['clientId' => $cfg['oauth_client_id'],
                      'clientSecret' => $cfg['oauth_client_secret'],
                      'redirectUri' => $cfg['redirect_uri'] ]; 

      $provider = new Omines\OAuth2\Client\Provider\Gitlab($providerCfg);
      $authUrl = $provider->getAuthorizationUrl();

      // We are setting this to be able to check given state 
      // against previously stored one (this one!!) 
      // to mitigate CSRF attack
      // This check will be done in method oauth_get_token()
      // 
      session_start();
      $_SESSION['oauth2state'] = $provider->getState();
      header('Location: '.$authUrl);
      exit;
    break;
  }
}
