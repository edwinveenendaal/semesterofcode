<?php 
module_load_include('inc', 'vals_soc', 'includes/module/vals_soc.access');

function vals_soc_content_start(){?>
	<div id='ajax_msg'></div>
	<div id='our_content'><?php
}

function vals_soc_content_end(){?>
	</div><?php
}
/**
 * Callback for the Program settings page
 * @return string
 */
function vals_soc_admin_program_settings_page() {
    ob_start();
    vals_soc_content_start();
    module_load_include('inc', 'vals_soc', 'includes/ui/administer/program_settings');
    vals_soc_content_end();
    return ob_get_clean();
}

function vals_soc_administer_page(){
    ob_start();
    vals_soc_content_start();
    module_load_include('inc', 'vals_soc', 'includes/ui/administer/blocklayout');
    vals_soc_content_end();
    return ob_get_clean();
}

function vals_soc_overview_proposals_page(){
	ob_start();
	vals_soc_content_start();
	$action = 'proposals';
	include(_VALS_SOC_ROOT. '/includes/ui/administer/blocklayout.inc');
	vals_soc_content_end();
	return ob_get_clean();
}

function vals_soc_administer_entity_page($action='administer'){
	ob_start();
	vals_soc_content_start();
	include(_VALS_SOC_ROOT. '/includes/ui/administer/blocklayout.inc');
	vals_soc_content_end();
	return ob_get_clean();
}

function vals_soc_administer_institute_page(){
	ob_start();
	vals_soc_content_start();
	$action = 'administer';
	include(_VALS_SOC_ROOT. '/includes/ui/administer/blocklayout.inc');
	vals_soc_content_end();
	return ob_get_clean();
}

function vals_soc_members_page(){
	ob_start();
	vals_soc_content_start();
	$action = 'members';
	include(_VALS_SOC_ROOT. '/includes/ui/administer/blocklayout.inc');
	vals_soc_content_end();
	return ob_get_clean();
}

function vals_soc_overview_page(){
	ob_start();
	vals_soc_content_start();
	$action = 'overview';
	include(_VALS_SOC_ROOT. '/includes/ui/administer/blocklayout.inc');
	vals_soc_content_end();
	return ob_get_clean();
}

function vals_soc_administer_groupspage(){
	ob_start();
	vals_soc_content_start();
	$action = 'groups';
	include(_VALS_SOC_ROOT. '/includes/ui/administer/blocklayout.inc');
	//This does not work with variables being defined in this scope like action
// 	module_load_include('inc', 'vals_soc', 'includes/ui/administer/blocklayout');
	vals_soc_content_end();
	return ob_get_clean();
}

function vals_soc_administer_projects_page(){
	ob_start();
	vals_soc_content_start();
	module_load_include('inc', 'vals_soc', 'includes/ui/administer/project');
	vals_soc_content_end();
	return ob_get_clean();
}

function vals_soc_administer_my_projects_page(){
	ob_start();
	vals_soc_content_start();
	$owner_only = TRUE;
	include(_VALS_SOC_ROOT. '/includes/ui/administer/project.inc');
	//module_load_include('inc', 'vals_soc', 'includes/ui/administer/project');
	vals_soc_content_end();
	return ob_get_clean();
}

