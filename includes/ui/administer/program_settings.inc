<?php
drupal_add_css(drupal_get_path('module', 'vals_soc') . '/includes/css/dashboard/css/dashboard.css', array(
      'type' => 'file',
      'group' => CSS_THEME
));

function initProgSettingsLayout(){
	global $base_url;
	$output ='
		<div class="dashboard" id="program-settings-dashboard">
			<div class="dashboard-head">
				<span>'.t("Program settings").'</span>
			</div>
	
			<div class="block block-dashboard">
				<p id="dashboardLegend"></p>
	
				<!-- column one -->
				<div class="column first">
	';

		$output .='
					<div class="column-entry program_settings">
						<h4>
							<a class="dashboard-link"
							href="'.$base_url.'/dashboard/program_settings/timeline"
							title="'.t("Edit Timeline").'">'.t("Edit Timeline").'</a>
						</h4>
						<p>'.t("Here you can specify when events happen in the system, such as when students".
								" can submit proposals and when the program starts and finishes").'</p>
					</div>
		';
	
	$output .='
				</div>
				<!-- column two -->
				<div class="column">
	';

	$output .='
					<div class="column-entry program_settings">
						<h4>
							<a class="dashboard-link"
							href="'.$base_url.'/dashboard/program_settings/messages"
							title="'.t("Edit Messages").'">'.t("Edit Messages").'</a>
						</h4>
						<p>'.t("Here you can specify the format of emails that are sent to users of the program").'</p>
					</div>
		';
	$output .='
					<div class="column-entry program_settings">
						<h4>
							<a class="dashboard-link"
							href="'.$base_url.'/dashboard/program_settings/frontpage"
							title="'.t("Edit frontpage").'">'.t("Edit frontpage texts").'</a>
						</h4>
						<p>'.t("Here you can specify the text blocks that appear on the frontpage"
                            . ".").'</p>
					</div>
		';
	$output .='
				</div>
	
			</div>
	
		</div>
	';
	echo $output;
}
initProgSettingsLayout();