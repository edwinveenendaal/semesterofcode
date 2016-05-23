<?php

class Project extends AbstractEntity {

    private static $instance;
    public static $fields = array('pid', 'owner_id', 'title', 'description', 'url', 'state',
        'available', 'begin', 'end',
        'org_id', 'mentor_id', 'proposal_id', 'selected', 'tags', 'views', 'likes');

    public static function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getKeylessFields() {
        // we dont want to return the key fields here
        return array_slice(Project::$fields, 2);
    }

    public function getAllFields() {
        return Project::$fields;
    }

    //Todo: never used. Keep it?
    public function getAllProjects($fetch_style = PDO::FETCH_ASSOC) {
        $projects = db_select('soc_projects')->fields('soc_projects')->execute()->fetchAll($fetch_style);
        return $projects;
    }

    public static function getProjectById($id, $details = false, $fetch_style = PDO::FETCH_ASSOC, $get_prop_count = false) {
        $query = db_select('soc_projects', 'p')->fields('p', self::$fields)->condition('p.pid', $id);
        if ($get_prop_count) {
            //$query->leftjoin('soc_proposals', 'prop', 'p.pid = prop.pid');
            //$query->addExpression('COUNT(prop.proposal_id)', 'proposal_count');
            $query->addExpression('(select COUNT(proposal_id) AS proposal_count FROM soc_proposals WHERE pid=p.pid )', 'proposal_count');
        }
        if ($details) {
            $query->leftjoin('users', 'u1', 'p.mentor_id = %alias.uid');
            $query->leftjoin('users', 'u2', 'p.owner_id = %alias.uid');
            $query->leftjoin('soc_names', 'owner', 'p.owner_id = %alias.names_uid');
            $query->leftjoin('soc_names', 'mentor', 'p.mentor_id = %alias.names_uid');
            $query->leftjoin('soc_organisations', 'o', 'p.org_id = %alias.org_id');

            $query->fields('u1', array('mail', 'name'));
            $query->fields('u2', array('mail', 'name'));
            $query->fields('owner', array('name'));
            $query->fields('mentor', array('name'));
            $query->fields('o', array('name'));
        }
        //project is one asociative array
        //We also return non-available projects. It could happen that a proposal was made for 
        //a project that is later on made unavailable. All functionality should be there, but a clear notice
        //should be given.
        //self::addProjectCondition($query, 'p.', false);
        $project = $query->execute()->fetch($fetch_style);
        return $project;
    }

    public static function addProjectCondition(&$query, $project_alias = 'p.', $state_selected = FALSE) {
        // we want to deliver all the non-draft projects except for the owner (or colleagues) of these projects
        $myorgs = Organisations::getMyOrganisations();
        $current_ts = time();
        if (gettype($query) == 'string') {
            $query .= " AND ((" .
                    ($state_selected ? "" : " (${project_alias}state <> 'draft') AND ") .
                    ($state_selected ? "" : " (${project_alias}state <> 'pending') AND ") .
                    " (${project_alias}available = 1) AND " .
                    " (ISNULL(${project_alias}begin) OR ${project_alias}begin <= $current_ts) AND " .
                    " (ISNULL(${project_alias}end) OR ${project_alias}end >= $current_ts) " .
                    ")" .
                    ($myorgs ? " OR ${project_alias}org_id IN (" . implode($myorgs, ',') . ")" : "") .
                    ")";
        } else {
            $valid_project_condition = db_and()->
                    condition("${project_alias}available", 1, '=')->
                    condition(db_or()->
                            isNull("${project_alias}begin")->
                            condition("${project_alias}begin", $current_ts, '<='))->
                    condition(db_or()->
                    isNull("${project_alias}end")->
                    condition("${project_alias}end", $current_ts, '>='));
            if (!$state_selected) {
                $valid_project_condition->condition("${project_alias}state", 'draft', '<>')->
                        condition("${project_alias}state", 'pending', '<>');
            }

            if ($myorgs) {
                $query->condition(db_or()->
                                condition($valid_project_condition)->
                                condition("${project_alias}org_id", $myorgs, 'IN'));
            } else {
                $query->condition($valid_project_condition);
            }
        }
    }

