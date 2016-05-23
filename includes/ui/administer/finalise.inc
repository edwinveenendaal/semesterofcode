<?php
module_load_include('php', 'vals_soc', 'includes/classes/Agreement');

drupal_add_css(_VALS_SOC_PATH .'/includes/ui/tabs/tabs.css', array(
    'type' => 'file',
    'group' => CSS_THEME
));

drupal_add_js (_VALS_SOC_PATH .'/includes/ui/tabs/activatetabs.js');
drupal_add_js (_VALS_SOC_PATH .'/includes/js/ajax.js');
drupal_add_js (_VALS_SOC_PATH .'/includes/js/target_functions.js');
//drupal_add_library('system','ui');

include(_VALS_SOC_ROOT.'/includes/pages/agreement.php');
include(_VALS_SOC_ROOT.'/includes/functions/render_functions.php');
global $base_url;
echo '<div id="baktoprops"><a href=" '.$base_url.'/dashboard/projects/mine">'.t('Back to My project').'</a></div>';
echo '<br/>';
?>
<div id='admin_container' class='tabs_container'>
	<?php showFinalisation();?>
</div>