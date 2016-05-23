<?php

/**
 * Root directory of Drupal installation.
 */
define('DRUPAL_ROOT', getcwd());
/*For some reason the server could not derive well the scheme of the url and returned something like ://<host>
 * in Ubuntu, giving such a malformed base url and resulting in an identical path to the base_url and thereby
 * an empty base_root. It is not sure whether this exists also in non-ajax calls, but it seemed better to derive the
 * very basic globals the same for both ajax and non-ajax. So we derive the scheme based on the HTTPS server var and
 * our own path derivation in initial.php.
 * 
 *  COPY THIS FILE TO THE ROOT OF THE INSTALLATION, REPLACING THE DRUPAL INDEX!
 */
include(DRUPAL_ROOT.'/initial.php');//Needed to derive the _WEB_URL which will be '' or '/vals'

require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);


//echo "<!-- Mijn Adres : ".$_SERVER['REMOTE_ADDR']. "-->";
//$ip_condition = ($_SERVER['REMOTE_ADDR'] == 'XXXX');
$vals_soc_included = defined('_VALS_SOC_ROOT');
if (! $vals_soc_included ) {
    define('_VALS_SOC_INCLUDED', FALSE);
    drupal_set_message('Please install and enable the vals_soc module',  'error');
    menu_execute_active_handler();
} else {
    define('_VALS_SOC_INCLUDED', TRUE);
    try {
        $ip_condition = FALSE;
        $vals_soc_pretend_possible = $ip_condition || (defined('_DEBUG') && _DEBUG &&
            (Users::isAdmin() || (defined('_VALS_SOC_TEST_ENV') && _VALS_SOC_TEST_ENV)));
        if (Users::isAdmin() || $vals_soc_pretend_possible){
            list($u, $o_state) = pretendUser();
        }

        menu_execute_active_handler();
        if ($vals_soc_pretend_possible){
            restoreUser($u, $o_state);
        }
    } catch (Exception $ex) {
        
        if  (!db_find_tables('soc_names')){
            //vals_soc database is not installed
            drupal_set_message('Please install and enable the vals_soc module',  'error');
            menu_execute_active_handler();
            
        } else {
            die('Some error occurred'. (constant('_DEBUG')  ? $ex->getMessage() : "" ));
        }
    }
}

//////// EDIT THE FILE UNDER THE ROOT IF YOU HAVE ALREADY INSTALLED THE APPLICATION
