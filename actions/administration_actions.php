<?php
include('include.php');//Includes the necessary bootstrapping and the ajax functions
include(_VALS_SOC_ROOT.'/includes/classes/Project.php');
include(_VALS_SOC_ROOT.'/includes/functions/render_functions.php');
include(_VALS_SOC_ROOT.'/includes/pages/projects.php');
include(_VALS_SOC_ROOT.'/includes/pages/administration.php');

//return result depending on action parameter
switch ($_GET['action']){
	case 'list':
		$type = altSubValue($_POST, 'type');
		switch ($type){
			case _INSTITUTE_GROUP:
			case _ORGANISATION_GROUP:
			case _PROJECT_OBJ:
			case _STUDENT_GROUP: echo renderOrganisations($type, '', 'all', $_POST['target']);break;
			case _SUPERVISOR_TYPE:
			case _STUDENT_TYPE:
			case _MENTOR_TYPE:
			case _ORGADMIN_TYPE:
			case _INSTADMIN_TYPE:
			case 'administer': echo renderUsers($type, '', 'all', '', TRUE);break;
			default:
				//echo tt('No such type: %1$s', $type);
				showError(tt('No such type: %1$s', $type));
		}
	break;
	case 'add':
		$target = altSubValue($_POST, 'target');
		$type = altSubValue($_POST, 'type');
		$show_action = getRequestVar('show_action', 'administer');//altSubValue($_GET, 'show_action', 'administer');
		echo
		'<h2>'.
			(($type == _STUDENT_GROUP) ? t('Add a group to your list of student groups') :
			tt('Add your %1$s', t_type($type))).
		'</h2>';

		$form = drupal_get_form("vals_soc_${type}_form", null, $target, $show_action);
		//TODO  Should this stay? $form['#action'] = url('dashboard/administer/members');
		// Process the submit button which uses ajax
		$form['submit'] = ajax_pre_render_element($form['submit']);
		// Print $form
		renderForm($form, $target);
	break;
    case 'showmembers':
    	if ($_POST['type'] == _STUDENT_GROUP){
            echo renderUsers(_STUDENT_TYPE, '', $_POST['id'], $_POST['type']);
        } elseif ($_POST['type'] == _INSTITUTE_GROUP){
            $subtype = altSubValue($_GET, 'subtype', 'all');
            if ($subtype == _STUDENT_TYPE){
            	echo renderStudents($_POST['id']);
            } elseif ($subtype == _SUPERVISOR_TYPE){
                echo renderSupervisors($_POST['id']);
            } elseif ($subtype == _INSTADMIN_TYPE){
                echo renderUsers(_INSTADMIN_TYPE, '', $_POST['id'], _INSTITUTE_GROUP);
            } elseif ($subtype == 'staff'){
                $inst_id = $_POST['id'];
                echo renderUsers(_INSTADMIN_TYPE, '', $inst_id, _INSTITUTE_GROUP, TRUE);
	    		echo renderUsers(_SUPERVISOR_TYPE, '', $inst_id, _INSTITUTE_GROUP, TRUE);

            } else {
            	echo tt('No such type %1$s', $subtype);
            }

        } elseif ($_POST['type'] == _ORGANISATION_GROUP){
           $organisation_id = altSubValue($_POST, 'id', '');
           if($organisation_id == 0){
           	$organisation_id = 'all';
           }
           echo
			renderUsers(_ORGADMIN_TYPE, '', $organisation_id, _ORGANISATION_GROUP, TRUE).
			renderUsers(_MENTOR_TYPE, '', $organisation_id, _ORGANISATION_GROUP, TRUE);
        }
     break;
    case 'show':
    	$type = altSubValue($_POST, 'type', '');
    	$show_action = altSubValue($_POST, 'show_action', 'administer');
    	if ($type && (in_array($type, array(_INSTITUTE_GROUP, _STUDENT_GROUP)))){
    		$derived = deriveTypeAndAction();
    		if ($derived['type'] == 'group'){
    			$show_action = 'groups';
    		}
    	}
    	$show_last = altSubValue($_POST, 'new_tab', false);
    	showRoleDependentPage(getRole(), $show_action, $show_last);
    break;
    case 'view':
    	$type = altSubValue($_POST, 'type');
    	$id = altSubValue($_POST, 'id');
    	$target = altSubValue($_POST, 'target', '');
    	$buttons = altSubValue($_GET, 'buttons', true);
        if (Users::isOfType(_SOC_TYPE)){
            $buttons = FALSE;
        }
    	if (! ($id && $type && $target)){
    		die(t('There are missing arguments. Please inform the administrator of this mistake.'));
    	}
    	$organisation = Groups::getGroup($type, $id);
    	if (! $organisation){
    		echo tt('The %1$s cannot be found', t_type($type));
    	} else {
    		echo "<div id='msg_$target'></div>";
    		echo renderOrganisation($type, $organisation, null, $target, $buttons);
    	}
    	break;
    case 'delete':
    	$type = altSubValue($_POST, 'type', '');
    	$id = altSubValue($_POST, 'id', '');
    	$target = altSubValue($_POST, 'target', '');
    	//perhaps the type can be derived from the current url (based on http-referrer system var)
    	extract(deriveTypeAndAction(empty($type)), EXTR_OVERWRITE);
    	if (! isValidOrganisationType($type)) {
    		echo jsonBadResult(t('There is no such type we can delete'));
    	} else {
    		$result = Groups::removeGroup($type, $id);
    		echo $result ? jsonGoodResult() : jsonBadResult('', array('target'=>$target));
    	}
    break;
    case 'send_invite_email':
    	module_load_include('inc', 'vals_soc', 'includes/module/vals_soc.mail');
    	$type = altSubValue($_POST, 'type', '');
    	$email = altSubValue($_POST, 'contact_email', '');
    	$subject = altSubValue($_POST, 'subject', '');
    	$body = altSubValue($_POST, 'description', '');
    	$items = array(array('key' => 'vals_soc_invite_new_user', 'to' => $email, 'from' => NULL, 'subject' => $subject,
    		'body' => $body));
    	try{
    		vals_soc_send_emails_cron($items);
    		//$result = vals_soc_send_emails_now($items);
    		$result = TRUE;
    		$msg = t('Email successfully sent') . (_DEBUG ? showDrupalMessages(): '');
    	}
    	catch(Exception $err){
    		$result = FALSE;
    		$msg = t('Email could not be sent') . (_DEBUG ? $err->getMessage().showDrupalMessages() : '');
    	}
    	
    	$id = altSubValue($_POST, 'id', '');
    	$show_action = altSubValue($_POST, 'show_action', '');

		if ($result){
            echo json_encode(array(
            		'result'=>TRUE,
            		'id' => $id,
            		'type'=> $type,
            		'show_action' => $show_action,
            		'msg'=> $msg
            	));
        } else {
        	echo jsonBadResult($msg);
        }
    break;
    case 'inviteform':
    	$type = altSubValue($_POST, 'type', '');
    	$subtype = altSubValue($_POST, 'subtype', '');
    	$id = altSubValue($_POST, 'id', '');
    	//HIer afleiden
    	$derived = deriveTypeAndAction();
    	if ($derived['type']!== $type){
    		$after = $type;
    	}
    	$after = altSubValue($_POST, 'show_action', 'administer');
    	$target = altSubValue($_POST, 'target', '');
    	if (! isValidOrganisationType($type) ) {//for convenience we have made a project an organisationtype as well //TODO: make this better
    		echo tt('There is no such type you can invite people to : %1$s', $type);
    	} else {
    		$obj = Groups::getGroup($type, $id);
    		// See http://drupal.stackexchange.com/questions/98592/ajax-processed-not-added-on-a-form-inside-a-custom-callback-my-module-deliver
    		// for additions below
    		$form = drupal_get_form("vals_soc_invite_form", $obj, $target, $after, $type, $subtype);
    		renderForm($form, $target);
    	}
    	break;
    case 'edit':
        $type = altSubValue($_POST, 'type', '');
        $id = altSubValue($_POST, 'id', '');
        $target = altSubValue($_POST, 'target', '');
        if (! isValidOrganisationType($type) ) {//for convenience we have made a project an organisationtype as well //TODO: make this better
        	echo tt('There is no such type to edit : %1$s', $type);
        } else {
        	$obj = Groups::getGroup($type, $id);
        	// See http://drupal.stackexchange.com/questions/98592/ajax-processed-not-added-on-a-form-inside-a-custom-callback-my-module-deliver
        	// for additions below
        	$originalPath = false;
        	if(isset($_POST['path'])){
        		$originalPath = $_POST['path'];
        	}
        	unset($_POST);
        	$form = drupal_get_form("vals_soc_${type}_form", $obj, $target);
        	if($originalPath){
        		$form['#action'] = url($originalPath);
        	}
        	// Process the submit button which uses ajax
        	//$form['submit'] = ajax_pre_render_element($form['submit']);
        	// Build renderable array
//         	$build = array(
//         			'form' => $form,
//         			'#attached' => $form['submit']['#attached'], // This will attach all needed JS behaviors onto the page
//         	);
        	renderForm($form, $target);
        }
    break;
    case 'save':
        $type = altSubValue($_POST, 'type', '');
        $id = altSubValue($_POST, 'id', '');
        $show_action = altSubValue($_POST, 'show_action', 'view');
        //TODO do some checks here
        if(! isValidOrganisationType($type) ){//&& ($type !== _PROJECT_OBJ)
        	$result = NULL;
        	drupal_set_message(tt('This is not a valid type: %s', $type), 'error');
        	echo jsonBadResult();
        	return;
        }

        $properties = Groups::filterPostByType($type, $_POST);
        if (!$id){
        	$new = true;
        	$result = ($type == _STUDENT_GROUP) ? Groups::addStudentGroup($properties) :
        		($type == _PROJECT_OBJ ? Project::getInstance()->addProject($properties) : Groups::addGroup($properties, $type));
        } else {
        	$new = false;
        	$result = Groups::changeGroup($type, $properties, $id);
        }

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
    default: echo "No such action: ".$_GET['action']. ' in administration actions.';
}