    public function getProjectsRowCountBySearchCriteria($tags, $organisation, $state, $supervisor) {
        $projectCount = db_select('soc_projects');
        if (isset($supervisor) && $supervisor) {
            $projectCount->join('soc_supervisor_rates', 'r', 'soc_projects.pid = r.pid');
            $projectCount->condition('r.uid', $supervisor);
            $projectCount->condition('r.rate', 0, '>=');
        }
        if (isset($tags) && $tags) {
            if (strpos($tags, ',') !== FALSE) {
                $tags_list = explode(",", $tags);
                foreach ($tags_list as $tag) {
                    $projectCount->condition('tags', '%' . trim($tag) . '%', 'LIKE');
                }
            } else {
                $projectCount->condition('tags', '%' . $tags . '%', 'LIKE');
            }
        }
        if (isset($organisation) && $organisation != "0") {
            $projectCount->condition('org_id', $organisation);
        }
        $state_selected = FALSE;
        if (isset($state) && ($state != "0")) {
            $projectCount->condition('state', $state);
            $state_selected = TRUE;
        }
        $this->addProjectCondition($projectCount, '', $state_selected);
        $projectCount->fields('soc_projects');
        $result = $projectCount->execute()->rowCount();
        return $result;
        //$msg .= toSql($projectCount);
        //return array($result, $msg);
    }

    public function getProjectsBySearchCriteria($tags, $organisation, $state, $supervisor, $sorting, $startIndex, $pageSize) {
        $queryString = "SELECT p.pid, p.title, p.description, p.tags, p.state, p.proposal_id, p.selected, p.available, o.name, " .
                " (SELECT COUNT(proposal_id) " .
                " FROM soc_proposals " .
                " WHERE pid=p.pid) AS proposal_count "
                . " FROM soc_projects p "
                . " LEFT JOIN soc_organisations o ON ( p.org_id = o.org_id) "
                . ($supervisor ? "LEFT JOIN soc_supervisor_rates AS r ON ( p.pid = r.pid) " : "")
                . " WHERE " .
                ($supervisor ? " r.uid = $supervisor AND r.rate >= 0 " : " 1=1 ");
        if (isset($tags) && $tags) {
            if (strpos($tags, ',') !== FALSE) {
                $tags_list = explode(",", $tags);
                foreach ($tags_list as $tag) {
                    $queryString .= " AND tags LIKE '%" . trim($tag) . "%'";
                }
            } else {
                $queryString .= " AND tags LIKE '%" . trim($tags) . "%'";
            }
        }

        if (isset($organisation) && $organisation != "0") {
            $queryString .= " AND p.org_id = " . $organisation;
        }
        if (isset($state) && ($state != "0")) {
            
        }
        $state_selected = FALSE;
        if (isset($state) && $state != "0") {
            $queryString .= " AND p.state = '$state'";
            $state_selected = TRUE;
        }
        $this->addProjectCondition($queryString, 'p.', $state_selected);
        $queryString .= " GROUP BY p.pid ";
        $queryString .= " ORDER BY " . $sorting . " ";

        $queryString .= " LIMIT " . $startIndex . "," . $pageSize . ";";
        return db_query($queryString)->fetchAll();
    }

    public function getFavouriteProjects() {
        $my_id = Users::getMyId();
        $queryString = "SELECT p.pid, p.title, p.description, p.tags, p.state, o.name"
                . " FROM soc_projects p
            LEFT JOIN soc_organisations o on p.org_id = o.org_id
            LEFT JOIN soc_student_favourites f on p.pid = f.pid"
                . " WHERE f.uid = $my_id ";
        return db_query($queryString)->fetchAll();
    }

    public function getStudentsAndProposalCountByCriteriaRowCount($group = '', $mine_only = false) {
        if (!$group) {
            $group = array();
            if ($mine_only) {
                $result = Groups::getGroups(_STUDENT_GROUP, $GLOBALS['user']->uid);
            } else {
                $institutes = Users::getInstituteForUser($GLOBALS['user']->uid);
                if ($institutes->rowCount() > 0) {
                    $result = Groups::getGroups(_STUDENT_GROUP, 'all', $institutes->fetchObject()->inst_id);
                } else {
                    // give up, just get their own
                    $result = Groups::getGroups(_STUDENT_GROUP, $GLOBALS['user']->uid);
                }
            }
            foreach ($result as $record) {
                array_push($group, $record->studentgroup_id);
            }
        }
        $role = _STUDENT_ROLE_ID;
        $query = "
    		SELECT u.uid,u.name as username, sg.name as groupname,COUNT(v.proposal_id) AS proposal_count
			FROM users AS u
			LEFT JOIN soc_proposals AS v ON ( u.uid = v.owner_id )
			LEFT JOIN users_roles as r ON (u.uid = r.uid)
			LEFT JOIN soc_user_membership as m ON (u.uid = m.uid)
			LEFT JOIN soc_studentgroups AS sg ON (sg.studentgroup_id = m.group_id)" .
                ($group ? "WHERE sg.studentgroup_id IN (:grps) AND " : "WHERE ") .
                //"sg.owner_id = ".$GLOBALS['user']->uid." AND r.rid = $role AND m.type = 'studentgroup'
                " r.rid = $role AND m.type = 'studentgroup'
			GROUP BY username";
        $projects = db_query($query, array(':grps' => $group))->rowCount();
        return $projects;
    }

