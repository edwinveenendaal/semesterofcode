<?php
include('include.php');
module_load_include('php', 'vals_soc', 'includes/functions/ajax_functions');
module_load_include('php', 'vals_soc', 'includes/classes/ThreadedComments');
module_load_include('php', 'vals_soc', 'includes/classes/ThreadUIBuilder');
module_load_include('php', 'vals_soc', 'includes/classes/Project');
module_load_include('php', 'vals_soc', 'includes/classes/Proposal');
module_load_include('php', 'vals_soc', 'includes/classes/Institutes');
module_load_include('php', 'vals_soc', 'includes/classes/Organisations');

switch ($_GET['action']){
	case 'delete':
		if (! Users::isAdmin()){
			echo errorDiv("You cannot delete comments");
		} else {
			$type = altSubValue($_POST, 'entity_type', '');
			$id = altSubValue($_POST, 'id', '');
			$entity_id = altSubValue($_POST, 'entity_id', '');
			try {
				$result = db_delete(tableName('comment'))->condition('id', $id);
			} catch (Exception $e) {
				echo "Error ". $e->getMessage();
			}
			echo $result ? successDiv(tt('You succesfully deleted your %1$s.', t('comment'))) :
				errorDiv(tt('We could not delete your %1%s.', t('comment')));
		}
		break;
	case 'save':
		global $user;
		
		$type = altSubValue($_POST, 'entity_type', '');
		$id = altSubValue($_POST, 'id', '');
		$entity_id = altSubValue($_POST, 'entity_id', '');
		$target = altSubValue($_POST, 'target', '');

		$properties = ThreadedComments::getInstance()->filterPostLite(ThreadedComments::getInstance()->getKeylessFields(), $_POST);
		$properties['author'] = $user->uid;
		$result = ThreadedComments::getInstance()->addComment($properties);
		$new = false;

		if ($result){
			// get all the threads
			$thread_details = ThreadedComments::getInstance()->getThreadsForEntity($entity_id, $type);
			// decide which entity it is and get the owner details & description etc
			if($type==_PROJECT_OBJ){
				$entity_details = Project::getInstance()->getProjectById($entity_id, true);
				$fire_emails = true;
			}
			else if($type==_PROPOSAL_OBJ){
				$entity_details = objectToArray(Proposal::getInstance()->getProposalById($entity_id, true));
				$fire_emails = true;
			}
			else{
				// for now nothing - only have projects & proposal comments 
				$fire_emails = false;
			}
			// send emails out...
			if($fire_emails){
				$properties['name'] = $user->name;
				$properties['mail'] = $user->mail;
				module_load_include('inc', 'vals_soc', 'includes/module/vals_soc.mail');
				notify_all_of_new_comment($entity_details, $thread_details, $properties);
			}
			
			echo json_encode(array(
					'result'=>TRUE,
					'id' => $result,
					'type'=> $type,
					'entity_id' => $entity_id,
					'msg'=> tt('You succesfully added a comment to this %1$s', t_type($type)). (_DEBUG ? showDrupalMessages(): '')
			));
		}
		else {
			echo jsonBadResult();
		}
		break;
	case 'view':
		$type = altSubValue($_POST, 'type', 'comment');
		$id = altSubValue($_POST, 'id');
		$target = altSubValue($_POST, 'target', '');
		if (! ($id && $type && $target)){
			die(t('There are missing arguments. Please inform the administrator of this mistake.'));
		}
		$post = ThreadedComments::getInstance()->getPostById($id);
		if (! $post){
			echo tt('The post for this %1$s cannot be found', t_type($type));
		} else {
			$entity_id = $post['entity_id'];
			$entity_type = $post['entity_type'];
			$threaded_comments = new ThreadUIBuilder($entity_id, $entity_type);
			echo $threaded_comments->renderSingleComment($post);
		}
		break;
	case 'viewall':
		if(getRole() != _ANONYMOUS_TYPE){
			$type = altSubValue($_GET, 'type');
			$id = altSubValue($_GET, 'id');
			module_load_include('inc', 'vals_soc', 'includes/ui/comments/threaded_comments');
			$content = initComments($id, $type);
			echo $content;
		}
		break;
	default: echo "No such action: ".$_GET['action'];
}