<?php
include_once(_VALS_SOC_ROOT.'/includes/functions/tab_functions.php');//it is sometimes included after propjects.php which does the same

function showRoleDependentPage($role, $show_action='administer', $show_last= FALSE){
	echo "<a href='"._WEB_URL."/dashboard'>".t('Back To Dashboard')."</a><br/>";
	switch ($role){
		case _ADMINISTRATOR_TYPE:
			showAdminPage($show_action, $show_last);
			break;
        case _SOC_TYPE:
			showAdminPage($show_action, $show_last);
			break;
		case _SUPERVISOR_TYPE:
			showSupervisorPage($show_action, $show_last);
			break;
		case _INSTADMIN_TYPE:
			showInstitutePage($show_action, $show_last);
			break;
			case _STUDENT_TYPE:
			showInstitutePage($show_action, $show_last);
			break;
		case _MENTOR_TYPE:
		case _ORGADMIN_TYPE:
			showOrganisationPage($show_action, $show_last);
			break;
	}
}

function showAdminPage($show_action, $show_last=FALSE){
	$data = array();
    $nr_tabs = 0;
	if (($show_action == 'administer') || ($show_action == 'overview')){
		echo '<h2>'.t('All the groups and persons').'</h2>';
		$data[] = array(1, 'Institutes', 'list', _INSTITUTE_GROUP);
		$data[] = array(1, 'Organisations', 'list', _ORGANISATION_GROUP);
		$data[] = array(1, 'Tutors', 'list', _SUPERVISOR_TYPE);
		$data[] = array(1, 'Mentors', 'list', _MENTOR_TYPE);
		$data[] = array(1, 'Students', 'list', _STUDENT_TYPE);
		$data[] = array(1, 'Organisation Admins', 'list', _ORGADMIN_TYPE);
		$data[] = array(1, 'Institute Admins', 'list', _INSTADMIN_TYPE);
		$nr_tabs = count($data);
		echo renderTabs($nr_tabs, null, 'admin_page-', '', $data, 0, TRUE,
				renderOrganisations(_INSTITUTE_GROUP, '', 'all', 'admin_page-1'));
	} elseif ($show_action == 'proposals') {
		echo '<h2>'.t('Overview of proposals per state').'</h2>';
		$data[] = array(1, 'draft', 'list', _PROPOSAL_OBJ, 0, 'state=draft');
		$data[] = array(1, 'open', 'list', _PROPOSAL_OBJ, 0, 'state=open');
		$data[] = array(1, 'published', 'list', _PROPOSAL_OBJ, 0, 'state=published');
		$data[] = array(1, 'accepted', 'list', _PROPOSAL_OBJ, 0, 'state=accepted');
		$data[] = array(1, 'rejected', 'list', _PROPOSAL_OBJ, 0, 'state=rejected');
		$data[] = array(1, 'retracted', 'list', _PROPOSAL_OBJ, 0, 'state=retracted');
		$data[] = array(1, 'finished', 'list', _PROPOSAL_OBJ, 0, 'state=finished');
		$data[] = array(1, 'archived', 'list', _PROPOSAL_OBJ, 0, 'state=archived');
		$nr_tabs = count($data);
		
		echo renderTabs($nr_tabs, null, 'admin_page-', _PROPOSAL_OBJ, $data, 0, TRUE,
				renderProposals('draft', null, 'admin_page-1', TRUE), 1 , 'proposal');
	}
	$s = '';
	for ($i=1;$i <= $nr_tabs;$i++){
		$s .= ($i > 1)? ', ':'';
		$s .= "'admin_page-$i'";
	}
	?>
	<script type="text/javascript">
       activatetabs('tab_', [<?php echo $s;?>]);
    </script><?php
}