function vals_soc_dashboard_overview_page(){
	$includeFile = null;
	if(hasRole(array(_ADMINISTRATOR_TYPE))){
		$includeFile = 'includes/ui/dashboard/adminlayout';
	}
    if(hasRole(array(_SOC_TYPE))){
		$includeFile = 'includes/ui/dashboard/soclayout';
	}
	elseif(hasRole(array(_ORGADMIN_TYPE))){
		$includeFile = 'includes/ui/dashboard/orgadminlayout';
	}
	elseif(hasRole(array(_MENTOR_TYPE))){
		$includeFile = 'includes/ui/dashboard/mentorlayout';
	}
	elseif(hasRole(array(_INSTADMIN_TYPE))){ // may need to update this to separate page
		$includeFile = 'includes/ui/dashboard/instadminlayout';
	}
	elseif(hasRole(array(_SUPERVISOR_TYPE))){  // may need to update this to separate page
		$includeFile = 'includes/ui/dashboard/supervisorlayout';
	}
	elseif(hasRole(array(_STUDENT_TYPE))){
		$includeFile = 'includes/ui/dashboard/studentlayout';
	}
	ob_start();
	vals_soc_content_start();
	module_load_include('inc', 'vals_soc', $includeFile);
	vals_soc_content_end();
	return ob_get_clean();
}

function vals_soc_orgsettings_page(){
	ob_start();
	vals_soc_content_start();
	module_load_include('inc', 'vals_soc', 'includes/ui/dashboard/orgsettings');
	vals_soc_content_end();
	return ob_get_clean();	
}

function vals_soc_browse_projects(){
	ob_start();
	vals_soc_content_start();
	module_load_include('inc', 'vals_soc', 'includes/ui/browse/projects');
	vals_soc_content_end();
	return ob_get_clean();
}

function vals_soc_favourite_projects(){
	ob_start();
	vals_soc_content_start();
	module_load_include('inc', 'vals_soc', 'includes/ui/administer/marked_projects');
	vals_soc_content_end();
	return ob_get_clean();
}

function vals_soc_browse_organisations(){
	ob_start();
	vals_soc_content_start();
	module_load_include('inc', 'vals_soc', 'includes/ui/browse/organisations');
	vals_soc_content_end();
	return ob_get_clean();
}

function vals_soc_browse_institutes(){
	ob_start();
	vals_soc_content_start();
	module_load_include('inc', 'vals_soc', 'includes/ui/browse/institutes');
	vals_soc_content_end();
	return ob_get_clean();
}

function vals_soc_browse_proposals(){
	ob_start();
	vals_soc_content_start();
	module_load_include('inc', 'vals_soc', 'includes/ui/browse/proposals');
	vals_soc_content_end();
	return ob_get_clean();
}

function vals_soc_browse_proposals_by_type(){
	ob_start();
	vals_soc_content_start();
	module_load_include('inc', 'vals_soc', 'includes/ui/browse/proposalsbytype');
	vals_soc_content_end();
	return ob_get_clean();	
}

function vals_soc_browse_my_proposals_by_type(){
	ob_start();
	vals_soc_content_start();
	$owner_only = TRUE;
	include(_VALS_SOC_ROOT. '/includes/ui/browse/proposalsbytype.inc');
	vals_soc_content_end();
	return ob_get_clean();
}

function vals_soc_browse_my_proposals(){
	ob_start();
	vals_soc_content_start();
	module_load_include('inc', 'vals_soc', 'includes/ui/administer/proposals');
	vals_soc_content_end();
	return ob_get_clean();
}

function vals_soc_my_project_offers(){
	ob_start();
	vals_soc_content_start();
	module_load_include('inc', 'vals_soc', 'includes/ui/browse/projectoffers');
	vals_soc_content_end();
	return ob_get_clean();
}

function vals_soc_my_accepted_project(){
	ob_start();
	vals_soc_content_start();
	module_load_include('inc', 'vals_soc', 'includes/ui/administer/accepted_project');
	vals_soc_content_end();
	return ob_get_clean();
}

function vals_soc_my_accepted_project_agreement(){
	ob_start();
	vals_soc_content_start();
	module_load_include('inc', 'vals_soc', 'includes/ui/administer/agreement');
	vals_soc_content_end();
	return ob_get_clean();
}

function vals_soc_my_accepted_project_finalisation(){
	ob_start();
	vals_soc_content_start();
	module_load_include('inc', 'vals_soc', 'includes/ui/administer/finalise');
	vals_soc_content_end();
	return ob_get_clean();
}