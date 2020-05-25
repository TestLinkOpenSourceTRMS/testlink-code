<?php
# filesource oauth.google.inc.php
#
# IMPORTANT NOTICE
# key in $tlCfg->OAuthServers[]
# can be anything you want that make this configuration
# does not overwrite other or will be overwritten
#
# HOW TO use this file ?
# 1. copy this file to 
#     [TESTLINK_INSTALL]/cfg/
#
# 2. configure according your application
#
# 3. add the following line to your custom_config.inc.php
#    require('aouth.google.inc.php');
#
# #############################################################
# Client implemented using 
# https://github.com/thephpleague/oauth2-google
##
# This is a working example for test site
# http://fman.hopto.org/
#
# You need to create the configuration for your site
# This is only a working example that is useful
# for the TestLink Development Team
#
$tlCfg->OAuthServers['google'] = array();
$tlCfg->OAuthServers['google']['redirect_uri'] = 
  'http://fman.hopto.org/login.php?oauth=google';

$tlCfg->OAuthServers['google']['oauth_enabled'] = true;
$tlCfg->OAuthServers['google']['oauth_name'] = 'google';

// Get from /gui/themes/default/images
$tlCfg->OAuthServers['google']['oauth_client_id'] = 
  '860603525614-fscj9cgr2dvks51uh6odl67skec536fd.apps.googleusercontent.com';

$tlCfg->OAuthServers['google']['oauth_client_secret'] = 
  '_YOKquNTa4Fux-OMJoxDBuov';

// Needed when you use the cURL implementation
// Can be authorization_code (by default), client_credentials or password
// $tlCfg->OAuthServers['google']['oauth_grant_type'] = 'authorization_code';  
//$tlCfg->OAuthServers['google']['oauth_url'] = 'https://accounts.google.com/o/oauth2/auth';
//$tlCfg->OAuthServers['google']['token_url'] = 'https://accounts.google.com/o/oauth2/token';

// false => then the only user will be selected automatically (applied for google)
//$tlCfg->OAuthServers['google']['oauth_force_single'] = false; 

// the domain you want to whitelist
//$tlCfg->OAuthServers['google']['oauth_domain'] = 'google.com'; 
//$tlCfg->OAuthServers['google']['oauth_profile'] = 'https://www.googleapis.com/oauth2/v1/userinfo';
//$tlCfg->OAuthServers['google']['oauth_scope'] = 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile';