    public static function getStudentsAndProposalCountByCriteria($group, $sorting = 'p.pid', $startIndex = 1, $pageSize = 10, $mine_only = false) {
        if (!$group) {
            $group = array();
            if ($mine_only) {
                $result = Groups::getGroups(_STUDENT_GROUP, $GLOBALS['user']->uid);
            } else {
                $institutes = Users::getInstituteForUser($GLOBALS['user']->uid);
                if ($institutes->rowCount() > 0) {
                    $result = Groups::getGroups(_STUDENT_GROUP, 'all', $institutes->fetchObject()->inst_id);
                } else {
                    // give up, just get their own
                    $result = Groups::getGroups(_STUDENT_GROUP, $GLOBALS['user']->uid);
                }
            }
            foreach ($result as $record) {
                array_push($group, $record->studentgroup_id);
            }
        }
        $role = _STUDENT_ROLE_ID;
        $query = "
    		SELECT u.uid,u.name as username, sg.name as groupname,COUNT(v.proposal_id) AS proposal_count
			FROM users AS u
			LEFT JOIN soc_proposals AS v ON ( u.uid = v.owner_id )
			LEFT JOIN users_roles as r ON (u.uid = r.uid)
			LEFT JOIN soc_user_membership as m ON (u.uid = m.uid)
			LEFT JOIN soc_studentgroups AS sg ON (sg.studentgroup_id = m.group_id)" .
                ($group ? "WHERE sg.studentgroup_id IN (:grps) AND " : "WHERE ") .
                //"sg.owner_id = ".$GLOBALS['user']->uid." AND r.rid = $role AND m.type = 'studentgroup'
                " r.rid = $role AND m.type = 'studentgroup'
			GROUP BY username";

        if (!$sorting) {
            $sorting = 'groupname ASC, username ASC';
        }
        $query .= " ORDER BY " . $sorting
                . " LIMIT " . $startIndex . "," . $pageSize . ";";
        $students = db_query($query, array(':grps' => $group))->fetchAll();
        return $students;
    }

    public function getProjectsProposalCount($pid) {
        $query = "SELECT p.pid from soc_projects as p "
                . "LEFT JOIN soc_proposals AS v ON ( v.pid = p.pid )"
                . "WHERE p.pid = :pid AND NOT ISNULL(v.proposal_id)";

        $nr = db_query($query, array(':pid' => $pid))->rowCount();
        return $nr;
    }

    public function getProjectsAndProposalCountByCriteriaRowCount($organisation = '', $owner_id = '') {
        if (!$organisation) {
            $organisation = array();
            $result = Organisations::getInstance()->getMyOrganisations(TRUE);
            foreach ($result as $record) {
                array_push($organisation, $record->org_id);
            }
        }
        $query = "SELECT p.* from soc_projects as p "
                . "LEFT JOIN soc_proposals AS v ON ( v.pid = p.pid )"
                . "WHERE p.org_id IN (:orgs) AND NOT ISNULL(v.proposal_id)";
        if ($owner_id) {
            //$query .= "AND p.owner_id = " . $owner_id;
            $query .= "AND p.mentor_id = " . $owner_id;
        }
        $projects = db_query($query, array(':orgs' => $organisation))->rowCount();
        return $projects;
    }

    public static function getProjectsAndProposalCountByCriteria($organisation, $owner_id = '', $sorting = 'p.pid', $startIndex = 1, $pageSize = 10) {

        if (!$organisation) {
            $organisations = array();
            $my_orgs = Organisations::getInstance()->getMyOrganisations(TRUE);
            foreach ($my_orgs as $org) {
                array_push($organisations, $org->org_id);
            }
        }
        $query = " 
    		SELECT p.pid, p.title, o.name AS org_name, COUNT(v.proposal_id) AS proposal_count
			FROM soc_projects AS p
			LEFT JOIN soc_proposals AS v ON ( v.pid = p.pid )
			LEFT JOIN soc_organisations AS o ON ( p.org_id = o.org_id ) 
			WHERE p.org_id IN (:orgs) AND NOT ISNULL(v.proposal_id)";

        if ($owner_id) {
            //$query .= "AND p.owner_id = " . $owner_id . " ";
            $query .= "AND p.mentor_id = " . $owner_id . " ";
        }

        $query .= "GROUP BY p.pid ";

        if (!$sorting) {
            $sorting = 'pid ASC';
        }
        $query .= " ORDER BY " . $sorting
                . " LIMIT " . $startIndex . "," . $pageSize . ";";
        $projects = db_query($query, array(':orgs' => $organisations))->fetchAll();
        return $projects;
    }

