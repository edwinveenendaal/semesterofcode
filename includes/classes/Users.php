<?php

class Users extends AbstractEntity {

    public static function getGroups($supervisor = '') {
        global $user;

        $supervisor = $supervisor ? : $user->uid;
        if (!self::isSuperVisor()) {
            drupal_set_message(t('You cannot view this data'), 'error');
            return array();
        }
        //todo: find out whether current user is supervisor
        $table = tableName(_STUDENT_GROUP);
        if ($supervisor == 'all') {
            $groups = db_select($table)->fields($table)->execute()->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $groups = db_select($table)->fields($table)->condition('owner_id', $supervisor)->
                            execute()->fetchAll(PDO::FETCH_ASSOC);
        }

        return $groups;
    }

    public static function isAdmin() {
        return self::isOfType(_ADMINISTRATOR_TYPE);
    }

    public static function isInstituteAdmin() {
        return self::isOfType(_INSTADMIN_TYPE);
    }

    public static function isOrganisationAdmin() {
        return self::isOfType(_ORGADMIN_TYPE);
    }

    public static function isSomeAdmin() {
        return self::isAdmin() || self::isInstituteAdmin() || self::isOrganisationAdmin();
    }

    public static function isSuperVisor() {
        global $user;
        return in_array(_INSTADMIN_TYPE, $user->roles) ||
                in_array(_SUPERVISOR_TYPE, $user->roles);
    }

    public static function isStudent() {
        return self::isOfType(_STUDENT_TYPE);
    }

    public static function isMentor() {
        global $user;
        return in_array(_MENTOR_TYPE, $user->roles) ||
                in_array(_ORGADMIN_TYPE, $user->roles);
    }

    public static function isUser() {
        global $user;

        return isset($user) && isset($user->uid) && $user->uid;
    }

    public static function isSoc() {
        return self::isOfType(_SOC_TYPE);
    }

    public static function isOfType($type, $uid = '') {
        global $user;

        if ($uid) {
            return in_array($type, getRoles($uid));
        } else {
            return in_array($type, $user->roles);
        }
    }

    public static function getMyId() {
        global $user;

        return $user->uid;
    }

    public static function getMyName() {
        global $user;
        if (isset($user->uid)) {
            $id = $user->uid;
            $u = db_query(
                    "SELECT u.name, n.name as full_name " .
                    "from users as u " .
                    "left join soc_names as n on u.uid = n.names_uid " .
                    "WHERE u.uid = $id")
                    ->fetchObject();
            if ($u->full_name) {
                return $u->full_name;
            } else {
                return $u->name;
            }
        }
        return $user->uid;
    }

    public static function getStudentDetails($id) {
        if ($id) {
            return db_query(
                                    "SELECT u.name as supervisor_name, u.mail as supervisor_mail, u.uid as supervisor_id " .
                                    ",u2.name as student_name, u2.mail as student_mail, u2.uid as student_id, " .
                                    "g.name as group_name,i.name as institute_name, i.inst_id " .
                                    "from soc_user_membership um " .
                                    "left join users_roles as ur on $id=ur.uid " .
                                    "left join role as r on r.rid=ur.rid " .
                                    "left join soc_studentgroups as g on um.group_id = g.studentgroup_id " .
                                    "left join users as u on u.uid = g.owner_id " .
                                    "left join soc_institutes as i on i.inst_id = g.inst_id " .
                                    "left join users as u2 on u2.uid = $id " .
                                    "WHERE um.uid = $id AND um.type = :studentgroup AND r.name = :student", array(':studentgroup' => _STUDENT_GROUP, ':student' => _STUDENT_TYPE))
                            ->fetchObject();
        } else {
            return null;
        }
    }

