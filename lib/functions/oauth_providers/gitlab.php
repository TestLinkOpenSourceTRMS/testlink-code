<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  gitlab.php
 *
 * Gitlab OAUTH API (authentication)
 *
 *
 */
//$where = explode('lib',__DIR__);
//require($where[0] . '/config.inc.php');
require('autoload.php');

/**
 *
 */
function oauth_get_token($authCfg, $code) 
{
  $result = new stdClass();
  $result->status = array('status' => tl::OK, 'msg' => null);

  //$oauthParams['redirect_uri'] = $authCfg['redirect_uri']; 
  $oauthParams['redirect_uri'] = 'http://fman.hopto.org/login.php?oauth=gitlab';
  if( isset($_SERVER['HTTPS']) ) {
    $oauthParams['redirect_uri'] = 
      str_replace('http://', 'https://', 
                  $oauthParams['redirect_uri']);  
  }  


  $providerCfg = ['clientId' => $authCfg['oauth_client_id'],
                  'clientSecret' => $authCfg['oauth_client_secret'],
                  'redirectUri' => $oauthParams['redirect_uri'] ]; 

  $provider = new Omines\OAuth2\Client\Provider\Gitlab($providerCfg);

  
      // Try to get an access token (using the authorization code grant)
      $token = $provider->getAccessToken('authorization_code', [
          'code' => $_GET['code'],
      ]);

      // Now you have a token you can look up a users profile data
      try {
          // We got an access token, let's now get the user's details
          $user = $provider->getResourceOwner($token);


          // printf('<br>getName %s!', $user->getName());
          // printf('<br>getEmail %s!', $user->getEmail());
          // printf('<br>getUserName %s!', $user->getUserName());

          $firstLast = $user->getName();
          $result->options = new stdClass();
          $result->options->givenName = $firstLast;
          $result->options->familyName = $firstLast;
          $result->options->user = $user->getEmail();
          $result->options->email = $user->getEmail();
          $result->options->login = $user->getUserName();
          $result->options->auth = 'oauth';
          return $result;

      } catch (Exception $e) {
          // Failed to get user details
          exit('Oh dear...');
      }
}