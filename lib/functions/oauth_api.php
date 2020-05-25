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
    case 'azuread':
    case 'gitlab':
    case 'github':
    case 'google':
    case 'microsoft':
      // @20200523 it seems that with relative can work 
      $url = 'lib/functions/oauth_providers/OAuth2Call.php?oauth2='
             . trim($oauthCfg['oauth_name']);
    break;


    default:
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