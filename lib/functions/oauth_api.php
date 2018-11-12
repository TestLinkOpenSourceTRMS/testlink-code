<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  oauth_api.php
 *
 * OAUTH API (authentication)
 *
 */

// Create correct link for oauth
function oauth_link($oauthCfg) {

  $oauth_params = array();

  $oauth_params['prompt'] = 'none';
  if ($oauthCfg['oauth_force_single']) {
    $oauth_params['prompt'] = 'consent';    
  }  

  $oauth_params['response_type'] = 'code';
  $oauth_params['client_id'] = $oauthCfg['oauth_client_id'];
  $oauth_params['scope'] = $oauthCfg['oauth_scope'];

  $oauth_params['redirect_uri'] = $oauthCfg['redirect_uri'];  
  if( isset($_SERVER['HTTPS']) ) {
    $oauth_params['redirect_uri'] = 
      str_replace('http://', 'https://', $oauth_params['redirect_uri']);  
  }  

  $url = $oauthCfg['oauth_url'] . '?' . http_build_query($oauth_params);
  return $url;
}
