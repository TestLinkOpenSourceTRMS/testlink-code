<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  github.php
 *
 * Github OAUTH API (authentication)
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
                  'redirectUri' => $oauthParams['redirect_uri']]; 

  $provider = new \League\OAuth2\Client\Provider\Github($providerCfg);  

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

    /*
    printf('<br>getName %s!', $user->getName());
    printf('<br>getEmail %s!', $user->getEmail());
    printf('<br>getUserName %s!', $user->getUserName());
    
    echo '<pre>';
    var_dump($user->toArray());
    var_dump($user->getNickname());
    echo '</pre>';
    die();
  }
  curl_close($curl);
  $tokenInfo = json_decode($result_curl);

  // If token is received start session
  if (isset($tokenInfo->access_token)) {
    $oauthParams['access_token'] = $tokenInfo->access_token;
    $curlContentType = array('Authorization: token ' . $tokenInfo->access_token, 'Content-Type: application/xml','Accept: application/json');

    $queryString = http_build_query($tokenInfo);
    $targetURL = array();
    $targetURL['user'] = $authCfg['oauth_profile'] . '?' . $queryString;
    $targetURL['email'] = $authCfg['oauth_profile'] . '/emails?'. $queryString;

    // Get User
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $targetURL['user']);
    curl_setopt($curl, CURLOPT_USERAGENT, $curlAgent);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $curlContentType);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $result_curl = curl_exec($curl);
    $userInfo = json_decode($result_curl, true);
    curl_close($curl);

    if (!isset($userInfo['login'])) {
      $result->status['msg'] = 'User ID is empty';
      $result->status['status'] = tl::ERROR;
    }

    // Get email
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $targetURL['email'] );
    curl_setopt($curl, CURLOPT_USERAGENT, $curlAgent);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $curlContentType);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $result_curl = curl_exec($curl);
    $emailInfo = json_decode($result_curl, true);
    curl_close($curl);


    $firstLast = $user->getName();
    $result->options = new stdClass();
    $result->options->givenName = $firstLast;
    $result->options->familyName = $firstLast;

    // Github does not provide the email in the getResourceOwner()
    // response if the user has configured the mail as Private.
    // Solution from:
    // https://github.com/thephpleague/oauth2-github/issues/3
    //
    $email = $user->getEmail();
    if ($email == null) {
      $request = $provider->getAuthenticatedRequest(
                  'GET', 
                  'https://api.github.com/user/emails', 
                  $token // Your access token
      );
      $array = $provider->getParsedResponse($request);
      foreach ($array as $item) {
          if ($item['primary'] === true) {
              $email = $item['email'];
              break;
          }
      }
    }
    $result->options->user = $email;
    $result->options->email = $email;
    //$result->options->login = $user->getUserName();
    $result->options->auth = 'oauth';
    
    return $result;

  } catch (Exception $e) {
     // Failed to get user details
     exit('Oh dear...');
  }
}