    public static function getStudents($group = '') {
        global $user;

        $supervisor = $user->uid;
        //todo: find out whether current user is supervisor
        if (!self::isSuperVisor()) {
            drupal_set_message(t('You cannot view this data'), 'error');
            return array();
        }
        if ($group == 'all') {
            $students = db_query(
                    'select u.*, n.name as fullname from users as u ' .
                    'left join users_roles as ur on u.uid=ur.uid ' .
                    'left join role as r on r.rid=ur.rid ' .
                    "left join soc_names as n on u.uid=n.names_uid " .
                    'where r.name=:role ', array(':role' => _STUDENT_TYPE));
        } elseif ($group) {
            $students = db_query(
                    "SELECT u.*, n.name as fullname from users as u " .
                    "left join soc_user_membership as um on u.uid = um.uid " .
                    "left join soc_names as n on u.uid=n.names_uid " .
                    "WHERE um.type = :studentgroup AND um.group_id = $group AND u.uid != $supervisor ", array(':studentgroup' => _STUDENT_GROUP));
        } else {
            $groups = db_query(
                    "SELECT group_id from soc_user_membership um " .
                    "WHERE um.type = :studentgroup AND um.uid = $supervisor ", array(':studentgroup' => _STUDENT_GROUP))->fetchCol();
            if ($groups) {
                $students = db_query(
                        "SELECT u.*, n.name as fullname from users as u " .
                        "left join soc_user_membership um on u.uid = um.uid " .
                        "left join soc_names as n on u.uid=n.names_uid " .
                        "WHERE um.type = :studentgroup AND um.group_id IN (:groups) AND u.uid != $supervisor ", array(':groups' => $groups, ':studentgroup' => _STUDENT_GROUP));
            } else {
                return NULL;
            }
        }

        return $students;
    }

    public static function getAllUsers() {
        $query = "SELECT DISTINCT u.*,n.name as fullname, r.name as type from users as u " .
                "left join users_roles as ur on u.uid = ur.uid " .
                "left join role as r  on ur.rid = r.rid " .
                "left join soc_names as n on u.uid=n.names_uid " .
                "ORDER BY u.name";
        return db_query($query);
    }

    /*
     * Get Users of type member_type out of the organisation structure of type group_type, based on:
     * a) group_id == all => just get all students, tutors etc.
     * b) id is set => retrieve Users with that id (should be just one)
     * c) group id is set or can be derived from current user => get Users from that group
     */

    public static function getUsers($member_type, $group_type = '', $group_id = '', $id = '') {
        global $user;

        $group_head = $user->uid;
        //todo: find out whether current user is indeed head of the group

        $group_type = $group_type ? : self::participationGroup($member_type);
        if ($group_id == 'all') {
            // updated to ensure we only retrieve users that belong to
            // one of the logged in users 'soc_user_membership ' groups. 
            // For example, this was originally retrieving ALL mentors, 
            // inc ones not in any of the current users organisations
            $group_ids = Users::isAdmin() ? null : db_query(
                                    "SELECT group_id from soc_user_membership t" .
                                    " WHERE t.uid = $group_head AND t.type = '$group_type' ")
                            ->fetchCol();
            $query = "SELECT DISTINCT u.*,n.name as fullname from users as u " .
                    "left join users_roles as ur on u.uid = ur.uid " .
                    "left join role as r  on ur.rid = r.rid " .
                    "left join soc_user_membership as um  on u.uid = um.uid " .
                    'left join soc_names as n on u.uid=n.names_uid ' .
                    "WHERE r.name = '$member_type' AND um.type = '$group_type' ";
            if ($group_ids) {
                //So we know which groups and of which type membertype should be member
                $query .= " AND um.group_id IN (" . implode(',', $group_ids) . ")";
                $members = db_query($query);
            }
            $members = db_query($query);
        } else {
            if ($id) {
                $members = db_query(
                        "SELECT u.*,n.name as fullname from users as u " .
                        'left join soc_names as n on u.uid=n.names_uid ' .
                        "WHERE u.uid = '$id'");
            } else {
                if ($group_id && $group_type) {
                    $group_ids = array($group_id);
                } else {
                    if ($group_type) {
                        $key = self::keyField($group_type);
                        $table = tableName($group_type);
                        //get the organisation from the current user, assuming he/she is head of the organisation/group/etc
                        $group_ids = db_query(
                                "SELECT $key from $table t" .
                                " WHERE t.owner_id = $group_head ")
                                ->fetchCol();
                    } else {
                        $group_ids = null;
                    }
                }

                if ($group_ids) {
                    //So we know which groups and of which type membertype should be member
                    $members = db_query(
                            "SELECT u.*,n.name as fullname from users as u " .
                            "left join users_roles as ur on u.uid = ur.uid " .
                            "left join role as r  on ur.rid = r.rid " .
                            "left join soc_user_membership as um  on u.uid = um.uid " .
                            'left join soc_names as n on u.uid=n.names_uid ' .
                            "WHERE r.name = '$member_type' AND um.type = '$group_type' AND um.group_id IN (" .
                            implode(',', $group_ids) . ")");
                } else {
                    return NULL;
                }
            }
        }

        return $members;
    }

