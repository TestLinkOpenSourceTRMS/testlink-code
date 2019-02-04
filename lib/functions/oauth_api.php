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

  $oap = array();

  $oap['redirect_uri'] = trim($oauthCfg['redirect_uri']);  
  if( isset($_SERVER['HTTPS']) ) {
    $oap['redirect_uri'] = 
      str_replace('http://', 'https://', $oaps['redirect_uri']);  
  }  

  $oap['prompt'] = 'none';
  if ($oauthCfg['oauth_force_single']) {
    $oap['prompt'] = 'consent';    
  }  

  $oap['response_type'] = 'code';
  $oap['client_id'] = $oauthCfg['oauth_client_id'];
  $oap['scope'] = $oauthCfg['oauth_scope'];

  // http_build_query — Generate URL-encoded query string
  $url = $oauthCfg['oauth_url'] . '?' . http_build_query($oap);

  return $url;
}
