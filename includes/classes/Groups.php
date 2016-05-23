<?php
class Groups extends AbstractEntity{
	public static function getGroups($org_type, $member_id='', $id='')
	{
		global $user;
	
		$current_userid = $user->uid;
		//todo: find out whether current user is institute_admin
	
		if ($member_id == 'all'){	
			if (($org_type == _STUDENT_GROUP) && $id){
				$groups = db_query("SELECT o.* from soc_${org_type}s as o WHERE inst_id = $id");
			} else {
				$groups = db_query("SELECT o.* from soc_${org_type}s as o");
			}
		} else {
			$key_column = self::keyField($org_type);
			$code_key_column = ($org_type == _STUDENT_GROUP) ? 'studentgroup_id' : 'entity_id';
			$member_type = ($org_type == _STUDENT_GROUP) ? _STUDENT_GROUP :(($org_type == _ORGANISATION_GROUP) ? _MENTOR_TYPE: _SUPERVISOR_TYPE);
			if ($id){
				$sqlStr = "SELECT o.*, c.code, c2.code as owner_code from ".tableName($org_type)." as o ".
						" left join soc_user_membership as um on o.$key_column = um.group_id ".
						" left join soc_codes as c on o.$key_column = c.$code_key_column AND c.type = '$member_type'".
						" left join soc_codes as c2 on o.$key_column = c2.$code_key_column AND c2.type = '${org_type}_admin'".
						" WHERE um.type = '$org_type' AND o.$key_column = $id ";
				$groups = db_query($sqlStr);
			} else {
				$member_id = $member_id ?: $current_userid;
				$sqlStr = "SELECT o.*, c.code, c2.code as owner_code from ".tableName($org_type)." as o ".
						" left join soc_user_membership as um on o.$key_column = um.group_id ".
						" left join soc_codes as c on o.$key_column = c.$code_key_column AND c.type = '$member_type'".
						" left join soc_codes as c2 on o.$key_column = c2.$code_key_column AND c2.type = '${org_type}_admin'".
						" WHERE um.type = '$org_type' AND um.uid = $member_id ";
				$groups = db_query($sqlStr);
			}
		}	
		return $groups;
	}
	
	public static function getGroup($org_type, $id='', $group_head_id=''){
		return self::getGroups($org_type, $group_head_id, $id)->fetchObject();
	}
	
	static function changeGroup($type, $organisation, $id)
	{
		if (! $organisation){
			drupal_set_message(t('Update requested with empty data set'), 'error');
			return false;
		}
		$key = self::keyField($type);
	
		$res = db_update(tableName($type))
		->condition($key, $id)
		->fields($organisation)
		->execute();
		// the returned value from db_update is how many rows where updated rather than a boolean 
		// - however if the user submits the form without changing anything no rows are actually updated and
		// zero is returned, which is not an error per se. so as a hack set this back to '1'
		// until we find a better way of handling this
		if($res==0){
			$res=1;
		}
		return $res;
	}

	static function isOwner($type, $id){
		if (! in_array($type, array(_STUDENT_GROUP, _INSTITUTE_GROUP, _ORGANISATION_GROUP, _PROJECT_OBJ, _PROPOSAL_OBJ))){
			drupal_set_message(tt('You cannot be the owner of an entity called %1$s', $type), 'error');
			return FALSE;
		}
		if (Users::isAdmin()){
			//We always want the admin to be able to delete stuff for example and can expect him/her to be very
			//cautious about that of course
			return TRUE;
		}
		$key_field = self::keyField($type);
		$entity = db_query("SELECT * FROM ".tableName($type)." WHERE $key_field = $id")->fetchAssoc();
		//fetchAssoc returns next record (array) or false if there is none
		if (!$entity) {
			return false;
		}
		// just for projects, allow assigned mentors to also have shared ownership
		// so a project owner can also allow the nominated mentor to edit the project details
		if($type == 'project'){
			if($entity['mentor_id'] == $GLOBALS['user']->uid){
				return TRUE;
			}
		}
		//fetchAssoc returns next record (array) or false if there is none
		return $entity && ($entity['owner_id'] == $GLOBALS['user']->uid);
	}
	
