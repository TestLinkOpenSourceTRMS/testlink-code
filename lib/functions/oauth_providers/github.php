<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  github.php
 *
 * Github OAUTH API (authentication)
 *
 * @internal revisions
 * @since 1.9.17
 *
 */

// Get token
function oauth_get_token($authCfg, $code) {

  $result = new stdClass();
  $result->status = array('status' => tl::OK, 'msg' => null);

  // Params to get token
  $oauthParams = array(
     'code'          => $code,
     'client_id'     => $authCfg['oauth_client_id'],
     'client_secret' => $authCfg['oauth_client_secret']
  );

  $oauthParams['redirect_uri'] = $oauthCfg['redirect_uri'];  
  if( isset($_SERVER['HTTPS']) ) {
    $oauthParams['redirect_uri'] = 
      str_replace('http://', 'https://', $oauthParams['redirect_uri']);  
  }  

  $curlAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:7.0.1) Gecko/20100101 Firefox/7.0.1';
  $curlContentType = array('Content-Type: application/xml','Accept: application/json');

  // Step #1 - Get the token
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $authCfg['token_url']);
  curl_setopt($curl, CURLOPT_POST, 1);
  curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
  curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($oauthParams));
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_COOKIESESSION, true);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  $result_curl = curl_exec($curl);

  if( $result_curl === false ) {
    echo 'Curl error: ' . curl_error($curl);
    echo '<pre>';
    var_dump(curl_getinfo($curl));
    echo '</pre>';
    die();
  }
  curl_close($curl);
  $tokenInfo = json_decode($result_curl);

  // If token is received start session
  if (isset($tokenInfo->access_token)) {
    $oauthParams['access_token'] = $tokenInfo->access_token;

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


    $result->options = new stdClass();
    $result->options->givenName = $userInfo['login'];
    $result->options->familyName = $userInfo['id'];
    $result->options->user = $emailInfo[0]['email'];
    $result->options->auth = 'oauth';

  } else {
    $result->status['msg'] = 'An error occurred during getting token';
    $result->status['status'] = tl::ERROR;
  }

  return $result;
}
