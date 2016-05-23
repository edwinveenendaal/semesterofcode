<?php
// drupal_add_css(_VALS_SOC_PATH .'/includes/ui/tabs/tabs.css', array(
// 'type' => 'file',
// 'group' => CSS_THEME
// ));

function initMyProjectLayout($role) {
    $title = (hasRole(array(_STUDENT_TYPE)) ? t('My accepted project') : t('My accepted projects'));
    drupal_set_title($title);
    switch ($role) {
        case _STUDENT_TYPE:
            getSingleAcceptedProjectView(Agreement::getInstance()->getSingleStudentsAgreement(true));
            break;
        case _SUPERVISOR_TYPE:
        case _INSTADMIN_TYPE:
        case _MENTOR_TYPE:
        case _ORGADMIN_TYPE:
            getListView();
            break;
    }
}

function getListView(){
    if( hasRole(array(_ORGADMIN_TYPE)) || hasRole(array(_MENTOR_TYPE)) 
        || hasRole(array(_INSTADMIN_TYPE)) || hasRole(array(_SUPERVISOR_TYPE))){
		?>
		
		<div id="TableContainer" style="width: 800px;"></div>
		<script type="text/javascript">

				jQuery(document).ready(function($){
					window.view_settings = {};

					function loadFilteredProjects(){
						$("#TableContainer").jtable("load", {
							//organisation: $("#organisation").val(),
						});
					}

				    //Prepare jTable
					$("#TableContainer").jtable({
						paging: true,
						pageSize: 10,
						sorting: true,
						defaultSorting: "pid ASC",
						actions: {
							listAction: module_url + "actions/agreement_actions.php?action=list_search"
						},
						fields: {
							agreement_id: {
								key: true,
		    					create: false,
		    					edit: false,
		    					list: false
							},
							title: {
								title: "Project",
								width: "34%",
								display: function (data) {
									return "<a title=\"View project details\" href=\"javascript:void(0);\" onclick=\"getProjectDetail("+data.record.project_id+")\">"
											+ data.record.title+"</a>";
									},
							},
							name: {
								title: "Student",
								width: "30%",
								display: function (data){
									var op = data.record.name;
									if(data.record.student_name != null){
										op += '&nbsp;(' +data.record.student_name + ')';
									}
									return op;
								}
							},
							<?php if( hasRole(array(_ORGADMIN_TYPE)) || hasRole(array(_MENTOR_TYPE)) ){?>
							supervisor_user_name: {
								title: "Supervisor",
								width: "30%",
								display: function (data){
									var op = data.record.supervisor_user_name;
									if(data.record.supervisor_name != null){
										op += '&nbsp;(' +data.record.supervisor_name + ')';
									}
									return op;
								}
							},
							<?php } ?>
							<?php if( hasRole(array(_INSTADMIN_TYPE)) || hasRole(array(_SUPERVISOR_TYPE)) ){?>
							mentor_user_name: {
								title: "Mentor",
								width: "30%",
								display: function (data){
									var op = data.record.mentor_user_name;
									if(data.record.mentor_name != null){
										op += '&nbsp;(' +data.record.mentor_name + ')';
									}
									return op;
								}
							},
							<?php } ?>
							proposal_view : {
								width: "6%",
		    					title: "View",
								sorting: false,
		    					display: function (data) {
									return "<a title=\"View Project\" href=\"javascript:void(0);\" "+
										"onclick=\"getAcceptedProjectOverview("+data.record.agreement_id+")\">"+
											"<span class=\"ui-icon ui-icon-info\">See detail</span></a>";
		    					},

		    					create: false,
		    					edit: false
							},

						},
					});

					//Load projects list from server on initial page load
					loadFilteredProjects();

				});
			</script><?php
    } else {
        echo t('Sorry you are not allowed to access this page');
    }
}

