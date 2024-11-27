<?php
//
// filesource oauth.azuread.inc.php
//
// Azure AD 
// Fill in CLIENT_ID,
//         CLIENT_SECRET,
//         YOURTESTLINKSERVER,
//         TENANTID 
// with your information
// See this article for registering an application: https://docs.microsoft.com/en-us/azure/active-directory/develop/v1-protocols-oauth-code
// Make sure, you grant admint consent for it: https://docs.microsoft.com/en-us/azure/active-directory/manage-apps/configure-user-consent

// 
// IMPORTANTE NOTICE
// key in $tlCfg->OAuthServers[]
// can be anything you want that make this configuration
// does not overwrite other or will be overwritten
//
// HOW TO use this file ?
// 1. copy this file to 
//     [TESTLINK_INSTALL]/cfg/
//
// 2. configure according your application
//
// 3. add the following line to your custom_config.inc.php
//    require('aouth.azuread.inc.php');
//
// ------------------------------------------------------------- 
$tlCfg->OAuthServers['azuread'] = array();

$tlCfg->OAuthServers['azuread']['redirect_uri'] = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . '/login.php';


$tlCfg->OAuthServers['azuread']['oauth_client_id'] = 'CHANGE_WITH_CLIENT_ID';
$tlCfg->OAuthServers['azuread']['oauth_client_secret'] = 
  'CHANGE_WITH_CLIENT_SECRET';

// https://login.microsoftonline.com/YOUR_TENANT_ID/v2.0/.well-known/openid-configuration
$azureADBaseURL = 'https://login.microsoftonline.com/CHANGE_WITH_TENANT_ID';
$msGraphURL = 'https://graph.microsoft.com';
$tlCfg->OAuthServers['azuread']['oauth_url'] = 
  $azureADBaseURL . '/oauth2/v2.0/authorize';

$tlCfg->OAuthServers['azuread']['token_url'] = 
  $azureADBaseURL . '/oauth2/v2.0/token';

$tlCfg->OAuthServers['azuread']['oauth_profile'] = 
  $msGraphURL . '/oidc/userinfo';


$tlCfg->OAuthServers['azuread']['oauth_enabled'] = true;
$tlCfg->OAuthServers['azuread']['oauth_name'] = 'azuread'; //do not change this
$tlCfg->OAuthServers['azuread']['oauth_force_single'] = true; 
$tlCfg->OAuthServers['azuread']['oauth_grant_type'] = 'authorization_code';  

// the domain you want to whitelist (email domains)
$tlCfg->OAuthServers['azuread']['oauth_domain'] = 'autsoft.hu'; 


$tlCfg->OAuthServers['azuread']['oauth_scope'] = 
  'https://graph.microsoft.com/mail.read https://graph.microsoft.com/user.read openid profile email';
