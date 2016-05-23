<?php
include('include.php');//Includes the necessary bootstrapping and the ajax functions
include(_VALS_SOC_ROOT.'/includes/classes/Agreement.php');
include(_VALS_SOC_ROOT.'/includes/classes/Institutes.php');
include(_VALS_SOC_ROOT.'/includes/classes/Organisations.php');
include(_VALS_SOC_ROOT.'/includes/classes/Proposal.php');
include(_VALS_SOC_ROOT.'/includes/classes/Project.php');
module_load_include('php', 'vals_soc', 'includes/classes/ThreadedComments');
module_load_include('php', 'vals_soc', 'includes/pages/proposals');
module_load_include('php', 'vals_soc', 'includes/pages/projectoffers');

$apply_proposals = vals_soc_access_check('dashboard/projects/apply') ? 1 : 0;
$browse_proposals = vals_soc_access_check('dashboard/proposals/browse') ? 1 : 0;
$is_student = (Users::isOfType(_STUDENT_TYPE));

switch ($_GET['action']){
	case 'proposal_page':
		initBrowseProposalsLayout();
	break;
	case 'myproposal_page':
		echo "<div id='admin_container' class='tabs_container'>";
		echo showMyProposalPage();
		echo "</div>";
	break;
	case 'render_proposals_for_id':
		if(isset($_POST['mine_only']) && $_POST['mine_only']){
			$mine_only = $_POST['mine_only'] === 'true' ? true: false;
		}
		if(isset($_POST['id']) && $_POST['id']){
			echo showProposalsForProject($_POST['id'], $mine_only);
		}else{
			echo "Unable to find proposals without project identifier";
		}
	break;
	case 'proposal_form':
		if (!vals_soc_access_check('dashboard/projects/apply')) {
			echo errorDiv(t('You cannot apply for projects'));
			break;
		}
		$target = altSubValue( $_POST, 'target');
		$project_id = altSubValue( $_POST, 'id');
		$proposal_id = altSubValue( $_POST, 'proposalid', 0);
		if (! Users::isOfType(_STUDENT_TYPE, $GLOBALS['user']->uid)){
			if (_VALS_TEST_UI_ONLY){
				//TODO: this kind of testing should go soon
				echo "!! Since you are an admin, you can test a bit. We test with user 31 under the condition that _VALS_TEST_UI_ONLY is true.";
				$owner_id = 31;
			} else {
				echo errorDiv(t('Only students can submit proposals'));
				return;
			}
		} else {
			$owner_id = Users::getMyId();
		}
		$project = Project::getProjectById($project_id);
		$student_details = Users::getStudentDetails($owner_id);
	
		if (!$project){
			echo errorDiv(t('This project could not be found'));
			return;
		}
		if ($student_details){
			if (!$proposal_id){
				$proposals = Proposal::getInstance()->getProposalsPerProject($project_id, $owner_id);
				if (count($proposals) > 1){
					//This case should not occur or very little, once we catch the case of having already a version
					echo '<span style="color:orange;">'.
							t('Be aware that you have more than one proposal for this project. You better delete one of them.').
							'</span>';
				}
				$proposal = $proposals ? $proposals[0] : null;
			} else {
				$proposal = $proposal_id ? Proposal::getInstance()->getProposalById($proposal_id): null;
			}
	
			echo "<div id='edit_proposal' class='edit_proposal' style='border-style: solid;border-width: 1px; border-color:	rgb(153,​ 217,​ 234);padding:10px;'>
			<h2>".tt('Create proposal for :"%1$s"',$project['title'])."</h2>";
			echo '<h3>'.t('Student details').'</h3>';
			echo "<div id='student_details' style='color:blue'>".
					sprintf('%1$s: %2$s<br/>%3$s: %4$s<br/>%5$s: %6$s<br/>%7$s: %8$s', t('Name'), $student_details->student_name,
							t('Email'), $student_details->student_mail, t('Institute'), $student_details->institute_name,
							t('First supervisor'), $student_details->supervisor_name)
							//"<br/>Group: ".$student_details->group_name.
			."</div><hr>";
			$possible_supervisors = Project::getInterestedSupervisors($project_id);
			$form = drupal_get_form('vals_soc_proposal_form', $proposal, $target, $project_id, $possible_supervisors);
			renderForm($form, $target);
			echo "</div>";
		} else {
			echo errorDiv(t('Not all details could be retrieved for you. You might not have been put in a student group. Contact your lecturer please.'));
		}
		break;
	case 'list':
		$target = altSubValue($_POST, 'target');
        $state  = altSubValue( $_GET, 'state');
		if($state){
            $with_details = TRUE;
			$proposals = Proposal::getProposalsPerOrganisation('','',$state, $with_details);
			echo renderProposals($state, $proposals, $target, $with_details);
		} else {
			echo "No state passed ";
		}
		break;
	case 'list_proposals':
		try{
			$student=null;
			if(isset($_POST['student']) && $_POST['student']){
				$student = $_POST['student'];
				
				if ($is_student && $student != Users::getMyId()){
					throw new Exception((t('You can only see your own proposals!')));
				}
			} else {
				if ($is_student){
					throw new Exception((t('You can only see your own proposals!')));
				}
			}
			$institute=null;
			if(isset($_POST['institute']) && $_POST['institute']){
				$institute = $_POST['institute'];
			}
			$organisation=null;
			if(isset($_POST['organisation']) && $_POST['organisation']){
				$organisation = $_POST['organisation'];
			}
			$project=null;
			if(isset($_POST['project']) && $_POST['project']){
				$project = $_POST['project'];
			}
			//Return result to jTable
			$cnt = Proposal::getInstance()->getProposalsRowCountBySearchCriteria(
					$student, $institute, $organisation, $project);
			$recs = $cnt ? 
						Proposal::getInstance()->getProposalsBySearchCriteria(
							$student, $institute, $organisation, $project, $_GET["jtSorting"], $_GET["jtStartIndex"],
							$_GET["jtPageSize"]) :
						array();
			
			jsonGoodResultJT($recs, $cnt);
		}
		catch(Exception $ex){
			//Return error message
			jsonBadResultJT($ex->getMessage());
		}
	break;
	case 'proposal_detail':
		global $user;
		$proposal_id = getRequestVar('proposal_id', null);
		//TODO bepaal hier 
		if ($proposal_id){
			if (! ($browse_proposals || Groups::isOwner(_PROPOSAL_OBJ, $proposal_id) )){
				jsonBadResult(t('You can only see your own proposals!'));
			} else {
				$proposal = Proposal::getInstance()->getProposalById($proposal_id, true);
                $proposal->proposal_count = Project::getProjectsProposalCount($proposal->pid);
				$project_id = $proposal->pid;
				// is this person the project owner?
				$is_project_owner = Groups::isOwner(_PROJECT_OBJ, $project_id);
				$proposal->is_project_owner = $is_project_owner;
				$proposal->is_project_mentor = true;
				if ($user->uid != $proposal->mentor_id){
					$proposal->is_project_mentor = false;
				}
				jsonGoodResult($proposal);
			}
		} else {
			jsonBadResult(t('No proposal identifier submitted!'));
		}
	break;
	case 'edit':
		$proposal_id = getRequestVar('proposal_id', null, 'post');
		$result_format = getRequestVar('format', 'json', 'post');
		if($proposal_id){
			if (! ($browse_proposals || Groups::isOwner(_PROPOSAL_OBJ, $proposal_id) )){
				jsonBadResult(t('You can only see your own proposals!'));
			} else {
				$target = altSubValue($_POST, 'target');
				$proposal = Proposal::getInstance()->getProposalById($proposal_id, true);
				$project_id = $proposal->pid;
				$project = Project::getProjectById($project_id);
				$possible_supervisors = Project::getInterestedSupervisors($project_id);
				$form = drupal_get_form('vals_soc_proposal_form', $proposal, $target, $project_id, $possible_supervisors);
				if ($form){
					$prefix_form = "<div>".tt('<b>Project</b> <i>%1$s</i>',$project['title'])."</div>";
					if ($result_format == 'json') {
						jsonGoodResult($prefix_form.renderForm($form, $target, true));
					} else {
						echo $prefix_form;
						renderForm($form, $target);
					}
				} else {
					if ($result_format == 'json') {
						jsonBadResult();
					} else {
						echo errorDiv(getDrupalMessages('error', true));
					}
				}
			}
		} else{
			jsonBadResult(t('No proposal identifier submitted!'));
		}
	break;
	case 'delete':
		$proposal_id = getRequestVar('proposal_id', null, 'post');
		$target = getRequestVar('target', 'our_content', 'post');
		if($proposal_id){
			$is_modal = ($target == 'tab_edit');
			//we need the container where the result is bad and we show an error msg
			$container =  'our_content';//$is_modal ? 'admin_container' : 'our_content';
			$before = $is_modal ? 'TableContainer' : 'toc' ;
			$args = array('id' => $proposal_id, 'before'=> $before, 'target'=> $container, 'replace_target'=> ! $is_modal);
			$proposal_nr = Proposal::getInstance()->getProposalById($proposal_id);
			if (!$proposal_nr){
				jsonBadResult(t('This proposal was already deleted!'), $args);
				return;
			}
			$title = altPropertyValue($proposal_nr, 'title');
			$state = altPropertyValue($proposal_nr, 'state');
			if (! Groups::isOwner(_PROPOSAL_OBJ, $proposal_id)){
				jsonBadResult(t('You can only delete your own proposals!'), $args);
			} elseif ($state == 'published') {
				jsonBadResult(t('We could not remove your proposal: It has already been published.'), $args);
			} else {
				$num_deleted = db_delete(tableName(_PROPOSAL_OBJ))
					->condition(AbstractEntity::keyField(_PROPOSAL_OBJ), $proposal_id)
					->execute();
				if ($num_deleted){
					// junk the proposal comments too
					ThreadedComments::getInstance()->removethreadsForEntity($proposal_id, _PROPOSAL_OBJ);
					//$args['before'] = '';
					jsonGoodResult(TRUE, tt('You have removed the proposal %1$s', $title), $args);
				} else {
					jsonBadResult(t('We could not remove your proposal'), $args);
				}
			}
		} else{
			jsonBadResult(t('No proposal identifier submitted!'), $args);
		}
	break;
	case 'save_public':
		// no break so that the request filters down to 'save'
		$is_public = true;
	case 'submit':
		// no break so that the request filters down to 'save'
		$is_final = true;
		$target = altSubValue($_POST, 'target', '');
	case 'save':
		if (!$apply_proposals){
			jsonBadResult(t('Student application period is not currently open'));
		} else {
			$id = altSubValue($_POST, 'id', '');
			$project_id = altSubValue($_POST, 'project_id', '');
			$project = Project::getProjectById($project_id);
			$properties = Proposal::filterPost($_POST);
			if (isset($is_public) && $is_public){
				$properties['state'] = 'open';
				$is_final = false;
			}
			if (isset($is_final) && $is_final){
				$properties['state'] = 'published';
			}
			if (!$id){
				$new = TRUE;
				$id = $result = Proposal::insertProposal($properties, $project_id);
			} else {
				$new = FALSE;
				if (!Groups::isOwner(_PROPOSAL_OBJ, $id)){
					drupal_set_message(t('You are not the owner of this proposal'), 'error');
					$result = null;
				} else {
					//If there was no supervisor chosen, at least maintain the orginal one, rather than leave it orphaned
					if($properties['supervisor_id'] == 0){
						if ($original_supervisor = altSubValue($_POST, 'original_supervisor_id', '')) {
							$properties['supervisor_id'] = $original_supervisor;
						}
					}
					$result = Proposal::updateProposal($properties, $id);
				}
			}
		
			if ($result){
				// Send out emails to mentor/supervisor once new proposal published
				// get either the existing proposal key
				// or the newly inserted proposal key
				if (is_bool($result)){
					//already existed
					$existed = true;
					$key = $id;
				} else {
					/// newly inserted
					$existed = false;
					$key = $result;
				}
				try {
					$props = Proposal::getInstance()->getProposalById($key, true);
					module_load_include('inc', 'vals_soc', 'includes/module/vals_soc.mail');
					notify_mentor_and_supervisor_of_proposal_update($props, $existed);
				} catch (Exception $e) {
					// Logged higher up or log this here somehow? TODO
				}
				
				if (isset($is_final) && $is_final){
					echo json_encode(array(
							'result'=>'OK',
							'id' => $id,
							'target' => $target,
							'msg'=>tt('You succesfully submitted your proposal for %1$s', $project['title']).
							(_DEBUG ? showDrupalMessages() : '')
					));
				} else {
					$version = ($properties['state'] == 'open') ? t('public') : t('draft');
					echo json_encode(array(
							'result'=>'OK',
							'id' => $id,
							//'type'=> $type,
							'msg'=>
							($new ?
									tt('You succesfully saved a (%2$s) version of your proposal for %1$s', $project['title'], $version):
									tt('You succesfully changed the (%2$s) version of your proposal for %1$s', $project['title'], $version)
							).
							(_DEBUG ? showDrupalMessages() : '')
					));
				}
			} else {
				echo jsonBadResult();
			}
		}
	break;
	case 'reject_form':
		$target = getRequestVar('target');
		renderForm(drupal_get_form('vals_soc_reject_form', getRequestVar('id', 0), $target), $target);
	break;
	case 'reject':
		$id = getRequestVar('id', 0, 'post');
		$reason = getRequestVar('reason', '', 'post');
		$rationale = getRequestVar('rationale', '', 'post');
		try {
			$good_result = t('You rejected this proposal').'<script>hideOtherDivsAfterProposalReject('.$id.')</script>';
			$result = Proposal::getInstance()->rejectProposal($id, $reason, $rationale);
			if ($result){
				$props = Proposal::getInstance()->getProposalById($id, true);
				module_load_include('inc', 'vals_soc', 'includes/module/vals_soc.mail');
				notify_student_and_supervisor_of_proposal_rejection_by_mentor($props);
				echo jsonGoodResult(true, $good_result);
			}else{
				echo jsonBadResult(t('You tried to reject this proposal, but it failed'));
			}
		} catch (Exception $e){
			jsonBadResult(t('Something went wrong in the database '. (_DEBUG ? $e->getMessage():'')));
		}
	break;
	case 'view':
		$proposal_id = getRequestVar('id', 0, 'post');
		$target = getRequestVar('target', 'admin_container', 'post');
		if($proposal_id){
			//$is_modal = ($target !== 'our_content');
			//this is the case where the result is bad and we show an error msg
			//$container =  $is_modal ? 'modal-content' : 'our_content';
			//$before = 'toc' ;
			//$args = array('id' => $proposal_id, 'before'=> $before, 'target'=> $container, 'replace_target'=> true);
			$proposal = Proposal::getInstance()->getProposalById($proposal_id, TRUE);
			if (!$proposal){
				echo errorDiv(t('This proposal does not seem to exist!'));
				return;
			}
			if (Users::isStudent() && ! Groups::isOwner(_PROPOSAL_OBJ, $proposal_id)){
				echo errorDiv(t('You can only view your own proposals!'));
			} else {
				//TODO: find out whether we use the proposal view only in the my proposals and if not whether this 
				//matters: non owners have no right to delete for example and so no reason to do a followup action
				echo renderProposal($proposal, $target, 'myproposal_page');
			}
		} else {
			echo errorDiv(t('No proposal identifier submitted!'));
		}
	break;
	
	case 'mark_proposal':
		$proposal_id = getRequestVar('proposal_id', 0, 'post');
		$project_id = getRequestVar('project_id', 0, 'post');
		$is_final = getRequestVar('is_final', 0, 'post');
		 
		if (!$project_id){
			echo t('The project could not be found');
			return;
		}
		if (!$proposal_id){
			echo t('The proposal could not be found');
			return;
		}
		if (!$is_final){
			$is_final = 0;
		}
		 
		// Get the projects current proposal id and state (if set)
		$project = Project::getProjectById($project_id, FALSE, NULL);
		$old_proposal = $project->proposal_id;// probably dont need this now
		$was_selected = $project->selected;
		 
		 
		// only allow project owner (or assigned mentor) to update its selected & proposal_id fields
		//if(!Groups::isOwner('project', $project_id) && $project->mentor_id != $GLOBALS['user']->uid){
		if(!Groups::isOwner('project', $project_id)){
			echo t('Only the project owner or mentor can update its proposal status.');
			return;
		}
		 
		$selected_prev_set = ($was_selected == 1);
	
		if(!$selected_prev_set){
			// update the project
			$props['proposal_id'] = $proposal_id;
			$props['selected'] = $is_final;
			if ($is_final){
				$props['state']= 'preselected';
			}
			$result = Project::changeProject($props, $project_id);
			//send message back giving status & success message
			if ($result){
				// fire our emails
				$all_proposals_for_this_project = Proposal::getProposalsPerProject($project_id, null, true);
				module_load_include('inc', 'vals_soc', 'includes/module/vals_soc.mail');
				notify_students_and_supervisors_of_project_status_update($all_proposals_for_this_project, $proposal_id, $is_final);
				echo t('Changes successfully made.');
			}
			else{
				echo t('There was a problem updating your project preferences.');
			}
		}
		else{
			// send message back saying mentor has already made his decision & can't change it
			echo t('You already have chosen a final proposal for this project, you cannot change it now, unless the students chooses another offer.');
		}
	break;
	case 'list_my_offers':
		try{
			if(!(Users::isStudent())){
				echo jsonBadResult(t('Only students can accept project offers'));
				return;
			}
			$student = $GLOBALS['user']->uid;
			
			$organisation=null;
			if(isset($_POST['organisation']) && $_POST['organisation']){
				$organisation = $_POST['organisation'];
			}
			$cnt = Proposal::getInstance()->getProposalsRowCountBySearchCriteria(
					$student, null, $organisation, null, true);
			$recs = $cnt ?
			Proposal::getInstance()->getProposalsBySearchCriteria(
					$student, null, $organisation, null, $_GET["jtSorting"], $_GET["jtStartIndex"],
					$_GET["jtPageSize"], true) :
					array();
			jsonGoodResultJT($recs, $cnt);
		}
		catch(Exception $ex){
			//Return error message
			jsonBadResultJT($ex->getMessage());
		}
		break;
	case 'accept_proposal_offer':
		if(!(Users::isStudent())){
			echo jsonBadResult(t('Only students can accept project offers'));
			return;
		}
		$student = $GLOBALS['user']->uid;
		if(Agreement::getInstance()->getSingleStudentsAgreement()){
			echo t('You have already accepted a project offer');
			return;
		}
		if(isset($_POST['proposal_id']) && $_POST['proposal_id'] && isset($_POST['project_id']) && $_POST['project_id']){
			$proposal_id = $_POST['proposal_id'];
            
			if(Groups::isOwner('proposal', $proposal_id)){
				module_load_include('inc', 'vals_soc', 'includes/module/vals_soc.mail');
				// get ALL my proposals
				$all_my_proposals = Proposal::getInstance()->getProposalsBySearchCriteria($student, '', '', '', '', 0, 1000);
				foreach ($all_my_proposals as $my_proposal){
                    //From all my proposals this is the one I accept. Notify 
                    //all stakeholders of this decision
					if ($my_proposal->proposal_id == $proposal_id){
						$project_id = $my_proposal->pid;
						// next find all the other proposals for this project
						$all_proposals_for_this_project = Proposal::getInstance()->getProposalsPerProject($project_id, '', true); // TODO - may need to set details flag here
						foreach ($all_proposals_for_this_project as $single_proposal_for_accepted_project){
							if ($single_proposal_for_accepted_project->proposal_id == $proposal_id){
								//email SUCCESSFUL (student, supervisor, mentor) that this project has now been accepted by this student
								notify_all_of_project_offer_acceptance($single_proposal_for_accepted_project, $proposal_id, true);
								$props = array('state' => 'accepted');//set this proposal to 'accepted'
                                Proposal::getInstance()->updateProposal($props, $proposal_id);
								$props['state'] = 'active'; //set the project to 'active'
								Project::getInstance()->changeProject($props, $project_id);
							}
							else{
								if($single_proposal_for_accepted_project->state != 'rejected'){
									//email UNSUCCESSFUL proposal (student & supervisor) that this project has now been accepted by another student
									notify_all_of_project_offer_acceptance($single_proposal_for_accepted_project, $proposal_id, false);
									$props = array();
									$props['state'] = 'archived'; // set these to archived in case we need to separate later between auto rejected & manually rejected
									Proposal::getInstance()->updateProposal($props, $single_proposal_for_accepted_project->proposal_id);//uncomment to set this after testing **************
								}
							}
						}
					} else { 
                        // this.proposal =!= accepted proposal
                        // any other proposals by this student not accepted, so 
                        // projects are freed. 
						if($my_proposal->state != 'rejected'){
							$props = array();
							$props['state'] = 'archived'; // set these to archived in case we need to separate later between auto rejected & manually rejected
							Proposal::getInstance()->updateProposal($props, $my_proposal->proposal_id);//uncomment to set this after testing **************
						}
						$project_id = $my_proposal->pid;
						$all_proposals_for_this_project = Proposal::getInstance()->getProposalsPerProject($project_id, '', true); // TODO - may need to set details flag here
						//Now administer and notify changed situation for all proposals attached
                        //to the project this proposal was for
                        foreach ($all_proposals_for_this_project as $single_proposal_for_this_project){
							if ($single_proposal_for_this_project->owner_id == $student && 
								$single_proposal_for_this_project->proposal_id == $single_proposal_for_this_project->pr_proposal_id){
								$update_props = array();
								$update_props['proposal_id'] = 0;
								if ($single_proposal_for_this_project->selected == "0"){ //(means its an interim)
									// email mentor only - withdrawn PREFERRED INTERIM
									notify_all_of_project_offer_rejection($single_proposal_for_this_project, $proposal_id, true);
								} else { // (means its an offer)
									$update_props['selected'] = 0;
									// email (mentor) - rejected OFFER - project is therefore reopened and he should choose another proposal
									// email this proposal (student & supervisor) to say that the project has reopended and the mentor can choose another, possibly theirs
									notify_all_of_project_offer_rejection($single_proposal_for_this_project, $proposal_id, false);
								}
								//Proposal::getInstance()->updateProposal($update_props, $proposal_id); //uncomment to set this after testing *********
								Project::getInstance()->changeProject($update_props, $project_id); //uncomment to set this after testing *********
							}
							// else - the proposal was owned by this student but had not been either set as interim OR OFFER - so do nothing
							// as we have now set this proposal.state as 'archived' so the mentor cannot choose it in the UI. (TODO - implement that bit)
						}
					}
					
				}

				// next create the initial agreement entity in the db
				$agreement = Agreement::getInstance()->insertAgreement(array('proposal_id' => $proposal_id));
				echo getAcceptedProjectResponse();
			}
			else{
				echo t('Only the proposal owner can accept this project offer.');
			}
		}
		else{
			echo t('No proposal or project Id found in request.');
		}
		break;
	case 'show':
		// THIS IS A PLACEHOLDER
	break;
	default: echo "No such action: ".$_GET['action'];
}