function showSupervisorPage($show_action, $show_last=FALSE){
	//Get my institutions
	$my_id = Users::getMyId();
	$institutes = Groups::getGroups(_INSTITUTE_GROUP, $my_id);
	if (! $institutes->rowCount()){
		echo t('You have not registered yourself to an institute yet. ');
		echo tt('Register yourself with your institute at %1$s using the code you got or ask your Semester of Code Program organiser.',
			'<a href="'._WEB_URL ."/user/$my_id/edit\">". t('your account').'</a>');
	} else {
		$my_institute = $institutes->fetchObject();

		if ($show_action == 'administer'){
			echo t('You should be an admin member to edit this institute');//showInstituteAdminPage($my_institute);
		} elseif ($show_action == 'view'){
			showInstituteAdminPage($my_institute, $show_action);
		} elseif ($show_action == 'members') {
			showInstituteMembersPage($my_institute, $show_last);
		} elseif ($show_action == 'groups'){
			showInstituteGroupsAdminPage($my_institute, $show_last);
		} elseif ($show_action == 'overview'){
			 showInstituteOverviewPage($my_institute);
		} else {
			echo tt('there is no such action possible %1$s', $show_action);
		}
	}
}

function showInstitutePage($show_action, $show_last=FALSE){
    $my_id = Users::getMyId();
	//Get my institutions
	if (Users::isStudent()){
		$institutes = Users::getInstituteForUser($GLOBALS['user']->uid);
	} else {
		$institutes = Groups::getGroups(_INSTITUTE_GROUP, $GLOBALS['user']->uid);
	}

	if (! $institutes->rowCount()){
		if (Users::isInstituteAdmin() || user_access('vals admin register')) {
			echo t('You have no institute yet registered.');

			$add_tab = '<h2>'.t('Add your institute').'</h2>';
			$tab_prefix = 'inst_page-';
			$target = "${tab_prefix}1";
			$form = drupal_get_form('vals_soc_institute_form', '', $target, $show_action);
			unset($form['cancel']);
			$add_tab .= renderForm($form, $target, true);
			$data = array();
			$data[] = array(1, 'Add', 'add', _INSTITUTE_GROUP, null, "target=admin_container&show_action=$show_action", false);
			echo renderTabs(1, null, $tab_prefix, _INSTITUTE_GROUP, $data, null, TRUE, $add_tab);
			?>
			<script type="text/javascript">
				transform_into_rte();
				activatetabs('tab_', ['<?php echo $target;?>']);
	        </script><?php
		} else {
			echo t('You have not registered yourself to an institute yet. ');
			echo tt('Register yourself with your institute at %1$s using the code you got or ask your Semester of Code Program organiser.',
			'<a href="'._WEB_URL ."/user/$my_id/edit\">". t('your account').'</a>');
		}
	} else {
		$my_institute = $institutes->fetchObject();
		if ($show_action == 'administer'){
			//show_last is not relevant as we always expect max 1 institute
			showInstituteAdminPage($my_institute, $show_action);
		} elseif ($show_action == 'view'){
			//show_last is not relevant as we always expect max 1 institute
			showInstituteAdminPage($my_institute, $show_action);
		} elseif ($show_action == 'members') {
			showInstituteMembersPage($my_institute, $show_last);
		} elseif ($show_action == 'groups'){
			showInstituteGroupsAdminPage($my_institute, $show_last);
		} elseif ($show_action == 'overview'){
			showInstituteOverviewPage($my_institute, $show_last);
		} else {
			echo tt('there is no such action possible %1$s', $show_action);
		}
	}
}

function showInstituteOverviewPage($institute){
	include_once(_VALS_SOC_ROOT.'/includes/classes/Proposal.php');
	include_once(_VALS_SOC_ROOT.'/includes/classes/Organisations.php');
	include_once(_VALS_SOC_ROOT.'/includes/classes/Project.php');
	echo "<h2>" . t('Overview of your institute activity')."</h2>";
		$inst_id = $institute->inst_id;
		$nr_proposals_draft = count(Proposal::getProposalsPerOrganisation('', $inst_id));
		$nr_proposals_final = count(Proposal::getProposalsPerOrganisation('', $inst_id, 'published'));
		$nr_students = Users::getUsers(_STUDENT_TYPE, _INSTITUTE_GROUP, $inst_id)->rowCount();
		$nr_groups = Groups::getGroups(_STUDENT_GROUP, 'all', $inst_id)->rowCount();
		$nr_tutors = Users::getUsers(_SUPERVISOR_TYPE, _INSTITUTE_GROUP, $inst_id)->rowCount() +
			Users::getUsers(_INSTADMIN_TYPE, _INSTITUTE_GROUP, $inst_id)->rowCount();
		$nr_orgs = count(Organisations::getInstance()->getOrganisations());
		$nr_projects = count(Project::getProjects());
		echo "<b>".t("Proposals in draft:")."</b>&nbsp; $nr_proposals_draft<br>";
		echo "<b>".t("Proposals submitted:")."</b>&nbsp; $nr_proposals_final<br>";
		echo "<b>".t("Number of students subscribed:")."</b>&nbsp; $nr_students<br>";
		echo "<b>".t("Number of groups available:")."</b>&nbsp; $nr_groups<br>";
		echo "<b>".t("Number of supervisors subscribed:")."</b>&nbsp; $nr_tutors<br>";
		echo "<b>".t("Number of organisations:")."</b>&nbsp; $nr_orgs<br>";
		echo "<b>".t("Number of projects:")."</b>&nbsp; $nr_projects<br>";
}

