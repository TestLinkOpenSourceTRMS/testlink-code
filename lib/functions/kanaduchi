<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  oauth_api.php
 *
 * Google OAUTH API (authentication)
 *
 *
 * @internal revisions
 * @since 1.9.17
 *
 */
  
// Create correct link for oauth
function oauth_link($authCfg)
{
  $promt = 'none';
  if ($tlCfg->authentication['oauth_force_single'])
    $promt = 'consent';

  $oauth_url = $authCfg['oauth_url'] . '/auth';
  $oauth_params = array(
    'redirect_uri'  => 'http://' . $_SERVER[HTTP_HOST]. '/login.php?oauth=true',
    'response_type' => 'code',
    'prompt'        => $promt,
    'client_id'     => $authCfg['oauth_client_id'],
    'scope'         => $authCfg['oauth_scope']
  );


  $url = $oauth_url . '?' . urldecode(http_build_query($oauth_params));
  return $url;
}

//Create new user
function create_oauth_user_db($login, $options)
{
  $user = new tlUser();
  $user->login = $login;
  $user->emailAddress = $login;
  $user->firstName = $options->givenName;
  $user->lastName = $options->familyName;
  $user->authentication = 'OAUTH';
  $user->isActive = true;
  $user->setPassword('oauth');
  return ($user->writeToDB($db) == tl::OK);
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

    $result->userInfo = $userInfo;
  } else {
    $result->status['msg'] = 'An error occurred during getting token';
    $result->status['status'] = tl::ERROR;
  }

  return $result;

}
