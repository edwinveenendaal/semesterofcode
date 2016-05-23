<?php

drupal_add_css(drupal_get_path('module', 'vals_soc') . '/includes/css/dashboard/css/dashboard.css', array(
    'type' => 'file',
    'group' => CSS_THEME
));

//Will be called at the end of this file
function initDashboardLayout() {
    $tl = Timeline::getInstance()->getOrgadminTimelineVars();
    print_r($tl);
    return getOrgAdminLayout($tl);
}

function getOrgAdminLayout($timeline_args) {
    global $base_url;
    $output = '
		<div class="dashboard" id="main-dashboard">
			<div class="dashboard-head">
				<span>' . t("Organisation Admin dashboard") . '</span>
			</div>
		
			<div class="block block-dashboard">
				<p id="dashboardLegend">' . $timeline_args['dashboardLegend'] . '</p>

				<!-- column one -->
				<div class="column first">
	';

    if ($timeline_args['managedOrganisationsVisible']) {
        $output .='
					<div class="column-entry org_app">
						<h4>
							<a class="dashboard-link component-link"
								href="' . $base_url . '/dashboard/organisation/administer"
								title="' . t("Managed organisations") . '">' . t("Managed organisations") . '</a>
						</h4>
						<p>' . t("Organizations for which you are an administrator") . '</p>
					</div>
		';
    }
    if ($timeline_args['manageProjectIdeasVisible']) {
        $output .='
					<div class="column-entry org_app manage_project_ideas">
						<h4>
							<a class="dashboard-link component-link"
								href="' . $base_url . '/dashboard/projects/administer/mine"
								title="' . t("Manage my project ideas") . '">' . t("Manage my project ideas") . '</a>
						</h4>
						<p>' . t("Allows you to add and amend project ideas for students.") . '</p>
							<div>
								<a href="' . $base_url . '/dashboard/projects/administer/mine" title="Manage my projects only">Manage my projects only</a>
								|<a href="' . $base_url . '/dashboard/projects/administer" title="Manage all my organisations projects">Manage all my organisations projects</a>
							</div>
					</div>
		';
    }

    if (TRUE || $timeline_args['manageProjectIdeasVisible']) {//TODO: the statistics can be seen by all
        $output .="
		<div class='column-entry org_app shipment_tracking'>
		<h4>
			<a class='dashboard-link component-link'
			href='$base_url/dashboard/overview'
			title='" . t('Overview of proposals, number of students etc.') . "'>" . t('Overview') . "</a>
		</h4>
		<p>" . t('Shows activity on proposals for your organisation, number of students etc.') . "</p>
		
		</div>
		";
    }

    $output .='	
				</div>
				<!-- column two -->
				<div class="column">
	';

    if ($timeline_args['organisationMembersVisible']) {
        $output .='
					<div class="column-entry participants">
						<h4>
							<a class="dashboard-link component-link"
							href="' . $base_url . '/dashboard/organisation/members"
							title="' . t("Members of my organisations") . '">' . t("Members of my organisations") . '</a>
						</h4>
						<p>' . t("List of your organisations members") . '</p>
					</div>
		';
    }

    if ($timeline_args['browseProjectIdeasVisible']) {
        $output .='
					<div class="column-entry projects">
						<h4>
							<a class="dashboard-link component-link"
								href="' . $base_url . '/dashboard/projects/browse"
								title="' . t("All project ideas (read only)") . '">' . t("All project ideas (read only)") . '</a>
						</h4>
						<p>' . t("All project ideas from all organisations") . '</p>
					</div>
		';
    }

    if ($timeline_args['proposalsVisible']) {
        $output .='
					<div class="column-entry proposals_submitted">
						<h4>
							<a class="dashboard-link component-link"
								href="' . $base_url . '/dashboard/proposals/browsebytype/mine"
								title="' . t("Review submitted proposals") . '">' . t("Review submitted proposals") . '</a>
						</h4>
						<p>' . t("Allows you to see a list of proposals submitted to your projects by prospective students") . '</p>
						<div>
							<a href="' . $base_url . '/dashboard/proposals/browsebytype/mine" title="' . t('Filter on my projects only') . '">' .
                t('Review proposals for my projects only') . '</a>
							|<a href="' . $base_url . '/dashboard/proposals/browsebytype" title="' .
                t('Review proposals for all my organisations ') . '">' .
                t('Review proposals for all my organisations') . '</a>&nbsp;(' . t('Possibly limited access') . ')
						</div>
					</div>
		';
    }
    /*
     * // commenting this out as the actual link is something different to what is described
     * 
      if($timeline_args['matchedProjectsVisible']){
      $output .='
      <div class="column-entry proposals_submitted">
      <h4>
      <a class="dashboard-link component-link"
      href="'.$base_url.'/dashboard/projects/administer?state=active"
      title="'.t("Projects for my organisations").'">'.t("Projects for my organisations").'</a>
      </h4>
      <p>'.t("List of projects being developed for my organisations").'</p>
      </div>
      ';
      }
     */

    if ($timeline_args['myAcceptedProjectsVisible']) {
        $output .='
					<div class="column-entry proposals_submitted">
						<h4>
							<a class="dashboard-link component-link"
								href="' . $base_url . '/dashboard/projects/mine"
								title="' . t("My accepted projects") . '">' . t("My accepted projects") . '</a>
						</h4>
						<p>' . t("List of accepted projects that I am mentoring") . '</p>
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
