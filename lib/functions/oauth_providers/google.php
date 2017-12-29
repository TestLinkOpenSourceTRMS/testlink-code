<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  google.php
 *
 * Google OAUTH API (authentication)
 *
 * @internal revisions
 * @since 1.9.17
 *
 */

//Get token
function oauth_get_token($authCfg, $code)
{

  $result = new stdClass();
  $result->status = array('status' => tl::OK, 'msg' => null);

  //Params to get token
  $oauthParams = array(
     'code'          => $code,
     'grant_type'    => $authCfg['oauth_grant_type'],
     'client_id'     => $authCfg['oauth_client_id'],
     'redirect_uri'  => isset($_SERVER['HTTPS']) ? 'https://' : 'http://' . $_SERVER[HTTP_HOST]. '/login.php?oauth=google',
     'client_secret' => $authCfg['oauth_client_secret']
  );

  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $authCfg['token_url']);
  curl_setopt($curl, CURLOPT_POST, 1);
  curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode(http_build_query($oauthParams)));
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  $result_curl = curl_exec($curl);
  curl_close($curl);
  $tokenInfo = json_decode($result_curl, true);

  //If token is received start session
  if (isset($tokenInfo['access_token'])){
    $oauthParams['access_token'] = $tokenInfo['access_token'];
    $userInfo = json_decode(file_get_contents($authCfg['oauth_profile'] . '?' . urldecode(http_build_query($oauthParams))), true);

    if (isset($userInfo['id'])){
      if (isset($authCfg['oauth_domain'])) {
        $domain = substr(strrchr($userInfo['email'], "@"), 1);
        if ($domain !== $authCfg['oauth_domain']){
          $result->status['msg'] = 'User doesn\'t correspond to Oauth policy';
          $result->status['status'] = tl::ERROR;
        }
      }
    } else {
      $result->status['msg'] = 'User ID is empty';
      $result->status['status'] = tl::ERROR;
    }

    $options = new stdClass();
    $options->givenName = $userInfo['given_name'];
    $options->familyName = $userInfo['family_name'];
    $options->user = $userInfo['email'];
    $options->auth = 'oauth';

    $result->options = $options;
  } else {
    $result->status['msg'] = 'An error occurred during getting token';
    $result->status['status'] = tl::ERROR;
  }

  return $result;

}
