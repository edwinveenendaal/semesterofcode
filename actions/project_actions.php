<?php
include('include.php');//Includes the necessary bootstrapping and the ajax functions
// module_load_include('php', 'vals_soc', 'includes/classes/AbstractEntity');
module_load_include('php', 'vals_soc', 'includes/classes/ThreadedComments');
module_load_include('php', 'vals_soc', 'includes/classes/Organisations');
module_load_include('php', 'vals_soc', 'includes/classes/Project');
module_load_include('php', 'vals_soc', 'includes/classes/Proposal');
module_load_include('php', 'vals_soc', 'includes/pages/projects');
module_load_include('php', 'vals_soc', 'includes/functions/ajax_functions');
module_load_include('php', 'vals_soc', 'includes/functions/render_functions');

$path_arr = explode('/', $_SERVER['HTTP_REFERER']);//we need to store in separate var as otherwise a warning is issued for passing non var by ref
$mine = ('mine' == (array_pop($path_arr)));//needed for save and delete

switch ($_GET['action']){
	case 'eval_projects':
		if (!Users::isAdmin()){
			echo jsonBadResult(t('You can only evaluate a project as admin.'));
			return;
		}
		initEvaluateProjectLayout();
	break;
	case 'approve':
		if (!Users::isAdmin()){
			echo jsonBadResult(t('You can only approve a project as admin.'));
			return;
		}
		//We could bulk it like this
		//UPDATE  `vals_vps2`.`soc_projects` SET state = 'open'  where state ='pending'
	break;
	case 'reject':
		if (!Users::isAdmin()){
			echo jsonBadResult(t('You can only reject a project as admin.'));
			return;
		}
	break;
	case 'project_page':
        $pid = getRequestVar('pid');
		initBrowseProjectLayout($pid);
	break;
	case 'mark':
		if (!(Users::isStudent())){
			echo jsonBadResult(t('You can only mark a project as student member of an institute.'));
			return;
		}
		$id = getRequestVar('id');
		$table = tableName('student_favourite');
		//TODO The deletion doesn't seem necessary as the mark button is replaced by an icon once marked before
		//it is harmless though
		$num_deleted = db_delete($table)
		->condition('pid', $id)
		->condition('uid', $GLOBALS['user']->uid)
		->execute();
		if ($num_deleted !== FALSE){
			$result = db_insert($table)
			->fields(array('pid'=> $id,
					'uid'=>$GLOBALS['user']->uid))
					->execute();
			if ($num_deleted == 0){//it is a new one, so one like more for the project
				$result = $result && db_update(tableName('project'))
					->condition('pid', $id)
					->expression('likes', 'likes + 1')
					->execute();
			}
		} else {
			$result = FALSE;
		}
		echo $result ? jsonGoodResult(TRUE, t('You have marked this project')) :
		jsonBadResult(t('Something went wrong with the project marking.'));
	break;
	case 'recommend':
		if (!(Users::isSuperVisor())){
			echo t('You can only rate a project as staff member of an institute.');
			return;
		}
		$mails = array();
		$id = getRequestVar('id');
		$mail = array('from' => $GLOBALS['user']->mail);
		$mail['body'] = t('Hello,')."\n\n".
				tt('I would like to recommend the following project to you: %1$s',
						_VALS_SOC_FULL_URL."/projects/browse?pid=$id."). "\n\n".
						t('Kind regards,')."\n\n".
						Users::getMyName();
		$mail['subject'] = t('Recommendation from your supervisor');
		$mail['plain'] = true;
		
		$email = str_replace(' ', '', getRequestVar('email'));
		
		//TODO: remove this later. FYI both smtp module and mail can handle comma separated mail recipient lists. If one of 
		//those is invalid, the others are sent, but as I thought I had to use vals_soc_send_emails_now for multiple messages
		//one of them is sent with invalid address and so the following buggy message (punctuation and content) is shown: 
		//'Invalid address: testingnonsensYou must provide at least one recipient email address. '
		//this is a bad message for a user sending a list of users one email. Better is to send one mail with multiple recipients
		//and let smtp or php mail sort out that one of those is incorrect. Last try now: 1. send one mail with smtp where one 
		//address is wrong, 2. sending with php mail and one incorrect address.
		//REsult: for 1, since there are other valid addresses: the invalid one is ignored completely: test: have one nonsens address only
		//That gives: the result true and no messages. 
		//Moreover: all the mail addressses are visible to all the recipients!
		//So we choose to send all emails apart and then to hide the messages from drupal and produce our own.
		//It is a bit inefficient, but ok. A valid email address in smtp is when: 
		
		$emails = explode(',', $email);
		if (count($emails) > 1){
			$no = 0;
			foreach ($emails as $email){
				if ($email) {
					$mails[] = $mail;
					$mails[$no]['to'] = $email;
					$no++;
				}
			}
		} else {
			$mails[] = $mail;
			$mails[0]['to'] = $email;
		}
		
// 		$mail['to'] = $email;//NEW
// 		$mails[] = $mail;//NEW
		module_load_include('inc', 'vals_soc', 'includes/module/vals_soc.mail');
		if (vals_soc_send_emails_now($mails)) {
			echo successDiv(t('You sent your recommendation(s)'));
		} else {
			echo errorDiv(t('Something wrong with sending your recommendation(s): ').getDrupalMessages('error'));
		}
		break;
	case 'rate':
		//do something
		if (!(Users::isSuperVisor())){
			echo t('You can only rate a project as supervisor.');
			return;
		}
		$rate = getRequestVar('rate');
		$id = getRequestVar('id');
		$table = tableName('supervisor_rate');
		$result = FALSE;
		$num_deleted = db_delete($table)
			->condition('pid', $id)
			->condition('uid', $GLOBALS['user']->uid)
			->execute();
		if ($num_deleted !== FALSE){
			$result = db_insert($table)
				->fields(array('pid'=> $id,
					'uid'=>$GLOBALS['user']->uid,
					'rate'=>$rate))
				->execute();
		}
		echo $result ? jsonGoodResult(TRUE, t('You have marked this project succesfully')) : 
			jsonBadResult(t('Something went wrong with the project rating.'));

		break;
	case 'list_search':
		try {
			$tags = null;
			if (isset($_POST['tags'])){
				$tags = $_POST['tags'];
			}
			$organisation = null;
			if (isset($_POST['organisation'])){
				$organisation = $_POST['organisation'];
			}
			$state = null;
			if (isset($_POST['state'])){
				$state = $_POST['state'];
			}
            $supervisor=null;
			if (isset($_POST['supervisor'])){
				$supervisor = $_POST['supervisor'];
			}
            
			$project_id = getRequestVar('pid', null);
			$favourites_only = getRequestVar('favourites', false);
			//Return result to jTable
			$jTableResult = array();
			$jTableResult['Result'] = "OK";
			if ($favourites_only){
				$jTableResult['Records'] = Project::getInstance()->getFavouriteProjects();
				$jTableResult['TotalRecordCount'] = count($jTableResult['Records']);
			} else {
				if ($project_id){
					$project = Project::getProjectById($project_id, false, PDO::FETCH_ASSOC, true);
					if ($project){
						$jTableResult['TotalRecordCount'] = 1;
						$jTableResult['Records'] = array($project);
					} else {
						$jTableResult['TotalRecordCount'] = 0;
						$jTableResult['Records'] = array();
					}
				} else {
                    //Since we might get more results than fit on the page size
                    //we requested, the total number can be higher then the actual
                    //retrieved results. The reason we do not page the results in 
                    //php here, but let mysql handle the paging, is that sorting 
                    //might be involved
					/* If we are interested in the query, adept the following function and perform the following lines instead
                     * list($jTableResult['TotalRecordCount'], $jTableResult['q_string']) = 
                            Project::getInstance()->getProjectsRowCountBySearchCriteria(
                                $tags, $organisation,$state, $supervisor);
                     */
                    $jTableResult['TotalRecordCount'] = 
                        Project::getInstance()->getProjectsRowCountBySearchCriteria(
                            $tags, $organisation,$state, $supervisor);
					$jTableResult['Records'] = 
                        Project::getInstance()->getProjectsBySearchCriteria(
                            $tags, $organisation, $state, $supervisor, $_GET["jtSorting"],
                            $_GET["jtStartIndex"], $_GET["jtPageSize"]);
				}
			}
			//Save it for navigation
			$_SESSION['lists']['projects'] = array();
			$_SESSION['lists']['projects']['nr'] = count($jTableResult['Records']);
			$_SESSION['lists']['projects']['list'] = $jTableResult['Records'];
			$_SESSION['lists']['projects']['current'] = -1;
            //Push the result on the ajax request channel
			print json_encode($jTableResult);
		}
		catch(Exception $ex){
			//Return error message
			jsonBadResultJT($ex->getMessage());
		}
	break;
	case 'list_search_proposal_count':
		$organisation=null;
		$owner_id = null;
		if (isset($_POST['organisation']) && $_POST['organisation']) {
            $organisation = $_POST['organisation'];
        }
        if (isset($_GET['mine_only']) && $_GET['mine_only']) {
            $owner_id = Users::getMyId();
        }
        //Return result to jTable
		$recs = Project::getInstance()->getProjectsAndProposalCountByCriteria(
			$organisation, $owner_id, $_GET["jtSorting"], $_GET["jtStartIndex"], $_GET["jtPageSize"]);
		$cnt = Project::getInstance()->getProjectsAndProposalCountByCriteriaRowCount($organisation,
            $owner_id);
        jsonGoodResultJT($recs, $cnt);
	break;
	case 'list':
		try {
			$target = getRequestVar('target', null);
			$inline = getRequestVar('inline', false);
			$org_id = getRequestVar('org', null);
			$mine   = getRequestVar('mine', false);
			$organisations = $org_id ?  array($org_id) : null;
			echo renderProjects($organisations, '', $target, $inline, true, $mine);
		}
		catch(Exception $ex){
			//Return error message
			errorDiv($ex->getMessage());
		}
		break;
	case 'project_detail':
		$project_id = getRequestVar('project_id', null);
		$project = null;
		if($project_id){
			try {
				if (isset($_SESSION['lists']['projects']) && $_SESSION['lists']['projects']){
					$current = getRequestVar('current',-1);
					if ($current >=0) {
						$project = $_SESSION['lists']['projects']['list'][$current];
					} else {
						$current = 0;
						foreach($_SESSION['lists']['projects']['list'] as $project_from_list){
							if ($project_from_list->pid == $project_id){
								$project = objectToArray($project_from_list);
								$_SESSION['lists']['projects']['list']['current'] = $current;
								$next_nr = $current < ($_SESSION['lists']['projects']['nr'] -1) ? $current + 1 : FALSE;
								$next_pid = $next_nr ? $_SESSION['lists']['projects']['list'][$next_nr]->pid : FALSE;
								$prev_nr = ($current > 0) ? ($current - 1) : FALSE;
								$prev_pid = ($prev_nr !== FALSE) ? $_SESSION['lists']['projects']['list'][$prev_nr]->pid : FALSE;
								$project['nav'] = array(
									'next_pid' =>  $next_pid,
									'next_nr' => $next_nr,
									'prev_pid' =>  $prev_pid,
									'prev_nr' => $prev_nr,
								);
								break;
							}
							$current++;
						}
					}
				}
				
				//It might be that the project is in draft and is not returned by the browse and so it is not
				//present in the session lists 
				if (!$project){
					 $project = Project::getProjectById($project_id, false, PDO::FETCH_ASSOC, true);
				}
				$my_id = Users::getMyId();
				if (($project['state'] == 'draft') && 
					!(
						($project['mentor_id'] == $my_id) || 
						($project['owner_id'] == $my_id) ||
						Users::isAdmin() ||
						(Groups::isAssociate(_PROJECT_OBJ, $project_id))))
				{
						jsonBadResult(t('You cannot view this proposal. It is in draft state.'));
						return;
				}
					
				
				if (Users::isSuperVisor()){
					$project['rate'] = Project::getRating($project_id, $my_id);
				} else {
					$project['rate'] = -2;
					if (Users::isStudent()){
						$table = tableName('student_favourite');
						$favourite = db_select($table)->fields($table)
						->condition('pid', $project_id)
						->condition('uid', $my_id)
						->execute()->rowCount();
						$project['favourite'] = ($favourite !=0);
						//Count the views of the students
						$result = db_update(tableName('project'))
							->condition('pid', $project_id)
							->fields(array('views'=> $project['views'] + 1))
							->execute();
					}
				}
				jsonGoodResult($project);
			} catch (Exception $e){
				jsonBadResult(t('Could not get details of project').(_DEBUG ? $e->getMessage(): ""));
			}
		}
		else{
			jsonBadResult( t("No valid project identifier submitted!"));
		}
	break;
	case 'view':
		$type = _PROJECT_OBJ;
		$id = altSubValue($_POST, 'id');
		$target = altSubValue($_POST, 'target', '');
		$inline = getRequestVar('inline', FALSE);
		if (! ($id && $type && $target)){
			die(t('There are missing arguments. Please inform the administrator of this mistake.'));
		}
		$project = Project::getProjectById($id, TRUE);
		if (! $project){
			echo t('The project cannot be found');
		} else {
			echo "<div id='msg_$target'></div>";
            echo renderProject($project, $target, $inline);
		}
		break;
	case 'add':
		$target = altSubValue($_POST, 'target');
		$type = altSubValue($_POST, 'type');
		$org  = altSubValue($_GET, 'org');
		echo '<h2>'.t('Add new project').'</h2>';
		echo "<div id='msg_$target'></div>"; 
		$form = drupal_get_form("vals_soc_project_form", '', $target, $org);
		renderForm($form, $target);
		
	break;
	case 'save':
		$type = altSubValue($_POST, 'type', '');
		$id = altSubValue($_POST, 'id', '');
		$draft = altSubValue($_POST, 'draft', false);
 		$properties = Project::getInstance()->filterPostLite(Project::getInstance()->getKeylessFields(), $_POST);
        $properties['state'] = ($draft ? 'draft' : _VALS_INITIAL_PROJECT_STATE);
        $properties['available'] = ($properties['available'] ? 1 : 0);
        if ($properties['available']){
            $properties['begin'] = date_timestamp_get(date_create_from_format('d-m-Y', $properties['begin']));
            $properties['end'] = date_timestamp_get(date_create_from_format('d-m-Y', $properties['end']));
        } else {
            $properties['begin'] = NULL;
            $properties['end'] = NULL;
        }
        
		if (!$id){
			$new = $properties['org_id'];
			$result = Project::getInstance()->addProject($properties);
		} else {
			$new = false;
			$result = Project::getInstance()->changeProject($properties, $id);
		}
		if ($result){
			echo json_encode(array(
					'result' => TRUE,
					'id' => $id,
					'type'=> $type,
					'new_tab' => !$id ? $properties['org_id'] : 0,//so we can distinguish which tab to open
					'extra' => ($mine? array( 'mine' => 1) : ''),
					'msg'=>
                        ($id ? tt('You succesfully changed the data of your %1$s', t_type($type)):
                               tt('You succesfully added your %1$s', t_type($type))).
                        (_DEBUG ? showDrupalMessages(): '')
			));
		} else {
			echo jsonBadResult();
		}
	break;
	case 'show':
		$show_last = altSubValue($_POST, 'new_tab', false);
		$owner_only = altSubValue($_POST, 'mine', false);
		showProjectPage($show_last, $owner_only);
	break;
	case 'edit':
		$type = altSubValue($_POST, 'type', '');
		$id = altSubValue($_POST, 'id', '');
		$target = altSubValue($_POST, 'target', '');

		$obj = Project::getProjectById($id, FALSE, NULL);
		if (!$obj){
			echo t('The project could not be found');
			return;
		}
		
		// See http://drupal.stackexchange.com/questions/98592/ajax-processed-not-added-on-a-form-inside-a-custom-callback-my-module-deliver
		// for additions below
		$originalPath = false;
		if(isset($_POST['path'])){
			$originalPath = $_POST['path'];
		}
		unset($_POST);
		$form = drupal_get_form("vals_soc_project_form", $obj, $target);
		if($originalPath){
			$form['#action'] = url($originalPath);
		}
		// Process the submit button which uses ajax
		//$form['submit'] = ajax_pre_render_element($form['submit']);
		// Build renderable array
// 		$build = array(
// 				'form' => $form,
// 				'#attached' => $form['submit']['#attached'], // This will attach all needed JS behaviors onto the page
// 		);
		renderForm($form, $target);

	    break;
    case 'delete':
    	$type = altSubValue($_POST, 'type', '');
    	$id = altSubValue($_POST, 'id', '');
    	if (! isValidOrganisationType($type)) {
    		echo jsonBadResult(t('There is no such type we can delete'));
    	} elseif (count(Proposal::getProposalsPerProject($id))) {
    		echo jsonBadResult(t('You cannot delte the project; there are already students working on a proposal for this project. You can still edit it though.'));
    	} else {
    		$result = Groups::removeGroup($type, $id);
    		ThreadedComments::getInstance()->removethreadsForEntity($id, $type);
    		echo $result ? jsonGoodResult(true, '', array('extra'=> ($mine? array( 'mine' =>1) :''))) : jsonBadResult();
    	}
    break;
	default: echo "No such action: ".$_GET['action'];
}