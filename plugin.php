<?php
/*
Plugin Name: LDAP User Authentication
Plugin URI: https://github.com/johnellmore/ldap-user-authentication
Description: Invisible WordPress plugin which uses allows LDAP users to authenticate.
Author: John Ellmore
Author URI: http://johnellmore.com/
Version: 1.0
*/

// load dependencies and project files
require_once('src/Config.php');
require_once('src/LDAPConnection.php');
require_once('src/Plugin.php');

// kick off the plugin
$plugin = \Ellmore\LDAPUserAuthentication\Plugin::getInstance();
$plugin->bootstrap();