    public static function getProjects($project_id = '', $owner_id = '', $organisations = '') {
        if ($project_id) {
            $p = self::getProjectById($project_id, FALSE, NULL);
            $projects = $p ? array($p) : array();
        } elseif ($organisations) {
            $table = tableName(_PROJECT_OBJ);
            if (!$owner_id) {
                $projects = db_query("SELECT p.* from $table as p WHERE p.org_id IN (:orgs) ", array(':orgs' => $organisations))->fetchAll();
            } else {
                $projects = db_query("SELECT p.* from $table as p WHERE p.org_id IN (:orgs) AND p.owner_id IN (:uid)", array(':orgs' => $organisations, ':uid' => $owner_id))->fetchAll();
            }
        } elseif ($owner_id) {
            $projects = self::getProjectsByUser($owner_id);
        } else {
            $projects = self::getInstance()->getAllProjects(NULL);
        }
        return $projects;
    }

    //TODO Rewrite this function a bit: multiple returns, unclear why the user_type should be passed
    public static function getProjectsByUser_orig($user_type, $user_id = '', $organisations = '') {
        global $user;

        $org_admin_or_mentor = $user->uid;
        $user_id = $user_id ? : $org_admin_or_mentor;
        $my_role = getRole();
        //todo: find out whether current user is institute_admin

        $table = tableName(_PROJECT_OBJ);
        if ($user_type == _ORGADMIN_TYPE) {
            if ($my_role != _ORGADMIN_TYPE) {
                drupal_set_message(t('You are not allowed to perform this action'), 'error');
                return array();
            } else {
                $my_orgs = $organisations ? :
                        db_query(
                                "SELECT o.org_id from $table as o " .
                                "LEFT JOIN soc_User_membership as um on o.org_id = um.group_id " .
                                "WHERE um.uid = $user_id AND um.type = :organisation", array(':organisation' => _ORGANISATION_GROUP))->fetchCol();
                if ($my_orgs) {
                    $my_projects = db_query("SELECT p.* from $table as p WHERE p.org_id IN (:orgs) ", array(':orgs' => $my_orgs))->fetchAll();
                } else {
                    drupal_set_message(t('You have no organisation yet'), 'error');
                    return array();
                }
            }
        } else {
            if (($my_role != _ORGADMIN_TYPE) && ($user_id != $org_admin_or_mentor)) {
                drupal_set_message(t('You are not allowed to perform this action'), 'error');
                return array();
            }
            $my_projects = db_query("SELECT p.* from $table as p WHERE p.owner_id = $user_id")->fetchAll();
        }

        return $my_projects;
    }

    public static function getProjectsByUser($user_id = '', $organisations = '', $show_all = _VALS_SOC_MENTOR_ACCESS_ALL) {
        global $user;

        $org_admin_or_mentor = $user->uid;
        $user_id = $user_id ? : $org_admin_or_mentor;
        $my_role = getRole();

        $table = tableName(_PROJECT_OBJ);
        if (in_array($my_role, array(_ORGADMIN_TYPE, _MENTOR_TYPE))) {
            $my_orgs = $organisations ? : db_query(
                            "SELECT o.org_id from $table as o " .
                            "LEFT JOIN soc_user_membership as um on o.org_id = um.group_id " .
                            "WHERE um.uid = $user_id AND um.type = :organisation", array(':organisation' => _ORGANISATION_GROUP))->fetchCol();
            if (!$my_orgs) {
                drupal_set_message(t('You have no organisation yet'), 'error');
                return array();
            }
            if ($show_all) {
                $my_projects = db_query("SELECT p.* from $table as p WHERE p.org_id IN (:orgs) ", array(':orgs' => $my_orgs))
                        ->fetchAll();
            } else {
                $my_projects = ///db_query("SELECT p.* from $table as p WHERE p.org_id IN (:orgs) AND p.owner_id = $user_id",array(':orgs' => $my_orgs))
                        db_query("SELECT p.* from $table as p WHERE p.org_id IN (:orgs) AND p.mentor_id = $user_id", array(':orgs' => $my_orgs))
                        ->fetchAll();
            }
        } else {
            drupal_set_message(t('You are not allowed to perform this action'), 'error');
            return array();
        }
        return $my_projects;
    }

