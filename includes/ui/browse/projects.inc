<?php
module_load_include('php', 'vals_soc', 'includes/classes/Project');
module_load_include('php', 'vals_soc', 'includes/classes/Organisations');
module_load_include('php', 'vals_soc', 'includes/pages/projects');
drupal_add_library('system','ui.widget');
drupal_add_library('system','ui.dialog');
drupal_add_library('system','ui.icon');
//TODO: do we want this? test it out
drupal_add_css(_VALS_SOC_PATH .'/includes/ui/tabs/tabs.css', array(
      'type' => 'file',
      'group' => CSS_THEME
));
drupal_add_js (_VALS_SOC_PATH .'/includes/ui/tabs/activatetabs.js');
//end TODO
drupal_add_js(_VALS_SOC_PATH .'/includes/js/ajax.js');
drupal_add_js(_VALS_SOC_PATH .'/includes/js/target_functions.js');
drupal_add_js(_VALS_SOC_PATH .'/includes/js/render_functions.js');
drupal_add_js(_VALS_SOC_PATH .'/includes/js/jtable/jquery.jtable.js');
drupal_add_css(_VALS_SOC_PATH . '/includes/js/jtable/jtable.css', array(
      'type' => 'file',
      'group' => CSS_THEME
));
ctools_include('modal');
ctools_modal_add_js();
$pid = getRequestVar('pid', '', 'get');
initBrowseProjectLayout($pid);