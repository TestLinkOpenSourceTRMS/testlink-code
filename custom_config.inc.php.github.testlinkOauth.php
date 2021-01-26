<?php
#
# 20200522 - tested OK
# Application is registered for github user testlinkOAuth
# using ngrok to provide a public URL.
#
# Here the command:
# NOT IS THE CASE
# ngrok http -region eu -subdomain=testlink 80
#
$tlCfg->OAuthServers[2]['redirect_uri'] = 
  'http://fman.hopto.org/login.php?oauth=github';

$tlCfg->OAuthServers[2]['oauth_client_id'] ='aa5f70a8de342fb95043';
$tlCfg->OAuthServers[2]['oauth_client_secret'] = 
  'c8d61d5ec4ed4eb2ac81064c27043ddef351107e';

$tlCfg->OAuthServers[2]['oauth_enabled'] = true;
$tlCfg->OAuthServers[2]['oauth_name'] = 'github';

// Can be authorization_code (by default), client_credentials or password
$tlCfg->OAuthServers[2]['oauth_grant_type'] = 'authorization_code';  
$tlCfg->OAuthServers[2]['oauth_url'] = 'https://github.com/login/oauth/authorize';

$tlCfg->OAuthServers[2]['token_url'] = 'https://github.com/login/oauth/access_token';
$tlCfg->OAuthServers[2]['oauth_force_single'] = false; 
$tlCfg->OAuthServers[2]['oauth_profile'] = 'https://api.github.com/user';
$tlCfg->OAuthServers[2]['oauth_scope'] = 'user:email';

# End Of File