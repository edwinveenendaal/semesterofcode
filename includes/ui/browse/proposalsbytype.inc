<?php
drupal_add_css(_VALS_SOC_PATH .'/includes/ui/tabs/tabs.css', array(
      'type' => 'file',
      'group' => CSS_THEME
    ));

drupal_add_js (_VALS_SOC_PATH .'/includes/ui/tabs/activatetabs.js');

drupal_add_js(_VALS_SOC_PATH .'/includes/js/jtable/jquery.jtable.js');
drupal_add_css(_VALS_SOC_PATH . '/includes/js/jtable/jtable.css', array(
	'type' => 'file',
	'group' => CSS_THEME
));

drupal_add_library('system','ui.widget');
drupal_add_library('system','ui.dialog');
drupal_add_library('system','ui.icon');

drupal_add_js(_VALS_SOC_PATH .'/includes/js/ajax.js');
drupal_add_js(_VALS_SOC_PATH .'/includes/js/target_functions.js');
drupal_add_js(_VALS_SOC_PATH .'/includes/js/render_functions.js');

ctools_include('modal');
ctools_modal_add_js();

include(_VALS_SOC_ROOT.'/includes/classes/Project.php');
include(_VALS_SOC_ROOT.'/includes/classes/Organisations.php');
include(_VALS_SOC_ROOT.'/includes/pages/proposals.php');

$owner_only = isset($owner_only) ? $owner_only : false;
initBrowseProposalsByTypeLayout($owner_only);