<?php
function vals_soc_form_user_register_form_validate_handler(&$form, &$form_state){
    $current_role = getRole();
    
    if (isset($form_state['values']['fullname']) && (strlen($form_state['values']['fullname']) > _FULL_NAME_LENGTH)){
    	form_set_error('fullname', tt('The name you entered is a bit too long, Please abbreviate to a max of %1d chars', _FULL_NAME_LENGTH));
    	return;
    }
    
    //If no  errors are encountered, just return, otherwise call form_set_error
    if ($current_role == _ADMINISTRATOR_TYPE){
        return;
    }
    $super = t('Semester of Code Admin');
    
    if (! (isset($form_state['values']['account_key']))){
        form_set_error('account_key', tt('There is no key entered. %1$s.'), $super);
        return;
    }
    
    $code_entered = $form_state['values']['account_key'];

    
    $db_and = db_and();
    $db_and->condition('code', $code_entered);
    $code_result = db_select('soc_codes')->fields('soc_codes')->condition($db_and)->execute();
    $valid_groups_cnt = $code_result->rowCount();
    
    if ($valid_groups_cnt == 0){
    	form_set_error('account_key', t('This is no valid key you entered.'));
    	return;
    }
    
    return;    
}

function vals_soc_form_user_register_form_submit_handler(&$form, &$form_state) {
    $current_role = getRole();//This is the role of the current user which might be administrator
    
    $uid = $form_state['values']['uid'];//As admin you are registering an account (always for somebody else)
    if ($current_role == _ADMINISTRATOR_TYPE){
    	$type = $form_state['values']['account_type'];
    	//$rid = $form_state['values']['roles'];//NOT TRUE !!!! The standard Drupal form for admins to add users submits an int id
    	$entity = in_array($type, array(_STUDENT_TYPE, _INSTADMIN_TYPE, _SUPERVISOR_TYPE)) ? 
    		altSubValue($form_state['values'], 'institute', 0) : altSubValue($form_state['values'], 'organisation', 0);
    	$studentgroup = 0;
    } else {
        //Get the parameters based on the code entered
    	$code_entered = altSubValue($form_state['values'], 'account_key', 0);
        if (!$code_entered){
        	throw new Exception(t('An internal program error occured. '). t('No code is entered'));
        }   	
    	$code_result = db_select('soc_codes')->fields('soc_codes')->condition('code', $code_entered)->execute()->fetchAssoc();
    	if ($code_result) {
	    	$type = $code_result['type'];
	    	$entity =  $code_result['entity_id'];
	    	$studentgroup =  $code_result['studentgroup_id'];
	    	if($type == _STUDENT_GROUP) $type = _STUDENT_TYPE;
    	} else {
    		throw new Exception(t('An internal program error occured. '). t('No valid code is entered'));
    	}
    }
  	$role = db_select('role', 'r')->fields('r')->condition('name', $type)->execute()->fetchAssoc();
    //Insert user role and its membership to some organisation/institute
	if ($role){
       $rid = $role['rid'];
       db_insert('users_roles')->fields( array('uid'=>$uid, 'rid'=>$rid))->execute();
    } else {
        throw new Exception(t('An internal program error occured.'). tt('Not a valid role %1$s', $type));
    }
    //Drupal expects a language input element in the account form. If we are here, the chosen custom language 
    //field has already been inserted in the user record for the new user (so we have to do nothing to achieve that).
     //The user table always contained a language field and is ready to accept that from the
    //register form. If no language field is submitted, the default language will be inserted.    
    
    if ((($type == _STUDENT_TYPE) || ($type == _SUPERVISOR_TYPE) || ($type == _INSTADMIN_TYPE)) && $entity){
        db_insert('soc_user_membership')->fields( array(
            'uid'=>$uid, 
            'type' => _INSTITUTE_GROUP, 
            'group_id'=>$entity
            ))->execute();
        
        if (($type == _STUDENT_TYPE) && $studentgroup){
            db_insert('soc_user_membership')->fields( array(
                'uid'=>$uid, 
                'type' => _STUDENT_GROUP, 
                'group_id'=>$studentgroup
                ))->execute();
        }
    }
    
    if ((($type == _ORGADMIN_TYPE) || ($type == _MENTOR_TYPE)) && $entity){
        db_insert('soc_user_membership')->fields( array(
            'uid'=>$uid, 
            'type' => _ORGANISATION_GROUP, 
            'group_id'=>$entity
            ))->execute();
    }
    
	if (isset( $form_state['values']['fullname'])){
    	$txn = db_transaction();
		try {
			$del_query = "DELETE FROM soc_names WHERE names_uid = $uid AND type= '$type'";
			$ins_query = "INSERT INTO soc_names VALUES ($uid, '$type', '".$form_state['values']['fullname']."')";
				
	    	$updated1 = db_query($del_query);
	    	$updated2 = db_query($ins_query);
		}  catch (Exception $ex) {
			$txn->rollback();
			drupal_set_message(t('We could not update the profile.'). (_DEBUG?  $ex->getMessage().': '.
				(isset($updated1)? $ins_query: $del_query): ''), 'error');
		}
    }
}

