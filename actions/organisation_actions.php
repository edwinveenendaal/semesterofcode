<?php
include('include.php');//Includes the necessary bootstrapping and the ajax functions
// module_load_include('php', 'vals_soc', 'includes/classes/AbstractEntity');
// module_load_include('php', 'vals_soc', 'includes/classes/Groups');
module_load_include('php', 'vals_soc', 'includes/classes/Organisations');
switch ($_GET['action']){
	case 'list_organisations':
		try{
			$orgName=null;
			$org_id = getRequestVar('orgid', null);
			if(isset($_POST['oname'])){
				$orgName = $_POST['oname'];
			}

			//Return result to jTable
			$jTableResult = array();
			$jTableResult['Result'] = "OK";
			if ($org_id){
				$organisations = Organisations::getInstance()->getOrganisationById($org_id);
				$jTableResult['TotalRecordCount'] = count($organisations);
				$jTableResult['Records'] = $organisations;
				
			} else {
				$jTableResult['TotalRecordCount'] = Organisations::getInstance()->getOrganisationsRowCountBySearchCriteria($orgName);
				$jTableResult['Records'] = Organisations::getInstance()->getOrganisationsBySearchCriteria($orgName,
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
	case 'organisation_detail':
		if(isset($_GET['orgid'])){
			try {
				$organisations = Organisations::getInstance()->getOrganisationById($_GET['orgid']);
				echo ($organisations ? jsonGoodResult($organisations[0]) : jsonBadResult(t('Could not find the organisation')));
			} catch (Exception $e){
				echo jsonBadResult($e->getMessage());
			}
		}
		else{
			echo jsonBadResult( t("No organisation identifier submitted!"));
		}
	break;
	default: echo "No such action: ".$_GET['action'];
}