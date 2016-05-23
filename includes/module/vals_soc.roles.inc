<?php

//The following are copied from the current database state. Can be neater to derive and put in the cache
//or something like that. But for now, all database instances use the same ids ('rid') so we just define them
//for performance reasons
define('_ANONYMOUS_ROLE_ID', 1);
define('_USER_ROLE_ID', 2); //Identical to DRUPAL_AUTHENTICATED_RID
define('_ADMINISTRATOR_ROLE_ID', 3);
define('_STUDENT_ROLE_ID', 4);
define('_MENTOR_ROLE_ID', 5);
define('_ORGADMIN_ROLE_ID', 8);
define('_SUPERVISOR_ROLE_ID', 9);
define('_INSTADMIN_ROLE_ID', 12);
define('_SOC_ROLE_ID', 14);

/**
 * Function to create the custom roles needed in drupal for the summer of code programme.
 * Note that by unistalling and installing again, the autoincrement value will be higher that the numbers above. You might want to reset the
 * values again to these numbers to be safe.
 */
function create_roles() {
    $roles = get_default_roles();
    // Call the array with your default roles.
    foreach ($roles as $key => $value) {
        // Check that a role is not already returned.
        if (!user_role_load_by_name($key)) {
//            $role = new stdClass(); // create your Class
//            // Match values to the role Class.
//            $role->name = $key; // Used to be $value['name'], but that is reserved for a readable string in the UI
//            $role->weight = $value['weight'];
//           //This will not work for InnoDB sql tables  $role->rid = $value['id'] ?: null;
//            // Save the Role using Drupal's API.
//            user_role_save($role);
            $new_rid = db_insert('role')
                ->fields(array('name' => $key,  'weight' =>  $value['weight']))
                ->execute();
             if ($new_rid != $value['id']) {
                db_update('role')
                  ->fields(array('rid' => $value['id']))
                  ->condition('rid', $new_rid)
                  ->execute();
              }
        }
    }
}

function create_permissions() {
// 	$perms[_ANONYMOUS_TYPE] = array(
// 			//'vals admin register' => TRUE,
// 			'vals browse projects' => TRUE,
// 			//'vals admin projects' => TRUE,
// 	);
// 	$perms[_USER_TYPE] = array(
// 			'vals admin register' => TRUE,
// 			'vals browse projects' => TRUE,
// 			'vals admin projects' => TRUE,
// 	);
    $perms[_SOC_TYPE] = array(
        //'vals admin register' => TRUE,
        'vals browse projects' => TRUE,
        'vals access dashboard' => TRUE,
            //'vals admin projects' => TRUE,
    );
    $perms[_ORGADMIN_TYPE] = array(
        'vals admin register' => TRUE,
        'vals browse projects' => TRUE,
        'vals admin projects' => TRUE,
        'vals access dashboard' => TRUE,
    );
    $perms[_SUPERVISOR_TYPE] = array(
        'vals admin register' => TRUE,
        'vals browse projects' => TRUE,
        //'vals admin projects' => TRUE,
        'vals access dashboard' => TRUE,
    );
    $perms[_MENTOR_TYPE] = array(
        //'vals admin register' => TRUE,
        'vals browse projects' => TRUE,
        'vals admin projects' => TRUE,
        'vals access dashboard' => TRUE,
    );
    $perms[_STUDENT_TYPE] = array(
        //'vals admin register' => TRUE,
        'vals browse projects' => TRUE,
        //'vals admin projects' => TRUE,
        'vals apply projects' => TRUE,
        'vals access dashboard' => TRUE,
    );
    $perms[_INSTADMIN_TYPE] = array(
        'vals admin register' => TRUE,
        'vals browse projects' => TRUE,
        'vals edit projects' => TRUE,
        'vals access dashboard' => TRUE,
    );
    foreach ($perms as $role => $permissions) {
        if (!$role_obj = user_role_load_by_name($role)) {
            $role_temp = new stdClass(); // create your Class
            // Match values to the role Class.
            $role_temp->name = $role; // Used to be $value['name'], but that is reserved for a readable string in the UI
            //If we leave the weight out, user_role_save will take the max weight sofar
            //$role_temp->weight = 2;
            //TODO: find out whether this value is important and what it should be
            // Save the Role using Drupal's API.
            if (user_role_save($role_temp)) {
                $role_obj = user_role_load_by_name($role);
                $rid = $role_obj->rid;
            }
        } else {
            $rid = $role_obj->rid;
        }
        // Grant permissions to this role
        user_role_change_permissions($rid, $permissions);
    }
}