function showInstituteGroupsAdminPage($my_institute, $show_last){
		$id = 0;
		$nr3 = 0;
		$data3 = $tabs3 = array();
		$groups = Groups::getGroups(_STUDENT_GROUP, $GLOBALS['user']->uid);

		$tab_id_prefix = 'group_page-';
		$nr_groups = $groups->rowCount();
		$translate = ($nr_groups >3) ? 0 : 2;
		$current_tab = $show_last ? $nr_groups : 1;
		foreach ($groups as $group){
			$nr3++;
			if ($nr3 == $current_tab){
				$id = $group->studentgroup_id;
				$my_group = $group;
			}
			$tabs3[] = "'$tab_id_prefix$nr3'";
			$data3[] = array($translate, $group->name, 'view', _STUDENT_GROUP, $group->studentgroup_id);
        }
		$nr3++;
	//	[translate, label, action, type, id, extra GET arguments, render with rich text area, render tab to the right]
		$data3[] = array(1, 'Add', 'add', _STUDENT_GROUP, null,
			"target=$tab_id_prefix$nr3&show_action=groups", false, 'addition');
		$tabs3[] = "'$tab_id_prefix$nr3'";

	    echo '<h2>'.t('The student groups of your institute').'</h2>';
	    echo renderTabs($nr3, 'Group', $tab_id_prefix, _STUDENT_GROUP, $data3, $id, TRUE,
	    		(($nr3 > 1) ?
	    			renderOrganisation(_STUDENT_GROUP, $my_group, null, "${tab_id_prefix}$current_tab"):
	    			tt('There is no group yet. Click "%1$s" to add one.', t('Add'))),
	    		$current_tab);
	    ?>
	    <script type="text/javascript">
			activatetabs('tab_', [<?php echo implode(', ', $tabs3);?>], '<?php echo
					"$tab_id_prefix$current_tab";?>');
			//activatetabs('tab_', [<?php //echo implode(', ', $tabs4);?>], null, true);
		</script>
	    <?php
}

function showInstituteMembersPage($my_institute, $show_last=FALSE){
		$nr2 = 2;
		$tab_id_prefix2 = 'member_page-';
		$data2 = array();
// 		 [translate, label, action, type, id, extra GET arguments, render with rich text area, render tab to the right]
		$data2[] = array(1, 'All Staff', 'showmembers', _INSTITUTE_GROUP, $my_institute->inst_id, "subtype=staff");
		$data2[] = array(1, 'All Students', 'showmembers', _INSTITUTE_GROUP, $my_institute->inst_id, "subtype=student");

		$tabs2 = array("'${tab_id_prefix2}1'", "'${tab_id_prefix2}2'");

		$id = 0;
		$nr4 = 0;
		$data4 = $tabs4 = array();
		$groups = Groups::getGroups(_STUDENT_GROUP, $GLOBALS['user']->uid);
		$current_tab = 1;
		foreach ($groups as $group){
			$nr2++;
			$tabs2[] = "'$tab_id_prefix2$nr2'";
			$data2[] = array(0, $group->name, 'showmembers', _STUDENT_GROUP, $group->studentgroup_id);//'Group'
        }
        echo '<h2>'.t('The registered staff and students of your institute').'</h2>';
	    echo renderTabs($nr2, t('My Group'), 'member_page-', _INSTITUTE_GROUP, $data2, $my_institute->inst_id, TRUE,
	    		renderUsers(_INSTADMIN_TYPE, '', $my_institute->inst_id, _INSTITUTE_GROUP, TRUE).
	    		renderUsers(_SUPERVISOR_TYPE, '', $my_institute->inst_id, _INSTITUTE_GROUP, TRUE));
	    ?>
	    <script type="text/javascript">
			activatetabs('tab_', [<?php echo implode(', ', $tabs2);?>]);
			<?php if ($nr4 > 0){?>activatetabs('tab_', [<?php echo implode(', ', $tabs4);?>], null, true);<?php }?>
		</script>
	    <?php

}

//Just being lazy: we can also view the institute with this function
function showInstituteAdminPage($my_institute, $action='administer'){
		$nr = 1;
		$tab_id_prefix = 'inst_page-';
		$data = array();
		$tabs = array("'${tab_id_prefix}1'");

		//we pass on the buttons=0 since we have the buttons as tabs
		$data[] = array(2, $my_institute->name, 'view', _INSTITUTE_GROUP, $my_institute->inst_id, "buttons=0");

		$next_tab = 2;
		if ($action=='administer'){
			if(vals_soc_access_check('dashboard/institute/administer/add_or_delete')){
				$data[] = array(1, 'Delete', 'delete', _INSTITUTE_GROUP, $my_institute->inst_id, '', false, 'delete');$nr++;
				$tabs[] = "'${tab_id_prefix}$next_tab'";
				$next_tab = 3;
			}
			$data[] = array(1, 'Edit', 'edit', _INSTITUTE_GROUP, $my_institute->inst_id, '', false, 'editing');$nr++;
			$tabs[] = "'${tab_id_prefix}$next_tab'";
		}

		//[number of tabs, label start, tab id start, type, data, id, render targets, active target content, active tab]
		echo renderTabs($nr, '', $tab_id_prefix, _INSTITUTE_GROUP, $data, $my_institute->inst_id, TRUE,
				renderOrganisation(_INSTITUTE_GROUP, $my_institute, null, "${tab_id_prefix}1", false));
	    ?>
	    <script type="text/javascript">
			activatetabs('tab_', [<?php echo implode(',', $tabs);?>]);
		</script>
	    <?php
}

function showOrganisationPage($show_action, $show_last=FALSE){
	//Get my organisations
	$my_id = Users::getMyId();
	$organisations = Groups::getGroups(_ORGANISATION_GROUP, $my_id);
	if (! $organisations->rowCount()){
		if (Users::isOrganisationAdmin() || user_access('vals admin register')) {
			echo t('You have no organisation yet registered');
			echo '<h2>'.t('Add your organisation').'</h2>';
			$tab_prefix = 'organisation_page-';
			$target = "${tab_prefix}1";
			$form = drupal_get_form('vals_soc_organisation_form', '', $target);
			$add_tab = renderForm($form, $target, true);
			$data = array();
			$data[] = array(1, 'Add', 'add', _ORGANISATION_GROUP, null, "target=admin_container&show_action=$show_action", true, 'adding_to_the right');
			echo renderTabs(1, null, $tab_prefix, _ORGANISATION_GROUP, $data, null, TRUE, $add_tab);
			?>
			<script type="text/javascript">
			   transform_into_rte();
			   activatetabs('tab_', ['<?php echo $target;?>']);
	        </script><?php
		} else {
			echo t('You have not registered yourself to an organisation yet. ');
			echo tt('Register yourself with your organisation at %1$s using the code you got from a colleague.',
					'<a href="'._WEB_URL ."/user/$my_id/edit\">". t('your account').'</a>');
		}
	} else {
		if ($show_action == 'administer'){
			showOrganisationAdminPage($organisations, $show_action, $show_last);
		} elseif ($show_action == 'view'){
			showOrganisationAdminPage($organisations, $show_action, $show_last);
		} elseif ($show_action == 'members') {
			showOrganisationMembersPage($organisations);
		} elseif ($show_action == 'overview'){
			showOrganisationOverviewPage($organisations);//showInstituteGroupsAdminPage($my_institute, $show_last);
		} else {
			echo tt('There is no such action possible %1$s', $show_action);
		}
	}
}

function showOrganisationOverviewPage($organisations){
	include_once(_VALS_SOC_ROOT.'/includes/classes/Proposal.php');
	if ($organisations->rowCount() == 1) {
		$org_id = $organisations->fetchObject()->org_id;
		$nr_proposals_draft = count(Proposal::getProposalsPerOrganisation($org_id));
		$nr_proposals_final = count(Proposal::getProposalsPerOrganisation($org_id, '', 'published'));
		echo "<b>".t("Proposals in draft:")."</b>&nbsp; $nr_proposals_draft<br>";
		echo "<b>".t("Proposals submitted:")."</b>&nbsp; $nr_proposals_final<br>";
	} else {
		foreach ($organisations->fetchAll() as $org){
			echo "<h2>".$org->name. "</h2>";
			$org_id = $org->org_id;
			$nr_proposals_draft = count(Proposal::getProposalsPerOrganisation($org_id));
			$nr_proposals_final = count(Proposal::getProposalsPerOrganisation($org_id, '', 'published'));
			echo "<b>".t("Proposals in draft:")."</b>&nbsp; $nr_proposals_draft<br>";
			echo "<b>".t("Proposals submitted:")."</b>&nbsp; $nr_proposals_final<br>";
		}
	}
}

function showOrganisationAdminPage($organisations, $action='administer', $show_last=FALSE){
	$nr = 0;
	$data = array();
	$tabs = array();
	$id = 0;

	$tab_id_prefix = 'organisation_page-';
	$nr_orgs = $organisations->rowCount();
	$current_tab = $show_last ? $nr_orgs : 1;
	$current_tab_content = t('You have no organisation yet.');
	foreach ($organisations as $org){
		$nr++;
		if ($nr == $current_tab){
			$id = $org->org_id;
			$my_organisation = $org;
			$current_tab_content = renderOrganisation(_ORGANISATION_GROUP, $my_organisation, null, "$tab_id_prefix$current_tab",
				($action == 'administer'));
		}
		$tabs[] = "'$tab_id_prefix$nr'";
		$data[] = array(2, $org->name, 'view', _ORGANISATION_GROUP, $org->org_id);
	}

	// check for org admin editing rights
	if(($action == 'administer') && vals_soc_access_check('dashboard/organisation/administer/add_or_delete')){
		//To remove the add tab: comment the three lines below
		$nr++;
		$data[] = array(1, 'Add', 'add', _ORGANISATION_GROUP, null, "target=$tab_id_prefix$nr&show_action=administer", true, 'adding_to_the right');
		$tabs[] = "'$tab_id_prefix$nr'";
	}
	echo sprintf('<h3>%1$s</h3>', t('Organisations you are involved in'));
	if ($nr){
		echo renderTabs($nr, 'Org', $tab_id_prefix, _ORGANISATION_GROUP, $data, $id, TRUE, $current_tab_content
			//$type, $organisation='', $organisation_owner='', $target='', $show_buttons=true)
			, $current_tab);
		?>
		<script type="text/javascript">
			activatetabs('tab_', [<?php echo implode(',', $tabs);?>], '<?php echo "$tab_id_prefix$current_tab";?>');
			</script>
		<?php
	} else {
		echo $current_tab_content;
	}
}

function showOrganisationMembersPage($organisations){
	$tab_offset = 1;
	$data = array();
	$tabs = array();
	// 		[translate, label, action, type, id, extra GET arguments]
	if(user_access('vals admin register')){
		$data[] = array(1, t('All Members'), 'showmembers', _ORGANISATION_GROUP, 'all');
		$tabs = array("'mentor_page-$tab_offset'");
		$tab_offset++;
	}

	foreach ($organisations as $org){
		$tabs[] = "'mentor_page-$tab_offset'";
		$data[] = array(2, $org->name, 'showmembers', _ORGANISATION_GROUP, $org->org_id);
		$tab_offset++;
	}
	if ($data){
		$first_tab_group_id = $data[0][4];

		echo renderTabs(--$tab_offset, 'Org', 'mentor_page-', _ORGANISATION_GROUP, $data, null, TRUE,
				renderUsers(_ORGADMIN_TYPE, '', $first_tab_group_id, _ORGANISATION_GROUP, TRUE).
				renderUsers(_MENTOR_TYPE, '', $first_tab_group_id, _ORGANISATION_GROUP, TRUE));

		?>
		<script type="text/javascript">
			activatetabs('tab_', [<?php echo implode(',', $tabs);?>]);
		</script>
	<?php
	} else {
		echo t('You cannot see organisation members at the moment');
	}
}