    public static function getParticipant($id = '') {
        return Users::getUsers('', '', '', $id)->fetchObject();
    }

    public static function getParticipantBasicSubset($id) {
        if (isset($id) && $id) {
            return db_query(
                            "SELECT u.name, u.mail,n.name as fullname from users as u " .
                            'left join soc_names as n on u.uid=n.names_uid ' .
                            "WHERE u.uid = '$id'")->fetchObject();
        } else {
            drupal_set_message(t('No valid user identifier specified'), 'error');
            return null;
        }
    }

    public static function getAllStudents($institute = '') {
        global $user;

        $institute_admin = $user->uid;
        //todo: find out whether current user is institute_admin

        if ($institute == 'all') {
            $students = db_query('select u.*,n.name as fullname from users as u ' .
                    'left join  users_roles as ur on u.uid=ur.uid ' .
                    'left join role as r on r.rid=ur.rid ' .
                    'left join soc_names as n on u.uid=n.names_uid ' .
                    'where r.name=:role ', array(':role' => _STUDENT_TYPE));
        } else {
            if (!$institute) {
                //get the institute from the institute admin
                $institute = db_query(
                        "SELECT group_id from soc_user_membership um " .
                        "WHERE um.type = :institute AND um.uid = $institute_admin ", array(':institute' => _INSTITUTE_GROUP))->fetchColumn();
            }
            if ($institute) {
                $students = db_query(
                        "SELECT u.*,n.name as fullname from users as u " .
                        " left join users_roles as ur on u.uid = ur.uid " .
                        " left join role as r on ur.rid = r.rid" .
                        " left join soc_user_membership as um on u.uid = um.uid" .
                        ' left join soc_names as n on u.uid=n.names_uid ' .
                        "WHERE r.name = :student AND um.type = :institute AND  um.group_id = $institute", array(':institute' => _INSTITUTE_GROUP, ':student' => _STUDENT_TYPE));
            } else {
                return NULL;
            }
        }

        return $students;
    }

    public static function getSupervisors($institute = '') {
        global $user;

        $institute_admin = $user->uid;
        //todo: find out whether current user is supervisor

        if ($institute == 'all') {
            $supervisors = db_query('select u.* from users as u left join  users_roles as ur on u.uid=ur.uid left join ' .
                    'role as r on r.rid=ur.rid where r.name=:role ', array(':role' => _SUPERVISOR_TYPE));
        } elseif ($institute) {
            $supervisors = db_query("SELECT u.* from users as u " .
                    "left join users_roles as ur  on u.uid = ur.uid " .
                    "left join role as r  on ur.rid = r.rid " .
                    "left join soc_user_membership as um on u.uid = um.uid " .
                    "WHERE r.name = :supervisor AND um.type = :institute " .
                    "AND um.group_id = $institute ", array(':institute' => _INSTITUTE_GROUP, ':supervisor' => _SUPERVISOR_TYPE));
        } else {
            //get the institute from the institute_admin
            $institute = db_query(
                    "SELECT group_id from soc_user_membership um" .
                    " WHERE um.type = :institute AND um.uid = $institute_admin ", array(':institute' => _INSTITUTE_GROUP))->fetchColumn();
            if ($institute) {
                $supervisors = db_query(
                        "SELECT u.* from users as u " .
                        "left join soc_user_membership um on u.uid = um.uid " .
                        "left join users_roles as ur  on u.uid = ur.uid " .
                        "left join role as r  on ur.rid = r.rid " .
                        "WHERE um.type = :institute AND um.group_id = $institute " .
                        "AND r.name = :supervisor ", array(':institute' => _INSTITUTE_GROUP, ':supervisor' => _SUPERVISOR_TYPE));
            } else {
                return NULL;
            }
        }
        return $supervisors;
    }

