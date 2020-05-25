<?php
//
// filesource oauth.microsoft.inc.php
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
//    require('aouth.microsoft.inc.php');
//
// ------------------------------------------------------------- 
$tlCfg->OAuthServers['microsoft'] = array();
$tlCfg->OAuthServers['microsoft']['redirect_uri'] = '';

$tlCfg->OAuthServers['microsoft']['oauth_enabled'] = true;
$tlCfg->OAuthServers['microsoft']['oauth_name'] = 'microsoft';
$tlCfg->OAuthServers['microsoft']['oauth_client_id'] = 'CLIENT_ID';
$tlCfg->OAuthServers['microsoft']['oauth_client_secret'] = 'CLIENT_SECRET';

// Can be authorization_code (by default), client_credentials or password
$tlCfg->OAuthServers['microsoft']['oauth_grant_type'] = 'authorization_code';
$tlCfg->OAuthServers['microsoft']['oauth_url'] = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize';

$tlCfg->OAuthServers['microsoft']['token_url'] = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';

$tlCfg->OAuthServers['microsoft']['oauth_force_single'] = true;
$tlCfg->OAuthServers['microsoft']['oauth_profile'] = 'https://graph.microsoft.com/v1.0/me';
$tlCfg->OAuthServers['microsoft']['oauth_scope'] = 'User.Read';

