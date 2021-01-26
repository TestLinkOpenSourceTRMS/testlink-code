<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  azuread.php
 *
 * Azure OAUTH API (authentication)
 *
 * @internal revisions
 *
 */

// Get token
function oauth_get_token($authCfg, $code) {

  $result = new stdClass();
  $result->status = array('status' => tl::OK, 'msg' => null);

  // Params to get token
  $oauthParams = array(
    'code'          => $code,
    'grant_type'    => $authCfg['oauth_grant_type'],
    'client_id'     => $authCfg['oauth_client_id'],
    'client_secret' => $authCfg['oauth_client_secret']
  );

  $oauthParams['redirect_uri'] = trim($authCfg['redirect_uri']);     
  if( isset($_SERVER['HTTPS']) ) {
    $oauthParams['redirect_uri'] = 
      str_replace('http://', 'https://', $oauthParams['redirect_uri']);  
  }  

  $token_curl = curl_init();
  curl_setopt($token_curl, CURLOPT_URL, $authCfg['token_url']);
  curl_setopt($token_curl, CURLOPT_POST, 1);
  curl_setopt($token_curl, CURLOPT_POSTFIELDS, http_build_query($oauthParams));
  curl_setopt($token_curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($token_curl, CURLOPT_SSL_VERIFYPEER, false);
  $result_token_curl = curl_exec($token_curl);
  curl_close($token_curl);
  $tokenInfo = json_decode($result_token_curl, true);

  // To get user principal name, given name, and surname from token endpoint, Directory.ReadWriteAll permission is required.
  // However, it is too much to give Write permission, so if you get them via the graph API, only User.Read and Email.Read will be sufficient.
  $graph_curl = curl_init();
  $graph_api_header = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $tokenInfo['access_token']
  ];
  curl_setopt($graph_curl, CURLOPT_URL, 'https://graph.microsoft.com/v1.0/me');
  curl_setopt($graph_curl, CURLOPT_HTTPHEADER, $graph_api_header);
  curl_setopt($graph_curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($graph_curl, CURLOPT_SSL_VERIFYPEER, false);
  $result_graph_curl = curl_exec($graph_curl);
  curl_close($graph_curl);
  $userInfo = json_decode($result_graph_curl, true);

  // At this point we may turn to the user_info endpoint for additional information
  // but for now, we are going to ignore it, as all neccessary information is available
  // in the id_token
  if (isset($tokenInfo['id_token'])){
    list($header, $payload, $signature) = explode(".", $tokenInfo['id_token']);
    $jwtInfo = json_decode(base64_decode ($payload), true);

    if (isset($jwtInfo['oid'])){
      if (isset($authCfg['oauth_domain'])) {
        $domain = substr(strrchr($userInfo['userPrincipalName'], "@"), 1);
        if ($domain !== $authCfg['oauth_domain']){
          $result->status['msg'] = 
          "TestLink Oauth policy - User email domain:$domain does not 
           match \$authCfg['oauth_domain']:{$authCfg['oauth_domain']} ";
          $result->status['status'] = tl::ERROR;
        }
      }
    } else {
      $result->status['msg'] = 'TestLink - User ID is empty';
      $result->status['status'] = tl::ERROR;
    }

    $options = new stdClass();
    $options->givenName = $userInfo['givenName'];
    $options->familyName = $userInfo['surname'];
    $options->user = $userInfo['userPrincipalName'];
    $options->auth = 'oauth';

    $result->options = $options;
  } else {
    $result->status['msg'] = 'TestLink - An error occurred during get token e'.$result_curl.'e';
    $result->status['status'] = tl::ERROR;
  }

  return $result;
}