    public static function getMentors($organisation = '') {
        global $user;

        $organisation_admin = $user->uid;
        //todo: find out whether current user is org admin
        //get organisations
        if ($organisation == 'all') {
            $mentors = db_query(
                    'select u.* from users as u ' .
                    'left join users_roles as ur on u.uid=ur.uid ' .
                    'left join role as r on r.rid = ur.rid ' .
                    'where r.name=:role ', array(':role' => _MENTOR_TYPE));
        } elseif ($organisation) {

            $mentors = db_query(
                    "SELECT u.* from users as u " .
                    "left join soc_user_membership as um on u.uid = um.uid " .
                    " WHERE um.type = :organisation AND um.group_id = $organisation AND " .
                    "u.uid != $organisation_admin ", array(':organisation' => _ORGANISATION_GROUP));
        } else {
            //get the organisation
            $organisation = db_query(
                    "SELECT group_id from soc_user_membership um " .
                    "WHERE um.type = :organisation AND um.uid = $organisation_admin ", array(':organisation' => _ORGANISATION_GROUP))
                    ->fetchColumn();
            if ($organisation) {
                $mentors = db_query(
                        "SELECT u.* from users as u " .
                        "left join soc_user_membership um on u.uid = um.uid " .
                        "WHERE um.type = :organisation AND um.group_id = $organisation AND u.uid != $organisation_admin ", array(':organisation' => _ORGANISATION_GROUP));
            } else {
                return NULL;
            }
        }

        return $mentors;
    }

    public static function getInstituteAdmins($institute = '') {
        global $user;

        $institute_admin = $user->uid;
        //todo: find out whether current user is supervisor

        if ($institute == 'all') {
            $admins = db_query(
                    'select u.* from users as u ' .
                    'left join  users_roles as ur on u.uid=ur.uid ' .
                    'left join role as r on r.rid=ur.rid ' .
                    'WHERE r.name=:role ', array(':role' => _INSTADMIN_TYPE));
        } else {
            if (!$institute) {
                $institute = db_query(
                        "SELECT group_id from soc_user_membership um " .
                        "WHERE um.type = :institute AND um.uid = $institute_admin ", array(':institute' => _INSTITUTE_GROUP))->fetchColumn();
            }
            if ($institute) {
                //Get all the admins from this institute (1?: all users with role institute_admin who are member of this institute
                $admins = db_query(
                        "SELECT u.* from users as u " .
                        " left join users_roles as ur on u.uid = ur.uid" .
                        " left join role as r on ur.rid = r.rid " .
                        " left join soc_user_membership as um on u.uid = um.uid" .
                        " WHERE r.name = '" . _INSTADMIN_TYPE . "'  AND um.type = :institute AND " .
                        " um.group_id = $institute ", array(':institute' => _INSTITUTE_GROUP)
                );
            } else {
                return NULL;
            }
        }

        return $admins;
    }

    public static function getOrganisationAdmins($organisation = '') {
        global $user;

        $organisation_admin = $user->uid;
        //todo: find out whether current user is organisation_admin

        if ($organisation == 'all') {
            $admins = db_query('select u.* from users as u left join  users_roles as ur on u.uid=ur.uid left join ' .
                    'role as r on r.rid=ur.rid where r.name=:role ', array(':role' => _ORGADMIN_TYPE));
        } else {
            if (!$organisation) {
                $organisation = db_query(
                        "SELECT group_id from soc_user_membership um " .
                        "WHERE um.type = :organisation AND um.uid = $organisation_admin ", array(':organisation' => _ORGANISATION_GROUP))
                        ->fetchColumn();
            }
            if ($organisation) {
                //Get all the admins from this organisation (1?: all users with role organisation_admin who are member of this organisation
                $admins = db_query(
                        "SELECT u.* from users as u " .
                        " left join users_roles as ur  on u.uid = ur.uid" .
                        " left join role as r on ur.rid = r.rid" .
                        " left join soc_user_membership as um on u.uid = um.uid" .
                        " WHERE r.name = :orgadmin AND um.type = :organisation AND um.group_id = $organisation ", array(':orgadmin' => _ORGADMIN_TYPE, ':organisation' => _ORGANISATION_GROUP));
            } else {
                return NULL;
            }
        }

        return $admins;
    }

    public static function getInstituteForUser($id) {
        if ($id) {
            $query = "SELECT o.* from soc_institutes as o
						left join soc_user_membership as um on o.inst_id = um.group_id
						WHERE um.type = :institute AND um.uid =" . $id;
            return db_query($query, array(':institute' => _INSTITUTE_GROUP));
        } else {
            return null;
        }
    }

}
