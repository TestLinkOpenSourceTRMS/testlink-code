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

//Get token
function oauth_get_token($authCfg, $code)
{

  $result = new stdClass();
  $result->status = array('status' => tl::OK, 'msg' => null);

  //Params to get token
  $oauthParams = array(
     'code'          => $code,
     'client_id'     => $authCfg['oauth_client_id'],
     'redirect_uri'  => isset($_SERVER['HTTPS']) ? 'https://' : 'http://' . $_SERVER[HTTP_HOST]. '/login.php?oauth=github',
     'client_secret' => $authCfg['oauth_client_secret']
  );

  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $authCfg['token_url']);
  curl_setopt($curl, CURLOPT_POST, 1);
  curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
  curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode(http_build_query($oauthParams)));
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_COOKIESESSION, true);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  $result_curl = curl_exec($curl);
  curl_close($curl);

  $tokenInfo = json_decode($result_curl);

  //If token is received start session
  if (isset($tokenInfo->access_token)){
    $oauthParams['access_token'] = $tokenInfo->access_token;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $authCfg['oauth_profile'].'?'.urldecode(http_build_query($tokenInfo)));
    curl_setopt($curl, CURLOPT_USERAGENT,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:7.0.1) Gecko/20100101 Firefox/7.0.1');
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/xml','Accept: application/json'));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $result_curl = curl_exec($curl);
    $userInfo = json_decode($result_curl, true);
    curl_close($curl);

    if (!isset($userInfo['login'])){
      $result->status['msg'] = 'User ID is empty';
      $result->status['status'] = tl::ERROR;
    }

    //Get email
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $authCfg['oauth_profile'].'/emails?'.urldecode(http_build_query($tokenInfo)));
    curl_setopt($curl, CURLOPT_USERAGENT,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:7.0.1) Gecko/20100101 Firefox/7.0.1');
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/xml','Accept: application/json'));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $result_curl = curl_exec($curl);
    $emailInfo = json_decode($result_curl, true);
    curl_close($curl);

    $options = new stdClass();
    $options->givenName = $userInfo['login'];
    $options->familyName = $userInfo['id'];
    $options->user = $emailInfo[0]['email'];
    $options->auth = 'oauth';

    $result->options = $options;
  } else {
    $result->status['msg'] = 'An error occurred during getting token';
    $result->status['status'] = tl::ERROR;
  }
  return $result;
}
