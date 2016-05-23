<?php
include('include.php');//Includes the necessary bootstrapping and the ajax functions
// module_load_include('php', 'vals_soc', 'includes/classes/AbstractEntity');

module_load_include('php', 'vals_soc', 'includes/classes/Groups');
module_load_include('php', 'vals_soc', 'includes/classes/Project');
module_load_include('php', 'vals_soc', 'includes/pages/proposals');
module_load_include('php', 'vals_soc', 'includes/classes/Institutes');

switch ($_GET['action']){
	case 'list':
		try{
			$instName=null;
			$inst_id = getRequestVar('instid', null);
			if(isset($_POST['iname'])){
				$instName = $_POST['iname'];
			}

			//Return result to jTable
			$jTableResult = array();
			$jTableResult['Result'] = "OK";
			if ($inst_id){
				$institutions = Institutes::getInstance()->getInstituteById($inst_id);
				$jTableResult['TotalRecordCount'] = count($institutions);
				$jTableResult['Records'] = $institutions;
				
			} else {
				$jTableResult['TotalRecordCount'] = Institutes::getInstance()->getInstitutesRowCountBySearchCriteria($instName);
				$jTableResult['Records'] = Institutes::getInstance()->getInstitutesBySearchCriteria($instName,
						 $_GET["jtSorting"], $_GET["jtStartIndex"], $_GET["jtPageSize"]);
			}
			print json_encode($jTableResult);
		}
		catch(Exception $ex){
			//Return error message
			$jTableResult = array();
			$jTableResult['Result'] = "ERROR";
			$jTableResult['Message'] = $ex->getMessage();
			print json_encode($jTableResult);
		}
	break;
	case 'detail':
		if(isset($_GET['instid'])){
			try {
				$institutions = Institutes::getInstance()->getInstituteById($_GET['instid']);
				echo ($institutions ? jsonGoodResult($institutions[0]) : jsonBadResult(t('Could not find the institution')));
			} catch (Exception $e){
				echo jsonBadResult($e->getMessage());
			}
		}
		else{
			echo jsonBadResult( t("No institution identifier submitted!"));
		}
	break;
	case 'list_search_proposal_count_student':
		$group=null;
		if(isset($_POST['group']) && $_POST['group']){
			$group = $_POST['group'];
		}
		if(isset($_GET['mine_only']) && $_GET['mine_only']){
			$mine_only = true;
		}
		else{
			$mine_only = false;
		}
		//Return result to jTable
		$recs = Project::getInstance()->getStudentsAndProposalCountByCriteria(
				$group, $_GET["jtSorting"], $_GET["jtStartIndex"], $_GET["jtPageSize"], $mine_only);
		$cnt = Project::getInstance()->getStudentsAndProposalCountByCriteriaRowCount($group, $mine_only);
		
		jsonGoodResultJT($recs, $cnt);
	break;
	case 'render_proposals_for_student':
		if(isset($_POST['mine_only']) && $_POST['mine_only']){
			$mine_only = $_POST['mine_only'] === 'true' ? true: false;
		}
		if(isset($_POST['id']) && $_POST['id']){
			echo showProposalsForStudent($_POST['id'], $mine_only);
		}else{
			echo "Unable to find proposals without student identifier";
		}
	break;
	default: echo "No such action: ".$_GET['action'];
}