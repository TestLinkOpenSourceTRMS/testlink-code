<?php
# filesource oauth.google.inc.php
#
# IMPORTANT NOTICE
# key in $tlCfg->OAuthServers[]
# can be anything you want that make this configuration
# does not overwrite other or will be overwritten
#
# HOW TO use this file ?
# just add the following line to your custom_config.inc.php
#
# require('aouth.google.inc.php');
#
#
# You need to create the configuration for your site
#
$tlCfg->OAuthServers['google']['oauth_enabled'] = true;
$tlCfg->OAuthServers['google']['oauth_name'] = 'google';

// Get from /gui/themes/default/images
$tlCfg->OAuthServers['google']['oauth_client_id'] = 'CLIENT_ID';
$tlCfg->OAuthServers['google']['oauth_client_secret'] = 'CLIENT_SECRET';

// Can be authorization_code (by default), client_credentials or password
$tlCfg->OAuthServers['google']['oauth_grant_type'] = 'authorization_code';  
$tlCfg->OAuthServers['google']['oauth_url'] = 'https://accounts.google.com/o/oauth2/auth';
$tlCfg->OAuthServers['google']['token_url'] = 'https://accounts.google.com/o/oauth2/token';

// false => then the only user will be selected automatically (applied for google)
$tlCfg->OAuthServers['google']['oauth_force_single'] = false; 

// the domain you want to whitelist
$tlCfg->OAuthServers['google']['oauth_domain'] = 'google.com'; 
$tlCfg->OAuthServers['google']['oauth_profile'] = 'https://www.googleapis.com/oauth2/v1/userinfo';
$tlCfg->OAuthServers['google']['oauth_scope'] = 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile';
