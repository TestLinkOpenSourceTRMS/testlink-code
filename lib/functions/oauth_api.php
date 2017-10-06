<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  oauth_api.php
 *
 * OAUTH API (authentication)
 *
 *
 * @internal revisions
 * @since 1.9.17
 *
 */
  
// Create correct link for oauth
function oauth_link($authCfg)
{
  $oauth_url = $authCfg['oauth_url'] . '/auth';
  $oauth_params = array(
    'redirect_uri'  => 'http://' . $_SERVER[HTTP_HOST]. '/login.php?oauth=true',
    'response_type' => 'code',
    'client_id'     => $authCfg['oauth_client_id'],
    'scope'         => $authCfg['oauth_scope']
  );
  $url = $oauth_url . '?' . urldecode(http_build_query($oauth_params));
  return $url;
}

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
     'redirect_uri'  => 'http://' . $_SERVER[HTTP_HOST]. '/login.php?oauth=true',
     'client_secret' => $authCfg['oauth_client_secret']
  );
  $url = $authCfg['oauth_url'] . '/token';

  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $url);
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
    $userInfo = json_decode(file_get_contents('https://www.googleapis.com/oauth2/v1/userinfo' . '?' . urldecode(http_build_query($oauthParams))), true);

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

    $result->userInfo = $userInfo;
  } else {
    $result->status['msg'] = 'An error occurred during getting token';
    $result->status['status'] = tl::ERROR;
  }

  return $result;

}