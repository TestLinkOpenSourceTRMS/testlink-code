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

  $oauthParams['redirect_uri'] = $authCfg['redirect_uri'];
  if( isset($_SERVER['HTTPS']) ) {
    $oauthParams['redirect_uri'] = 
      str_replace('http://', 'https://', 
                  $oauthParams['redirect_uri']);  
  }  

  $providerCfg = ['clientId' => $authCfg['oauth_client_id'],
                  'clientSecret' => $authCfg['oauth_client_secret'],
                  'redirectUri' => $oauthParams['redirect_uri'] ]; 

  $provider = new Omines\OAuth2\Client\Provider\Gitlab($providerCfg);
  // echo '<br>state from SESSION: '. $_SESSION['oauth2state'];
  // echo '<br>state from GET: ' . $_GET['state'];

  // CRITICAL
  // Suggested in https://github.com/thephpleague/oauth2-client
  // 
  // Check given state ($_GET) against previously stored one 
  // ($_SESSION) to mitigate CSRF attack
  if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    $msg = "OAuth CSRF Check using \$_SESSION['oauth2state'] -> Failed!";
    throw new Exception("OAuth CSRF Check using ", 1);
  }

    
  // Try to get an access token (using the authorization code grant)
  $token = $provider->getAccessToken('authorization_code', 
                                     ['code' => $_GET['code']]);

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