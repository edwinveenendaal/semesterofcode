<?php 
drupal_add_css(drupal_get_path('module', 'vals_soc') . '/includes/css/dashboard/css/dashboard.css', array(
      'type' => 'file',
      'group' => CSS_THEME
    ));
function initDashboardLayout(){
	return getSupervisorLayout(Timeline::getInstance()->getSupervisorTimelineVars());
}

function getSupervisorLayout($timeline_args){
	global $base_url;
	$output ='
	<div class="dashboard" id="main-dashboard">
	<div class="dashboard-head">
		<span>'.t("Supervisor dashboard").'</span>
	</div>

	<div class="block block-dashboard">
	<p id="dashboardLegend">'.$timeline_args['dashboardLegend'].'</p>

	
	
	<!-- column one -->
				<div class="column first">
	';
	if($timeline_args['managedInstitutesVisible']){
		$output .='
					<div class="column-entry org_app">
						<h4>
							<a class="dashboard-link component-link"
								href="'.$base_url.'/dashboard/institute/view"
								title="'.t("Details for my institution").'">'.t("My institution").'</a>
						</h4>
						<p>'.t("Institution in which you are giving lectures").'</p>
					</div>
		';
	}
	if($timeline_args['organisationMembersVisible']){
		$output .='
		<div class="column-entry participants">
		<h4>
		<a class="dashboard-link component-link"
		href="'.$base_url.'/dashboard/institute/members"
		title="'.t("Members of my institution").'">'.t('Members of my institution').'</a>
		</h4>
		<p>'.t("Lists of your institution colleagues").'</p>
		</div>
		';
	}
	
	if($timeline_args['groupsVisible']){
		$output .='
		<div class="column-entry participants">
		<h4>
		<a class="dashboard-link"
		href="'.$base_url.'/dashboard/institute/group/administer"
		title="'.t("Groups").'">'.t("Groups").'</a>
		</h4>
		<p>'.t("Create groups of students to manage codes and the initial supervisor-student relation.").'</p>
		<div>
		<!--<a href="#" title="'.t("Create Groups").'">'.t("Create and see your list of groups").'</a> |
		<a href="#" title="'.t("Manage student groups").'">'.t("Manage student groups").'</a>-->
		</div>
		</div>
		';
	}
	
	if(TRUE || $timeline_args['manageProjectIdeasVisible']){//TODO: the statistics can be seen by all
		$output .="
		<div class='column-entry org_app shipment_tracking'>
		<h4>
		<a class='dashboard-link component-link'
		href='$base_url/dashboard/overview'
		title='".t('Overview of proposals, number of students etc.')."'>".t('Overview')."</a>
				</h4>
				<p>".t('Shows activity on proposals made within your institute, number of students etc.')."</p>
	
				</div>
				";
	}
	
	$output .='	
				</div>
				<!-- column two -->
				<div class="column">
	';

	

	if($timeline_args['manageProjectIdeasVisible']){
		$output .='
		<div class="column-entry org_app projects">
		<h4>
		<a class="dashboard-link component-link"
		href="'.$base_url.'/dashboard/projects/browse"
		title="'.t("Review project ideas").'">'.t("Review project ideas").'</a>
		</h4>
		<p>'.t("Allows you to add comments to project ideas and to filter accidental entries.").'</p>
		</div>
		';
	}
	
	if($timeline_args['proposalsVisible']){
		$output .='
					<div class="column-entry proposals_submitted">
						<h4>
							<a class="dashboard-link component-link"
								href="'.$base_url.'/dashboard/proposals/browsebytype/mine"
								title="'.t("Proposals submitted within my institute").'">'.t('Proposals submitted by students from my institute and in my groups').'</a>
						</h4>
						<p>'.t("List of proposals submitted within my institute").'</p>
						<div>
							<a href="'.$base_url.'/dashboard/proposals/browsebytype/mine" title="'. t('See my students proposals only').'">'.
							t('See my students proposals only') .'</a>
							|<a href="'.$base_url.'/dashboard/proposals/browsebytype" title="'.
							t('See all proposals from students in my Institution').'">'.
							t('See all proposals from students in my Institution'). '</a>&nbsp;('. t('Limited access'). ')
						</div>
					</div>
		';
	}	

	if($timeline_args['myAcceptedProjectsVisible']){
		$output .='
					<div class="column-entry proposals_submitted">
						<h4>
							<a class="dashboard-link component-link"
								href="'.$base_url.'/dashboard/projects/mine"
								title="'.t("My accepted projects").'">'.t("My accepted projects").'</a>
						</h4>
						<p>'.t("List of accepted projects that I am supervising").'</p>
					</div>
				';
	}

	$output .='
	</div>

	</div>

	</div>
	';
	echo $output;
}

initDashboardLayout();
?>