	static function isAssociate($type, $id, $obj=null){	
		$scope_table = array(_INSTITUTE_GROUP=>_INSTITUTE_GROUP,_ORGANISATION_GROUP=>_ORGANISATION_GROUP,
				_STUDENT_GROUP=>_INSTITUTE_GROUP, _PROJECT_OBJ =>_ORGANISATION_GROUP, _PROPOSAL_OBJ => _INSTITUTE_GROUP);
		if (! in_array($type, array_keys($scope_table))){
			drupal_set_message(tt('You cannot be the associate of an entity called %1$s', $type), 'error');
			return FALSE;
		}
		$key_field = self::keyField($type);
		$entity = $obj ?: db_query("SELECT * FROM ".tableName($type)." WHERE $key_field = $id")->fetchAssoc();
		//fetchAssoc returns next record (array) or false if there is none
		if (!$entity) {
			return false;
		}
		//Is the current user the owner of this object, fine
		if ($entity['owner_id'] == $GLOBALS['user']->uid) {
			return true;
		}
		
		//Check if the current user is member of the organisation in scope to edit for example
		//If not, return here that the user is not associated (like a supervisor with students in his institute
		if (!self::isMember($scope_table[$type], $entity[self::keyField($scope_table[$type])])){
			return false;
		}
		//We impose some extra role restrictions: students can only have extensive rights for their own proposals
		//institutes and organisations can only be edited by admins
		if ($type == _INSTITUTE_GROUP){
			return Users::isInstituteAdmin(); 
		} elseif ($type == _ORGANISATION_GROUP){
			return Users::isOrganisationAdmin();
		} elseif($type == _PROPOSAL_OBJ){
			return ! Users::isStudent();// So all non-student institute members
		} elseif($type == _PROJECT_OBJ){
			return Users::isOrganisationAdmin();
		}
		return true;
	}
	
	static function isMember($type, $id){
		global $user;
		if (!$user) {
			return false;
		}
		//Assuming there is always an owner inside the group
		return db_query("SELECT * FROM soc_user_membership WHERE type = '$type' AND group_id = $id AND uid = ".$user->uid)->rowCount() > 0;
	}	
	
	static function hasMembers($type, $id){
		//Assuming there is always an owner inside the group
		return db_query("SELECT * FROM soc_user_membership WHERE type = '$type' AND group_id = $id")->rowCount() > 1;
	}
	
	static function removeGroup($type, $id){
		if (!isValidOrganisationType($type)){
			drupal_set_message(tt('This (%1$s) is not something you can remove.', t_type($type)), 'error');
			return FALSE;	
		}
		if (! self::isOwner($type, $id)){
			drupal_set_message(t('You are not authorised to perform this action'), 'error');
			return FALSE;
		}
		
		if (self::hasMembers($type, $id)){
			drupal_set_message(tt('There are already members in this %1$s. You can still edit the %1$s though.',
					t_type($type)), 'error');
			return FALSE;
		}
		
		if (($type == _ORGANISATION_GROUP) && db_query("SELECT pid FROM soc_projects WHERE org_id = $id")->rowCount()){
			drupal_set_message(tt('There are already projects for this %1$s. You should delete these first.',
					t_type($type)), 'error');
			return FALSE;
		}
		
		if (($type == _INSTITUTE_GROUP) && db_query("SELECT pid FROM soc_studentgroups WHERE inst_id = $id")->rowCount()){
			drupal_set_message(tt('There are already student groups for this %1$s. You should delete these first.',
					t_type($type)), 'error');
			return FALSE;
		}
		try {
			if($type != _PROJECT_OBJ){
				$num_deleted2 = db_delete("soc_user_membership")
				->condition('group_id', $id)
				->condition('type', $type)
				->execute();
				if (!$num_deleted2){					
					drupal_set_message(tt('The group had no members.', $type), 'status');
				}
					
				$subtype = ($type == _ORGANISATION_GROUP) ? _MENTOR_TYPE : (($type == _INSTITUTE_GROUP) ? _SUPERVISOR_TYPE : _STUDENT_GROUP);
				$code_field = ($subtype == _STUDENT_GROUP) ? 'studentgroup_id' : 'entity_id';
				$num_deleted3 = db_delete("soc_codes")
					->condition(
						db_and()
							->condition($code_field, $id)
							->condition(
								db_or()
									->condition('type', $subtype)
									->condition('type', "${type}_admin")))
					->execute();

				if (!$num_deleted3){
					drupal_set_message(tt('The %1$s had no code attached.', $type), 'status');
				}
			}
		} catch (Exception $e){
			drupal_set_message(tt(' We could not delete the %1$s', t_type($type)).(_DEBUG ? $ex->getMessage():''), 'error');
			return FALSE;
		}
		
		try{
			$num_deleted = db_delete(tableName($type))
			->condition(self::keyField($type), $id)
			->execute();
	
			if ($num_deleted){			
				drupal_set_message(tt('The %1$s has been deleted.', $type), 'status');
				return TRUE;
			} else {
				drupal_set_message(tt('The %1$s seems to have been deleted already, refresh your screen to see if this is true.', $type), 'error');
				return 0;
			}
		} catch (Exception $e){
			drupal_set_message(tt(' We could not delete the %1$s', t_type($type)).(_DEBUG ? $ex->getMessage():''), 'error');
			return FALSE;
		}
	}
	
/* 	static function addProject($props){
		if (! $props){
			drupal_set_message(t('Insert requested with empty (filtered) data set'), 'error');
			return false;
		}
	
		global $user;
		//mentor GRTDWCOCI
		$txn = db_transaction();
		try {
			$uid = $user->uid;
			$props['owner_id'] = $uid;
			$result = FALSE;
			$id = db_insert(tableName($type))->fields($props)->execute();
			if ($id){
				$result = $id;
			} else {
				drupal_set_message(tt('We could not add your %1$s.', $type), 'error');
			}
	
		} catch (Exception $ex) {
			$txn->rollback();
			drupal_set_message(t('We could not add your project. '). (_DEBUG? $ex->__toString(): ''), 'error');
		}
		return $result;
	} */
	
	
	static function addGroup($props, $type){
		global $user;
		
		if (! $props){
			drupal_set_message(t('Insert requested with empty (filtered) data set'), 'error');
			return false;
		}
			
		$txn = db_transaction();
		try {
			$uid = $user->uid;
			$props['owner_id'] = $uid;
			if ($type == _ORGANISATION_GROUP){
				if (!isset($props['url'])) $props[ 'url'] = '';
				if (!isset($props['description'])) $props[ 'description'] = '';
				$subtype = _MENTOR_TYPE;
			} else if ($type == _INSTITUTE_GROUP){
				$subtype = _SUPERVISOR_TYPE;
			} else {
				drupal_set_message(tt('This type of group cannot be added: %1$s', $type), 'error');
				return false;
			}

			$id = db_insert(tableName($type))->fields($props)->execute();
			if ($id){
				//Make current user creating this organisation, member
				$result = db_insert('soc_user_membership')->fields( array(
						'uid'=>$uid,
						'type' => $type,
						'group_id'=>$id,
				))->execute();
				if ($result){
					$insert = db_insert('soc_codes')->fields(
						array('type', 'code','entity_id', 'studentgroup_id')
					);
					$insert->values(array('type'=>$subtype,
							'code' => createRandomCode($subtype, $id),
							'entity_id'=> $id,
							'studentgroup_id' =>0));
					$insert->values(array('type'=>"${type}_admin",
							'code' => createRandomCode($type, $id),
							'entity_id'=> $id,
							'studentgroup_id' =>0));
					$result = $result && $insert->execute();
					if (!$result){
						drupal_set_message(t('We could not add a code.'), 'error');
					}
				} else {
					drupal_set_message(tt('We could not add you to this %1$s.', $type), 'error');
				}
			} else {
				drupal_set_message(tt('We could not add your %1$s.', $type), 'error');
			}
	
			return $result ? $id : FALSE;
	
		} catch (Exception $ex) {
			$txn->rollback();
			drupal_set_message(t('We could not add your group. '). (_DEBUG? $ex->__toString(): ''), 'error');
		}
		return FALSE;
	}
	
