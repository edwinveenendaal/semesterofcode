<?php
include('include.php');//Includes the necessary bootstrapping and the ajax functions
//include(_VALS_SOC_ROOT.'/includes/classes/Organisations.php');
//include(_VALS_SOC_ROOT.'/includes/classes/Institutes.php');
//include(_VALS_SOC_ROOT.'/includes/classes/Proposal.php');
include(_VALS_SOC_ROOT.'/includes/classes/Agreement.php');
include(_VALS_SOC_ROOT.'/includes/functions/render_functions.php');
include(_VALS_SOC_ROOT.'/includes/pages/myacceptedproject.php');
include(_VALS_SOC_ROOT.'/includes/pages/agreement.php');
//include(_VALS_SOC_ROOT.'/includes/pages/administration.php');

//return result depending on action parameter
switch ($_GET['action']){
	case 'edit':
		//$type = altSubValue($_POST, 'type', '');
		$id = altSubValue($_POST, 'id', '');
		$target = altSubValue($_POST, 'target', '');
		//$agreement = Agreement::getInstance()->getSingleStudentsAgreement($id);
		$agreement = Agreement::getInstance()->getSingleAgreementById($id, true);
		
		$originalPath = false;
		if(isset($_POST['path'])){
			$originalPath = $_POST['path'];
		}
		unset($_POST);
		$form = drupal_get_form("vals_soc_agreement_form", $agreement, $target);
		if($originalPath){
			$form['#action'] = url($originalPath);
		}
		renderForm($form, $target);
	break;
    case 'sign_complete':
		$id = altSubValue($_POST, 'id', '');
		$target = altSubValue($_POST, 'target', '');
		$agreement = Agreement::getInstance()->getSingleAgreementById($id, true);
		
		$originalPath = false;
		if(isset($_POST['path'])){
			$originalPath = $_POST['path'];
		}
		unset($_POST);
		$form = drupal_get_form("vals_soc_final_form", $agreement, $target);
		if($originalPath){
			$form['#action'] = url($originalPath);
		}
		renderForm($form, $target);
	break;
	case 'save':
		$type = altSubValue($_POST, 'type', '');
		$id = altSubValue($_POST, 'id', '');
		$show_action = altSubValue($_POST, 'show_action', '');
	
		$props = Agreement::getInstance()->filterPostLite(Agreement::getInstance()->getKeylessFields(), $_POST);
		
		if(isset($_POST['student_signed_already'])){
			$props['student_signed'] = 1;
		}
		if(isset($_POST['supervisor_signed_already'])){
			$props['supervisor_signed'] = 1;
		}
		if(isset($_POST['mentor_signed_already'])){
			$props['mentor_signed'] = 1;
		}
		$props['agreement_id'] = $id;
		$result = Agreement::getInstance()->updateAgreement($props);
		if ($result){
			echo json_encode(array(
					'result'=>TRUE,
					'id' => $id,
					'type'=> $type,
					'new_tab' => !$id ? $result : 0,
					'show_action' => $show_action,
					'msg'=>
					($id ? tt('You succesfully changed the data of your %1$s', t_type($type)):
							tt('You succesfully added your %1$s', t_type($type))).
					(_DEBUG ? showDrupalMessages(): '')
			));
		} else {
			echo jsonBadResult();
		}
	break;
    case 'finalise':
		$type = altSubValue($_POST, 'type', '');
		$id = altSubValue($_POST, 'id', '');
		//$show_action = altSubValue($_POST, 'show_action', '');
        $agreement_obj = Agreement::getInstance();
		$props = $agreement_obj->filterPostLite(
            $agreement_obj->getKeylessFields());
		
		if(isset($_POST['student_completed'])){
			$props['student_completed'] = 1;
		}
		if(isset($_POST['supervisor_completed'])){
			$props['supervisor_completed'] = 1;
		}
		if(isset($_POST['mentor_completed'])){
			$props['mentor_completed'] = 1;
		}
        if(isset($_POST['evaluation'])){
			$props['evaluation'] = $_POST['evaluation'];
		}
		$props['agreement_id'] = $id;
        $result = Agreement::getInstance()->updateAgreement($props);
		if ($result){
			echo json_encode(array(
					'result'=>TRUE,
					'id' => $id,
					'type'=> $type,
					'new_tab' => 0,
					'show_action' => 'finalisation_view',
					'msg'=>
					t('You succesfully marked the project as completed').
					(_DEBUG ? showDrupalMessages(): '')
			));
		} else {
			echo jsonBadResult();
		}
	break;
    
	case 'view':
		$type = altSubValue($_POST, 'type');
		$id = altSubValue($_POST, 'id');
		$target = altSubValue($_POST, 'target', '');
		$buttons = altSubValue($_GET, 'buttons', true);
		if (! ($id && $type && $target)){
			die(t('There are missing arguments. Please inform the administrator of this mistake.'));
		}
		//$agreement = Agreement::getInstance()->getProjectAgreements($id, '', '', '', '', true)->fetchObject();
		$agreement = Agreement::getInstance()->getSingleAgreementById($id, true);
		echo "<div id='msg_$target'></div>";
		echo renderAgreement($type, $agreement, '',$target, $buttons);
	break;
    case 'finalisation_view':
        $type = altSubValue($_POST, 'type');
		$id = altSubValue($_POST, 'id');
		$target = altSubValue($_POST, 'target', '');
		$buttons = altSubValue($_GET, 'buttons', true);
		if (! ($id && $type && $target)){
			die(t('There are missing arguments. Please inform the administrator of this mistake.'));
		}
		$agreement = Agreement::getInstance()->getSingleAgreementById($id, true);
		echo "<div id='msg_$target'></div>";
		echo renderFinalisation($type, $agreement, '',$target, $buttons);
    break;
	case 'list_search':
		if (Users::isSuperVisor()){
		//Return result to jTable
			$recs = Agreement::getInstance()->getAgreementsForSupervisorBySearchCriteria(true, $_GET["jtSorting"], $_GET["jtStartIndex"], $_GET["jtPageSize"]);
			$cnt = Agreement::getInstance()->getProjectAgreementsRowCount($GLOBALS['user']->uid,'');
		}
		else if(Users::isMentor()){
			$recs = Agreement::getInstance()->getAgreementsForMentorBySearchCriteria(true, $_GET["jtSorting"], $_GET["jtStartIndex"], $_GET["jtPageSize"]);
			$cnt = Agreement::getInstance()->getProjectAgreementsRowCount('',$GLOBALS['user']->uid);
		}
		jsonGoodResultJT($recs, $cnt);
	break;
	case 'render_project_for_id':
		$id = altSubValue($_POST, 'id');
		$target = altSubValue($_POST, 'target', '');
		$agreement = Agreement::getInstance()->getSingleAgreementById($id, true);
		echo getSingleAcceptedProjectView($agreement);
	break;
	case 'render_agreement_for_id':
		$id = altSubValue($_POST, 'id');
		$target = altSubValue($_POST, 'target', '');
		$agreement = Agreement::getInstance()->getSingleAgreementById($id, true);
		echo "<div id='admin_container' class='tabs_container'>";
		echo showAgreement($agreement);
		echo "</div>";
	break;
    case 'render_finalisation_for_id':
		$id = altSubValue($_POST, 'id');
		$target = altSubValue($_POST, 'target', '');
		$agreement = Agreement::getInstance()->getSingleAgreementById($id, true);
		echo "<div id='admin_container' class='tabs_container'>";
		echo showFinalisation($agreement);
		echo "</div>";
	break;
	default: echo "No such action: ".$_GET['action'];
}