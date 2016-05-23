<?php
drupal_add_css(_VALS_SOC_PATH .'/includes/ui/tabs/tabs.css', array(
    'type' => 'file',
    'group' => CSS_THEME
 ));

drupal_add_js (_VALS_SOC_PATH .'/includes/ui/tabs/activatetabs.js');
drupal_add_js (_VALS_SOC_PATH .'/includes/js/ajax.js');
drupal_add_js (_VALS_SOC_PATH .'/includes/js/target_functions.js');
drupal_add_library('system','ui');

include(_VALS_SOC_ROOT.'/includes/pages/administration.php');
include(_VALS_SOC_ROOT.'/includes/classes/Proposal.php');
include(_VALS_SOC_ROOT.'/includes/functions/render_functions.php');

$role = getRole();
$action = isset($action) ? $action : 'administer';?>
<div id='admin_container' class='tabs_container'>
	<?php showRoleDependentPage($role, $action, false);?>
</div>