/**
 *  This function can be called to set on the fly a new set of permissions for 
 *  certain role. It should be called once only of course.
 */
function setNewPermissions($role, $permissions) {
    if (!is_array($permissions)) {
        $permissions = array($permissions);
    }
    $role_obj = user_role_load_by_name($role);
    $rid = $role_obj->rid;
    user_role_change_permissions($rid, $permissions);
}

/**
 * Create codes
 */
function create_codes() {
    $roles = get_default_roles();
    // Call the array with your default roles.
    if ($roles) {
        $query = db_insert('soc_codes')->fields(array('type', 'code'));
        foreach ($roles as $key => $value) {
            $query->values(array(
                'type' => $key,
                'code' => $value['code'],
                'entity_id' => 0,
            ));
        }
        //Now we have added all the code records
        $query->execute();
    }
}

/**
 * Function which defines a set of roles and returns them in an array.
 * @return multitype:multitype:string
 */

/**
 * Function which defines a set of roles and returns them in an array.
 * Note that we use the key as the string in the database and throughout the code. The name is used in the interface
 * in case the admin likes to revise the roles and permissions.
 * @return multitype:multitype:string
 */
function get_default_roles() {
    $roles = array();
    $roles[_ADMINISTRATOR_TYPE] = array(
        'name' => 'Admin',
        'weight' => '2',
        'code' => 'BHTGSKKRWP'
        ,'id' => _ADMINISTRATOR_ROLE_ID
    );
    // We need the role soc for the members of the team setting up a Semester of Code
    $roles[_SOC_TYPE] = array(
        'name' => 'Semester of Code',
        'weight' => '2',
        'code' => 'EICUN14SOC'
         ,'id' => _SOC_ROLE_ID
    );
    $roles[_ORGADMIN_TYPE] = array(
        'name' => 'Organisation Administrator',
        'weight' => '3',
        'code' => 'AHGLL765OW'
         ,'id' => _ORGADMIN_ROLE_ID
    );
    $roles[_SUPERVISOR_TYPE] = array(
        'name' => 'Academic Supervisor',
        'weight' => '4',
        'code' => 'MNUFD783SF'
         ,'id' => _SUPERVISOR_ROLE_ID
    );
    $roles[_MENTOR_TYPE] = array(
        'name' => 'Mentor',
        'weight' => '5',
        'code' => 'QTU8TR44KJ'
         ,'id' => _MENTOR_ROLE_ID
    );
    $roles[_STUDENT_TYPE] = array(
        'name' => 'Student',
        'weight' => '6',
        'code' => 'PGI99OPAQW'
         ,'id' => _STUDENT_ROLE_ID
    );
    $roles[_INSTADMIN_TYPE] = array(
        'name' => 'Institute Responsible',
        'weight' => '2',
        'code' => 'A99ODF435W'
         ,'id' => _INSTADMIN_ROLE_ID
    );

    return $roles;
}

function getRoles($uid = '') {
    global $user;

    if ($uid) {
        $roles = doAssocQuery("select r.rid,r.name from users_roles as ur left join role as r on r.rid=ur.rid " .
                "where ur.uid = $uid");
        return $roles;
    } else {
        if (isset($user)) {
            return $user->roles;
        } else {
            return array();
        }
    }
}

function getRole() {
    $roles = getRoles();
    if ($roles) {
        foreach ($roles as $rid => $name) {
            if ($rid == _ANONYMOUS_ROLE_ID) {
                return _ANONYMOUS_TYPE;
            } elseif ($rid !== _USER_ROLE_ID) {
                return $name;
            }
        }
        return _USER_TYPE;
    }
}

function getUserRoleId($roles = '', $uid = '') {
    $roles = $roles ? : getRoles($uid);
    //Every authenticated user has exactly two roles:  authenticated user (role 2) and some other role > 2
    //The roles come either from the global user property roles or via the form in an admin edit of a user.
    //In the latter case the roles have the form: [10 => 0, 9 => 9, 8 => 0,....2 => 2]  
    foreach ($roles as $rid => $role_val) {
        if (($role_val !== 0) && ($rid != _USER_ROLE_ID) && ($rid != _ANONYMOUS_ROLE_ID)) {
            return $rid;
        } else {
            
        }
    }
    return _USER_ROLE_ID;
}

function getUserRoleName($roles = array(), $uid = '', $role_id = '') {
    $rid = $role_id ? : getUserRoleId($roles, $uid);
    $role_name = db_query("select r.name from role as r " .
            "where r.rid = $rid")->fetchField(); //->fetchAll();
    return $role_name;
}