function vals_soc_form_user_profile_form_validate_handler(&$form, &$form_state){
	$current_role = getRole();
	
	
	$super = t('Semester of Code Admin');
	//If no  errors are encountered, just return, otherwise call form_set_error
	if (isset($form_state['values']['fullname']) && (strlen($form_state['values']['fullname']) > _FULL_NAME_LENGTH)){
		form_set_error('fullname', tt('The name you entered is a bit too long, Please abbreviate to a max of %1d chars', _FULL_NAME_LENGTH));
		return;
	}
	if ($current_role == _ADMINISTRATOR_TYPE){
		return;
	}
	$group_type = in_array($current_role, array(_SUPERVISOR_TYPE, _INSTADMIN_TYPE, _STUDENT_TYPE)) ? _INSTITUTE_GROUP: _ORGANISATION_GROUP;
	
	$my_groups = Groups::getGroups($group_type);
	if ($my_groups->rowCount()){
		//The user is already in an institute or organisation. Do not expect an account key or do anything with the 
		//organisation/institute. So the form validates as the other Drupal tests already passed if we are here.
		return;
	}
	

	$code_entered = isset($form_state['values']['account_key']) ? $form_state['values']['account_key'] : 0;

	//Check the student belonging to a valid group
	if ($current_role == _STUDENT_TYPE){
// 		$super = t('supervisor');
// 		if (! isset($form_state['values']['institute']) || $form_state['values']['institute']  == 0){
// 			form_set_error('institute', t('You should select your university or institute.'));
// 			return;
// 		}
// 		//If some organisation or institute is chosen, the key should be present
// 		if ((! (isset($form_state['values']['account_key']) && $form_state['values']['account_key']))){
// 			form_set_error('account_key', tt('There is no key entered. Ask your %1$s'), $super);
// 			return;
// 		}
// 		$db_and = db_and();
// 		$db_and->condition('code', $code_entered);
// 		$db_and->condition('type', _STUDENT_GROUP);//Since we do not want a long list of groups during registration
// 		//we use the institute instead and so we select not on type student, but on group
// 		$db_and->condition('entity_id', $form_state['values']['institute']);
// 		$valid_groups_cnt = db_select('soc_codes')->fields('soc_codes')->condition($db_and)->execute()->rowCount();
// 		if ($valid_groups_cnt == 0){
// 			form_set_error('account_key', t('This is no valid key you entered. Ask your supervisor.'));
// 		}
		return;
	} else {
		//Previously it was possible to connect yourself to some org/inst via the user change form but this is only possible for admins and we a
// 		allow them all anyway
		$org = 0;
		if (in_array($current_role, array(_MENTOR_TYPE, _ORGADMIN_TYPE) ) && isset($form_state['values']['organisation'])){
			$org = $form_state['values']['organisation'];
			$super = t('Organisation administrator');
		}
		if (in_array($current_role, array(_SUPERVISOR_TYPE, _INSTADMIN_TYPE)) && isset($form_state['values']['institute'])){
			$org = $form_state['values']['institute'];
			$super = t('Institution administrator');
		}
		if ($org){
			$db_and2 = db_and();
			$db_and2->condition('type', $current_role);
			$db_and2->condition('code', $code_entered);
			if ((! in_array($current_role, array(_INSTADMIN_TYPE, _ORGADMIN_TYPE, _SOC_TYPE, _ADMINISTRATOR_TYPE)))&&
					$org){
				$db_and2->condition('entity_id', $org);
			}
			$found_codes = db_select('soc_codes')->fields('soc_codes')->condition($db_and2)->execute()->rowCount();
			$possible_code_rec = db_select('soc_codes')->fields('soc_codes')->
				condition('type', $current_role)->
				condition('entity_id', $org)->
				execute()->fetchAssoc();
			//->fetchAllAssoc('type', PDO::FETCH_ASSOC); returns [student=>[type=>student, code=><student_code>,....]
			if (!$found_codes){
				form_set_error('account_key', tt('The key you typed is not correct. Ask your %1$s.', $super).
						(_DEBUG ? '<BR>'.t('In this phase, we just give you the code: ').$possible_code_rec['code']: ''));
			} else {
				return;
			}
		} else {
			//user changes details without connecting to an organisation or institute
			return;
		}
	}

}