	static function addStudentGroup($group){
		if (! $group){
			drupal_set_message(t('Insert requested with empty (filtered) data set'));
			return false;
		}
			
		global $user;
			
		$txn = db_transaction();
		try {
			$uid = $user->uid;
			$institute_ids = db_select('soc_user_membership')->fields('soc_user_membership', array('group_id'))
			->condition('uid', $uid)
			->condition('type', _INSTITUTE_GROUP)
			->execute()->fetchCol();
			if ($institute_ids){
				$inst_id = $institute_ids[0];
			} else {
				$inst_id = 0;
			}
	
			$gid = db_insert('soc_studentgroups')->fields(array(
					'name'=>$group['name'],
					'owner_id' =>  $uid,
					'inst_id' => $inst_id,
					'description' => ($group['description'] ?: ''),
			))->execute();
			if ($gid){
				$result = db_insert('soc_user_membership')->fields( array(
						'uid'=>$uid,
						'type' => _STUDENT_GROUP,
						'group_id'=>$gid,
				))->execute();
				if ($result){
					$result = $result && db_insert('soc_codes')->fields( array(
							'type'=>_STUDENT_GROUP,
							'code' => createRandomCode(_STUDENT_GROUP, $gid),
							'entity_id'=> $inst_id,
							'studentgroup_id' =>$gid))->execute();
					if (!$result){
						drupal_set_message(t('We could not add a code for this group.'), 'error');
					}
				} else {
					drupal_set_message(t('We could not add you to this group.'), 'error');
				}
			} else {
				drupal_set_message(t('We could not add your group.'), 'error');
			}
	
			return $result ? $gid : FALSE;
	
		} catch (Exception $ex) {
			$txn->rollback();
			drupal_set_message(t('We could not add your group.'). (_DEBUG? $ex->__toString(): ''), 'error');
		}
		return FALSE;
	}
	
	static function filterPostByType($type){
	
		//TODO: get the db fields from schema and move foreach out of switch
		$fields = array(
				_INSTITUTE_GROUP => array('name', 'contact_name', 'contact_email'),
				_ORGANISATION_GROUP=> array('name', 'contact_name', 'contact_email', 'url', 'description'),
				_STUDENT_GROUP=> array('name', 'description'),
				_PROJECT_OBJ => array('org_id', 'title', 'description', 'url', 'tags')
		);
		if (!$type || !isset($fields[$type])){
			return null;
		} else {
			$input = array();
		}
	
		foreach ($fields[$type] as $prop){
			if (isset($_POST[$prop])){
				$input[$prop] = $_POST[$prop];
			}
		}
		return $input;
	}
}