    static function getInterestedSupervisors($project_id) {
        return db_query("SELECT R.uid, U.name, N.name as full_name FROM " .
                        tableName('supervisor_rate') . " R " .
                        " LEFT JOIN soc_names N on R.uid = N.names_uid " .
                        " LEFT JOIN users U on R.uid = U.uid " .
                        " WHERE R.pid = $project_id"
                        //." AND R.type = 'supervisor'"
                        . " AND R.rate >= 0"
                )->fetchAll();
    }

    static function getRating($project_id, $user_id) {
        $rating = db_query("SELECT R.rate FROM " .
                tableName('supervisor_rate') . " R " .
                " WHERE R.pid = $project_id AND R.uid = $user_id "
                //." AND ( R.type = 'supervisor' OR R.type = 'institute_admin') "
                //. " AND R.rate >= 0"
                )->fetchObject();
        return $rating ? $rating->rate : -2;
    }

    static function addProject($props) {
        if (!$props) {
            drupal_set_message(t('Insert requested with empty (filtered) data set'), 'error');
            return false;
        }
        // sort and process the datetime array structure
        // pre sql statement.
        Project::normaliseFormArrays($props);

        global $user;
        $txn = db_transaction();
        try {
            $uid = $user->uid;

            $props['owner_id'] = $uid;
            //TODO: for now we assume the mentor is the same as creating the project. As long as we have not built
            //funcitonality to connect mentors to projects, this is a valid assumption
            $props['mentor_id'] = $uid;
            if (!isset($props['state'])) {
                $props['state'] = _VALS_INITIAL_PROJECT_STATE; //'pending';
            }
            //We normalise urls: if they start with http or https we assume the user inserted a full url
            //otherwise we assume a non-https full url
            if (isset($props['url']) && $props['url'] && (stripos($props['url'], 'http') === FALSE)) {
                $props['url'] = 'http://' . $props['url'];
            }
            $result = FALSE;
            $query = db_insert(tableName(_PROJECT_OBJ))->fields($props);
            $id = $query->execute();
            if ($id) {
                $result = $id;
            } else {
                drupal_set_message(t('We could not add your project'), 'error');
            }
        } catch (Exception $ex) {
            $txn->rollback();
            drupal_set_message(t('We could not add your project. ') . (_DEBUG ? $ex->__toString() : ''), 'error');
        }
        return $result;
    }

    static function changeProject($props, $id) {
        if (!$props) {
            drupal_set_message(t('Update requested with empty data set'));
            return false;
        }
        if (isset($props['url']) && $props['url'] && (stripos($props['url'], 'http') === FALSE)) {
            $props['url'] = 'http://' . $props['url'];
        }

        $props['available'] = ((isset($props['available']) && $props['available']) ? 1 : 0);
        if ($props['available']) {
            if (isset($props['begin']) && $props['begin']) {
                if ($begin = date_create_from_format('d-m-Y', $props['begin'])) {
                    $props['begin'] = date_timestamp_get($begin);
                }
            }
            if (isset($props['end']) && $props['end']) {
                if ($begin = date_create_from_format('d-m-Y', $props['end'])) {
                    $props['end'] = date_timestamp_get($begin);
                }
            }
        }

        Project::normaliseFormArrays($props);

        $key = self::keyField(_PROJECT_OBJ);
        //Project::normaliseFormArrays($props);
        $query = db_update(tableName(_PROJECT_OBJ))
                ->condition($key, $id)
                ->fields($props);
        $res = $query->execute();
        // the returned value from db_update is how many rows were updated rather than a boolean
        // - however if the user submits the form without changing anything no rows are actually updated and
        // zero is returned, which is not an error per se. so as a hack set this back to '1'
        // until we find a better way of handling this
        if ($res === 0) {
            $res = 1;
        }
        return $res;
    }

    static function normaliseFormArrays(&$props) {
        $processedProps = array();
        foreach ($props as $key => $value) {
            if (is_array($value)) {
                $value = implode(" ", $value);
            }
            // dont use empty values
            if (!empty($value)) {
                $processedProps[$key] = $value;
            }
        }
        $props = $processedProps;
    }

}
