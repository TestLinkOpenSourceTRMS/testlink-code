<?php
#
# @filename oauth.github.inc.php
#
# 20200522 - tested OK
# Application is registered for github user testlinkOAuth
# this user is owned by TestLink Development Team.
#
#
# Client implemented using 
# https://github.com/thephpleague/oauth2-github
#
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
#   require('aouth.github.inc.php');
#
# ###########################################################
# This is a working example for test site
# http://fman.hopto.org/
#
# You need to create the configuration for your site
# This is only a working example that is useful
# for the TestLink Development Team
#
$tlCfg->OAuthServers['github'] = array();
$tlCfg->OAuthServers['github']['redirect_uri'] = 
  'http://fman.hopto.org/login.php?oauth=github';

$tlCfg->OAuthServers['github']['oauth_client_id'] ='aa5f70a8de342fb95043';
$tlCfg->OAuthServers['github']['oauth_client_secret'] = 
  'c8d61d5ec4ed4eb2ac81064c27043ddef351107e';

$tlCfg->OAuthServers['github']['oauth_enabled'] = true;
$tlCfg->OAuthServers['github']['oauth_name'] = 'github';
# End Of File