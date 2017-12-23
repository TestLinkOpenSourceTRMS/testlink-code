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
function oauth_link($oauthCfg)
{
  $promt = 'none';
  if ($oauthCfg['oauth_force_single'])
  {
    $promt = 'consent';    
  }  

  $oauth_url = $oauthCfg['oauth_url'];
  $oauth_params = array(
    'redirect_uri'  => isset($_SERVER['HTTPS']) ? 'https://' : 'http://' . $_SERVER['HTTP_HOST']. '/login.php?oauth=' . $oauthCfg['oauth_name'],
    'response_type' => 'code',
    'prompt'        => $promt,
    'client_id'     => $oauthCfg['oauth_client_id'],
    'scope'         => $oauthCfg['oauth_scope']
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