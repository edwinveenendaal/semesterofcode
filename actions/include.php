<?php
define('DRUPAL_ROOT', realpath(getcwd().'/../../../../..'));
include(DRUPAL_ROOT.'/initial.php');//Needed to derive the _WEB_URL which will be '' or '/vals'
$scheme = ((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') ? 'https': 'http');
$base_url = $scheme. '://'.$_SERVER['HTTP_HOST']._WEB_URL; //This seems to be necessary to get to the user object: see
//http://drupal.stackexchange.com/questions/76995/cant-access-global-user-object-after-drupal-bootstrap, May 2014
//NOTE we used to have $_SERVER['REQUEST_SCHEME'] but this did NOT give a valid scheme back in Ubuntu: '' while on local machine 'http'
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);//Used to be DRUPAL_BOOTSTRAP_SESSION
include(_VALS_SOC_ROOT.'/includes/functions/ajax_functions.php');