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

function oauth_link($oauthCfg)
{
  $oap = array();

  $oap['redirect_uri'] = trim($oauthCfg['redirect_uri']);
  if (isset($_SERVER['HTTPS'])) {
    $oap['redirect_uri'] =
      str_replace('http://', 'https://', $oap['redirect_uri']);
  }


  switch ($oauthCfg['oauth_name']) {
    case 'gitlab':
    case 'github':
      // @20200523 it seems that with relative can work 
      $url = 'lib/functions/oauth_providers/OAuth2Call.php?oauth2='
             . trim($oauthCfg['oauth_name']);
    break;


    default:
      $oap['prompt'] = 'none';
      // see https://docs.microsoft.com/en-us/azure/active-directory/develop/v1-protocols-oauth-code for details
      if ($oauthCfg['oauth_name'] == 'azuread') {
        if (!is_null($oauthCfg['oauth_domain']))
          $oap['domain_hint'] = $oauthCfg['oauth_domain'];
      } else {
        if ($oauthCfg['oauth_force_single']) {
          $oap['prompt'] = 'consent';
        }
      }


      $oap['response_type'] = 'code';
      $oap['client_id'] = $oauthCfg['oauth_client_id'];
      $oap['scope'] = $oauthCfg['oauth_scope'];
      $oap['state'] = $oauthCfg['oauth_name'];



      // http_build_query — Generate URL-encoded query string
      $url = $oauthCfg['oauth_url'] . '?' . http_build_query($oap);
    break;

  }

  return $url;
}


/**
 * getOAuthProviderCfg
 *
 */
function getOAuthProviderCfg($provider) 
{
  $OAuthProviders = config_get('OAuthServers');
  foreach ($OAuthProviders as $providerCfg) {
    if ($provider == trim($providerCfg['oauth_name'])) {
      return $providerCfg;
    }
  }
  return null;
}