function vals_soc_form_user_profile_form_submit_handler(&$form, &$form_state) {
    
    /* As we are no longer performing the code labeled here under with 'FROM HERE', it makes no sense to perform these
     * lines either.
     */
    global $user;
    //First get the current user
    $uid = $user->uid;
    $current_role = getRole();    
    if ($current_role == _ADMINISTRATOR_TYPE){
    	$is_admin = TRUE;
    	if ($form_state['values']['uid'] != $uid){
    		$uid = $form_state['values']['uid'];//As admin you are not changing your own account but somebody
    		//else's account.
    	}
        $rid = getUserRoleId($form_state['values']['roles']);
        $role = db_select('role', 'r')->fields('r')->condition('rid', $rid)->execute()->fetchAssoc();
        if ($role){
            $type = $role['name'];
        } else {
            throw new Exception(t('An internal program error occured.'));
        }
    } else {
    	$is_admin = FALSE;
        $type = $current_role;
    }
    if (isset( $form_state['values']['fullname'])){
    	$txn = db_transaction();
		try {
			$del_query = "DELETE FROM soc_names WHERE names_uid = $uid AND type= '$type'";
			$ins_query = "INSERT INTO soc_names VALUES ($uid, '$type', '".$form_state['values']['fullname']."')";
				
	    	$updated1 = db_query($del_query);
	    	$updated2 = db_query($ins_query);
		}  catch (Exception $ex) {
			$txn->rollback();
			drupal_set_message(t('We could not update the profile.'). (_DEBUG?  $ex->getMessage().': '.
					(isset($updated1)? $ins_query: $del_query): ''), 'error');
		}
    }
	/*   
	 * I am still undecided on this point. We might want users (not students) give the permission to connect to another organisation or institute
	 * Perhaps one that was created by a colleague, the admin. If we want users to connect to more than one organisation without
	 * being owner of those, we should leave out the delete statement. For now we assume that organisation admins can have more than
	 * one organisation and they are the only ones capable of such a thing. Moreover: in the form definition we only show the 
	 * org or inst field if there is not any connection yet.
	 * 
	 * That said, for now the code below gives the possibility to connect a person to some organisation without having to do a
	 * re-register.
	 * 
	 *  We assume that the admins create their organisation/institute and all the others register with a 
     * certain organisation. There seems no reason to change that later. If an organisation has two admins 
	 * they can add themselves to an organisation/institute. So the only one doing this is the admin
	 */
    if (TRUE || $is_admin) {
    	if ((isset($form_state['values']['institute']) && $form_state['values']['institute']) &&
	     	(($type == _INSTADMIN_TYPE || ($type == _SUPERVISOR_TYPE)) || ($is_admin && (($type == _STUDENT_TYPE))))
	    		){
	    	db_query("DELETE FROM soc_user_membership WHERE uid = $uid");
	        db_insert('soc_user_membership')->fields( array(
	            'uid'=>$uid, 
	            'type' => _INSTITUTE_GROUP, 
	            'group_id'=>$form_state['values']['institute']
	            ))->execute();
	    }
	    //Mentors might want to change from organisation
	    if ((($type == _ORGADMIN_TYPE) || ($type == _MENTOR_TYPE)) && 
	    		(isset($form_state['values']['organisation']) && $form_state['values']['organisation'])){
	    	db_query("DELETE FROM soc_user_membership WHERE uid = $uid");
	        db_insert('soc_user_membership')->fields( array(
	            'uid'=>$uid, 
	            'type' => _ORGANISATION_GROUP, 
	            'group_id'=>$form_state['values']['organisation']
	            ))->execute();
	    }
    }
}