function getSingleAcceptedProjectView($agreement) {
    global $base_url;

    $output_text = '';
    $output_legend = '';
    $dashboard_legend = '';
    $project_details_legend = '';
    $project_details_text = '';

    $finalise_text = t("The project will be marked as completed when all agree so. An evaluation input possibility is provided, your supervisor and your mentor");
    $finalise_legend = t("Finalise the project");

    if (Users::isStudent()) {
        $output_text = t("Optionally create an agreement between you, your supervisor and your mentor");
        $output_legend = t("My project agreement");

        $dashboard_legend = t('Here are the resources for your accepted project');
        $project_details_legend = t("My project details");
        $project_details_text = t("Shows you the original project idea along with the proposal you originally submitted");
    } else if (Users::isOrganisationAdmin() || Users::isMentor()) {
        $output_text = t("Optionally create an agreement between you, your student and their supervisor");
        $output_legend = t("Project agreement");
        $dashboard_legend = t('Here are the resources for this accepted project');
        $project_details_legend = t("Project details");
        $project_details_text = t("Shows you the original project idea along with the proposal originally submitted by the student");

        drupal_set_title(t('Accepted project: ') . $agreement->title);
    } else {
        $output_text = t("Optionally create an agreement between you, your student and their mentor");
        $output_legend = t("Project agreement");
        $dashboard_legend = t('Here are the resources for this accepted project');
        $project_details_legend = t("Project details");
        $project_details_text = t("Shows you the original project idea along with the proposal originally submitted by the student");
        drupal_set_title(t('Accepted project: ') . $agreement->title);
    }


    $output = '
	 	<script type="text/javascript">
            window.view_settings = {};
            window.view_settings.apply_projects = 0;
            window.view_settings.rate_projects  = 0;
        </script>
        ';

    if (Users::isStudent()) {
        $output .= '<div id="baktoprops"><a href=" ' . $base_url . '/dashboard">' . t('Back to dashboard') . '</a></div>';
    } else {
        $output .= '<div id="baktoprops"><a href=" ' . $base_url . '/dashboard/projects/mine">' . t('Back to your list of accepted projects') . '</a></div>';
    }

    $output .= '<br/>
		<div class="dashboard" id="main-dashboard">
			<div class="dashboard-head">';

    if (Users::isStudent()) {
        $output .= '
			<span>' . t("My project") . '&nbsp;' . $agreement->title . '</span>';
    } else {
        $output .= '
			<span>' . t("Project page for ") . $agreement->title . '</span>';
    }

    $output .= '
			
			</div>
	
			<div class="block block-dashboard">
				<p id="dashboardLegend">' . $dashboard_legend . '</p>
	
				<!-- column one -->
				<div class="column first">
	';

    $output .='
					<div class="column-entry org_app">
						<h4>
							<a class="dashboard-link component-link"
								href="javascript:void(0);" onclick="getAgreement(' . $agreement->agreement_id . ')"
								title="' . $output_legend . '">' . $output_legend . '</a>
						</h4>
						<p>' .
            $output_text;
    $output .= '</p>
					</div>
		';

    $output .='
					<div class="column-entry org_app">
						<h4>
							<a class="dashboard-link component-link"
								href="javascript:void(0);" onclick="getFinalisation(' . $agreement->agreement_id . ')"
								title="' . $finalise_legend . '">' . $finalise_legend . '</a>
						</h4>
						<p>' .
            $finalise_text;
    $output .= '</p>
					</div>
		';

    $output .='
				</div>
				<!-- column two -->
				<div class="column">
	';


    $output .='
			<div class="column-entry proposals_submitted">
                <h4>
                    <a class="dashboard-link component-link" ' .
            'href="javascript:void(0);" ' .
            'onclick="getProposalDetail(' . $agreement->proposal_id . ')"' .
            ' title="' . $project_details_legend . '">' . $project_details_legend . '</a>
                </h4>
                <p>' . $project_details_text . '</p>
			</div>
		';


    $output .='
				</div>
	
			</div>
	
		</div>
	';
    echo $output;
}
