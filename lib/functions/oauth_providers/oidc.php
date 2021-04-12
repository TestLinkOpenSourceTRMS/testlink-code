<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  oidc.php
 *
 * OIDC OAUTH API (authentication)
 *
 * @internal revisions
 * @since 1.9.20
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
     'client_secret' => $authCfg['oauth_client_secret'],
     'grant_type'    => $authCfg['oauth_grant_type']
  );

  $oauthParams['redirect_uri'] = $authCfg['redirect_uri'];  
  if( isset($authCfg['https']) ) {
    $oauthParams['redirect_uri'] = 
      str_replace('http://', 'https://', $oauthParams['redirect_uri']);  
  }

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
    return false;
  }
  curl_close($curl);
  $tokenInfo = json_decode($result_curl);

  // If token is received start session
  if (isset($tokenInfo->access_token)) {

    $tokens = explode('.', $tokenInfo->id_token);
    if (count($tokens) != 3)
      return false;

    $base64payload = $tokens[1];

    $payload = json_decode(base64_decode($base64payload));
    if ($payload==false){
      return false;
    }

    $result->options = new stdClass();
    $result->options->givenName = $payload->name;
    $result->options->familyName = $payload->name;
    $result->options->user = $payload->email;
    $result->options->auth = 'oauth';
    return $result;
  }
  $result->status['msg'] = 'An error occurred during getting token';
  $result->status['status'] = tl::ERROR;

  return $result;
}