/**
 * A validation routine for the admin messages form which checks that if
 * an email address was entered, it is parsed and checked to try to ascertain
 * if it is valid or not.
 * @param unknown $form
 * @param unknown $form_state
 */
function vals_soc_admin_messages_form_validate($form, $form_state) {
	if (!empty($form_state['values']['vals_messages_test_email'])){
		$mail = $form_state['values']['vals_messages_test_email'];
		if (!valid_email_address($mail)) {
			form_set_error('vals_messages_test_email', t('Please Enter a valid email address or leave blank'));
		}
	}
}

/**
 * Function to call the test email function on form submission
 * @param unknown $form
 * @param unknown $form_state
 */
function vals_soc_admin_messages_form_submit($form, $form_state) {
	// fire off our test email handler
	if (!empty($form_state['values']['vals_messages_test_email'])){
		module_load_include('inc', 'vals_soc', 'includes/module/vals_soc.mail');
		// A test here to email via queues and cron
		if ($form_state['values']['vals_messages_test_cron_email']==1){
			drupal_set_message(t('Sending queued test emails to ').  $form_state['values']['vals_messages_test_email'] ."");
			vals_soc_handle_test_emails($form_state['values']['vals_messages_test_email'], TRUE);
		}
		else{
			drupal_set_message(t('Sending NON queued test emails to ').  $form_state['values']['vals_messages_test_email'] ."");
			vals_soc_handle_test_emails($form_state['values']['vals_messages_test_email'], FALSE);
		}
	}
}

function vals_soc_organisation_form_validate_handler(&$form, &$form_state)
{
     $current_role = getRole();
     if ($current_role == _ADMINISTRATOR_TYPE || $current_role == _ORGADMIN_TYPE){
         return;
     } else {
         form_set_error('', t('You have not sufficient rights to submit this form'));
     }
}

function vals_soc_organisation_form_submit_handler(&$form, &$form_state)
{
  global $user;
  
  $txn = db_transaction();
  try {
      $uid = $user->uid;
      $org_id = db_insert('soc_organisations')->fields(array(
            'name'=>$form_state['values']['name'], 
            'contact_name' => $form_state['values']['contact_name'], 
            'contact_email' => $form_state['values']['contact_email'],
            'url' => $form_state['values']['url'],
            'description' => ($form_state['values']['description'] ?: ''), 
            ))->execute();

      $result = $org_id && db_insert('soc_user_membership')->fields( array(
            'uid'=>$uid, 
            'type' => _ORGANISATION_GROUP, 
            'group_id'=>$org_id,
            ))->execute();
      $result = $result && db_insert('soc_codes')->fields( array(
          'type'=>_MENTOR_TYPE,
          'code' => createRandomCode(_MENTOR_TYPE, $org_id), 
          'entity_id'=> $org_id))->execute();
      if ($result) {
          drupal_set_message(t('You have succesfully added your organisation to the Semester of Code.'));
      } else {
            drupal_set_message(t('We could not add your organisation.'), 'error');
      }
  } catch (Exception $ex) {
      $txn->rollback();
      drupal_set_message(t('We could not add your organisation.'). (_DEBUG? $ex->__toString(): ''), 'error');
  }
    
}

function vals_soc_institute_form_validate_handler(&$form, &$form_state)
{
 
}

function vals_soc_institute_form_submit_handler(&$form, &$form_state)
{
	global $user;
  	
	$txn = db_transaction();
  try {
      $uid = $user->uid;
      $inst_id = db_insert('soc_institutes')->fields(array(
            'name'=>$form_state['values']['name'], 
            'contact_name' => $form_state['values']['contact_name'], 
            'contact_email' => $form_state['values']['contact_email'], 
            ))->execute();

      $result = $inst_id && db_insert('soc_user_membership')->fields( array(
            'uid'=>$uid, 
            'type' => _INSTITUTE_GROUP, 
            'group_id'=>$inst_id,
            ))->execute();
      $result = $result && db_insert('soc_codes')->fields( array(
          'type'=>_SUPERVISOR_TYPE,
          'code' => createRandomCode(_SUPERVISOR_TYPE, $inst_id), 
          'entity_id'=> $inst_id))->execute();
      if ($result) {
          drupal_set_message(t('You have succesfully added your institute to the Semester of Code.'));
      } else {
          $txn->rollback();
          drupal_set_message(t('We could not add your institute.'), 'error');
      }
  } catch (Exception $ex) {
      $txn->rollback();
      drupal_set_message(t('We could not add your institute.'). (_DEBUG? $ex->__toString(): ''), 'error');
  }
    
}

function vals_soc_studentgroup_form_validate_handler(&$form, &$form_state)
{
 global $user, $language, $base_url;
    
    $uid = $user->uid;
    $inst_nr = db_select('soc_user_membership')->fields('soc_user_membership')->
        condition('uid', $uid)->
        condition('type', _INSTITUTE_GROUP)->
        execute()->rowCount();
    if (!$inst_nr){
        $language_code = $language->prefix;//'en';
        form_set_error('', t('You have not registered yourself for a specific institute. ').
            t('You can change that in your ')."<a href='$base_url/$language_code/user/$uid/edit'>".
            t('profile')."</a>");
    }
}

function vals_soc_studentgroup_form_submit_handler(&$form, &$form_state)
{
    global $user;
    
    $uid = $user->uid;
    $inst = db_select('soc_user_membership')->fields('soc_user_membership')->
        condition('uid', $uid)->
        condition('type', _INSTITUTE_GROUP)->
        execute()->fetchAssoc();
    $inst_id = $inst['group_id'];
    $txn = db_transaction();
    try {
        
        $studentgroup_id = db_insert('soc_studentgroups')->fields(array(
                'inst_id' => $inst_id,
                'owner_id' => $uid,
        		'name' => $form_state['values']['name'],
                'description' => $form_state['values']['description'],
               
            ))->execute();

        $result = $studentgroup_id && db_insert('soc_user_membership')->fields(array(
                'uid' => $uid,
                'type' => _STUDENT_GROUP,
                'group_id' => $studentgroup_id,
            ))->execute();
        $result = $result && db_insert('soc_codes')->fields( array(
	            'type'=>_STUDENT_GROUP,
	            'code' => createRandomCode(_STUDENT_GROUP, $studentgroup_id), 
	            'entity_id'=> $inst_id,
	            'studentgroup_id' =>$studentgroup_id
            ))->execute();
        if ($result) {
            drupal_set_message(t('You have succesfully added your group to the Semester of Code.'));
        } else {
            drupal_set_message(t('We could not add your group.'), 'error');
        }
    } catch (Exception $ex) {
        $txn->rollback();
        drupal_set_message(t('We could not add your group.') . (_DEBUG ? $ex->__toString() : ''), 'error');
    }
}

//function vals_soc_project_form_validate_handler(&$form, &$form_state)
//{
// if ($form_state['values']['available'] && !$form_state['values']['begin']){
//     form_set_error('begin', t('When the project is available, it should have a start date'));
// } else {
//    form_set_error('begin', 'testing maaar ij komt hier met ['.$form_state['values']['begin'].']');
//  
// }
//     
//}
//
//function vals_soc_project_form_submit_handler(&$form, &$form_state)
//{
//    $form_state['values']['available'] = $form_state['values']['available'] ? 1 :0;
//    if ($form_state['values']['available']){
//        $form_state['values']['begin'] = date_timestamp_get(date_create_from_format('Y-m-d', $form_state['values']['begin']));
//        $form_state['values']['end'] = date_timestamp_get(date_create_from_format('Y-m-d', $form_state['values']['end']));
//    } else {
//        $form_state['values']['begin'] = NULL;
//        $form_state['values']['end'] = NULL;
//    }
//}