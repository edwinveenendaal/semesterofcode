<?php

/*
 * This file shows the various lists and views on the possible Users to Semester of Code: organisations
 * instititutes, mentors, supervisors, students etc
 */

function showFavouritesPage() {
    if (Users::isStudent()) {
        $my_id = Users::getMyId();
        $projects = Project::getInstance()->getFavouriteProjects();
        if ($projects) {
            echo "<ul>";
            foreach ($projects as $project) {
                echo "<li><a href='" . _WEB_URL . "/projects/browse?pid=" . $project->pid . "'>" . $project->title . "</a></li>";
            }
            echo "</ul>";
        } else {
            echo t('You have no projects marked as favourite yet. By browsing them you can mark them as you find them interesting');
        }
    } else {
        echo t('You must be a student to have kept a favourite list');
    }
}

function formatUsersNice($users, $type = 'User', $empty_message = '', $show_title = FALSE) {
    $output = '';
    $output .= '<dl class="view_record">';
    if ($show_title) {
        $output .= '<dt>';
        $formatted_type = ucfirst(str_replace('_', ' ', $type)) . 's';
        $output .= $formatted_type;
        $output .= '</dt>';
        $output .= '<span class="ui-icon ui-icon-arrowreturn-1-e"></span>';
    }

    if ($users && $users->rowCount()) {
        foreach ($users as $member) {
            $output .= '<dd>';
            $output .= '<b>' . t('Name') . ': </b>';
            if (isset($member->fullname) && $member->fullname != '') {
                $output .= $member->fullname;
            } else {
                $output .= $member->name;
            }
            $output .= '</dd>';
            $output .= '<dd>';
            $output .= '<b>' . t('Email') . ': </b>' . $member->mail;
            $output .= '</dd>';
            $output .= '<br/>';
        }
    } else {
        $output .= '<dd>';
        $output .= $empty_message;
        ;
        $output .= '</dd>';
    }
    $output .= '</dl>';
    return $output;
}

function formatAgreementRecordNice($agreement, $target = '') {
    $sname = (isset($agreement->student_name) ? $agreement->student_name : $agreement->name);
    $student_email = $agreement->mail;
    $spname = (isset($agreement->supervisor_name) ? $agreement->supervisor_name : $agreement->supervisor_user_name);
    $supervisor_email = $agreement->supervisor_user_mail;
    $mname = (isset($agreement->mentor_name) ? $agreement->mentor_name : $agreement->mentor_user_name);
    $mentor_email = $agreement->mentor_user_mail;

    $output = '';

    $output .= '<dl class="view_record">';

    $output .= '<dt>';
    $output .= t('Agreement text');
    $output .= '</dt>';
    $output .= '<span class="ui-icon ui-icon-arrowreturn-1-e"></span>';
    $output .= '<dd>';
    $output .= $agreement->description;
    $output .= '</dd>';

    $output .= '<dt>';
    $output .= t('Student');
    $output .= '</dt>';
    $output .= '<span class="ui-icon ui-icon-arrowreturn-1-e"></span>';
    $output .= '<dd>';
    $output .= $sname . ' (' . $student_email . ')';
    $output .= '</dd>';

    $output .= '<dt>';
    $output .= t('Supervisor');
    $output .= '</dt>';
    $output .= '<span class="ui-icon ui-icon-arrowreturn-1-e"></span>';
    $output .= '<dd>';
    $output .= $spname . ' (' . $supervisor_email . ')';
    $output .= '</dd>';

    $output .= '<dt>';
    $output .= t('Mentor');
    $output .= '</dt>';
    $output .= '<span class="ui-icon ui-icon-arrowreturn-1-e"></span>';
    $output .= '<dd>';
    $output .= $mname . ' (' . $mentor_email . ')';
    $output .= '</dd>';

    $output .= '</dl>';

    return $output;
}

function formatFinalisationRecordNice($agreement, $target = '') {
    $sname = (isset($agreement->student_name) ? $agreement->student_name : $agreement->name);
    $student_email = $agreement->mail;
    $spname = (isset($agreement->supervisor_name) ? $agreement->supervisor_name : $agreement->supervisor_user_name);
    $supervisor_email = $agreement->supervisor_user_mail;
    $mname = (isset($agreement->mentor_name) ? $agreement->mentor_name : $agreement->mentor_user_name);
    $mentor_email = $agreement->mentor_user_mail;

    $output = '';

    $output .= '<dl class="view_record">';

    $output .= '<dt>';
    $output .= t('Evaluation text');
    $output .= '</dt>';
    $output .= '<span class="ui-icon ui-icon-arrowreturn-1-e"></span>';
    $output .= '<dd>';
    $output .= $agreement->evaluation;
    $output .= '</dd>';

    $output .= '<dt>';
    $output .= t('Student');
    $output .= '</dt>';
    $output .= '<span class="ui-icon ui-icon-arrowreturn-1-e"></span>';
    $output .= '<dd>';
    $output .= $sname . ' (' . $student_email . ')';
    $output .= '<BR/><span>' . ($agreement->student_completed ?
                    ('<b>' . t('The student signed the project as complete') . '</b>') : t('You can mark the project as complete ')) . '</span>';
    $output .= '</dd>';

    $output .= '<dt>';
    $output .= t('Supervisor');
    $output .= '</dt>';
    $output .= '<span class="ui-icon ui-icon-arrowreturn-1-e"></span>';
    $output .= '<dd>';
    $output .= $spname . ' (' . $supervisor_email . ')';
    $output .= '<BR/><span>' . ($agreement->supervisor_completed ?
                    ('<b>' . t('The supervisor signed the project as complete') . '</b>') : t('The supervisor can mark the project as complete ')) . '</span>';
    $output .= '</dd>';

    $output .= '<dt>';
    $output .= t('Mentor');
    $output .= '</dt>';
    $output .= '<span class="ui-icon ui-icon-arrowreturn-1-e"></span>';
    $output .= '<dd>';
    $output .= $mname . ' (' . $mentor_email . ')';
    $output .= '<BR/><span>' . ($agreement->mentor_completed ?
                    ('<b>' . t('The mentor signed the project as complete') . '</b>') : t('The mentor can mark the project as complete ')) . '</span>';
    $output .= '</dd>';

    $output .= '</dl>';

    return $output;
}

/**
 * Replaces the unordered list used show a member record
 * @param unknown $record - array of key => values (form)
 * @param string $member_type - used to show what type of entity is supposed to be used by the generated CODE
 * @return string - a bunch of HTML
 */
function formatMemberRecordNice($record, $type, $target = '') {
    $key_name = Groups::keyField($type);
    $id = $record->$key_name;
    $member_type = '';
    switch ($type) {
        case _ORGANISATION_GROUP: // organisations invite mentors
            $parent_member_type = 'organisation administrator';
            $member_type = _MENTOR_TYPE;
            break;
        case _INSTITUTE_GROUP: // institutes invite supervisors
            $parent_member_type = 'institution administrator';
            $member_type = _SUPERVISOR_TYPE;
            break;
        case _STUDENT_GROUP: // studentgroups invite students
            $member_type = _STUDENT_TYPE;
            $parent_member_type = 'institution administrator or teacher';
            break;
        default: $member_type = 'user';
    }

    $output = '';
    $output .= '';
    $output .= '<dl class="view_record">';
    $i_am_owner = FALSE;
    // check to see if there is an 'owner_id field'
    if (isset($record->owner_id)) {
        $owner_details = '';
        if (Users::getMyId() == $record->owner_id) {
            // owner is me
            $owner_details = t('You');
            $i_am_owner = TRUE;
        } else {
            // else owner someone else, get their details
            $user = Users::getParticipantBasicSubset($record->owner_id);
            if ($user) {
                if (isset($user->fullname)) {
                    $owner_details = $user->fullname . ' (' . $user->mail . ')';
                } else {
                    $owner_details = $user->name . ' (' . $user->mail . ')';
                }
            } else {
                $owner_details = tt('We could not find the owner of this %1$s', t_type($type));
            }
        }
        $output .= '<dt>';
        $output .= t('Owner');
        $output .= '</dt>';
        $output .= '<span class="ui-icon ui-icon-arrowreturn-1-e"></span>';
        $output .= '<dd>';
        $output .= $owner_details;
        $output .= '</dd>';
    }
    $is_some_admin = Users::isSomeAdmin();
    // just loop through the rest of the fields
    foreach ($record as $key => $val) {
        // don't show any field ending with '_id' and do not show the codes unless you are an admin
        if ((substr_compare($key, '_id', -strlen('_id')) != 0) &&
                ((($key !== 'code') && ($key !== 'owner_code')) || ($is_some_admin || $i_am_owner))
        ) {
            $attribute_str = '<dt>';
            $attribute_str .= ucfirst(str_replace('_', ' ', $key));
            $attribute_str .= '</dt>';
            $attribute_str .= '<span class="ui-icon ui-icon-arrowreturn-1-e"></span>';
            $attribute_str .= '<dd>';
            $attribute_str .= $val;
            $server = _VALS_SOC_FULL_URL;
            if (($key == 'code')) {
                $output .= $attribute_str . '<br/>';
                $output .= '<i>' . tt('You can use this code to invite a %1$s to participate.', t($member_type));
                $output .= '<br/>';
                $output .= '<br/>';
                $output .= tt('To sign up a %1$s for your %2$s, send them this code and direct them to ' .
                        $server . '/user/register where they can use it to sign up.', t($member_type), t_type($type));
                $output .= '<br/>';
                $output .= '<br/>';
                $output .= t('Alternatively click the button below to send an email containing signup instructions') . '</i>';
                $output .= '<br/>';
                $invite_mentor_action = "onclick='ajaxCall(\"administration\", \"inviteform\", {type: \"$type\", id: $id, path: \"\", target: \"$target\", subtype: \"$member_type\"}, \"formResult\", \"html\", \"$target\");'";
                $output .= "<input type='button' value='" . tt('Invite %1$s', t($member_type)) . "' $invite_mentor_action/>";
            } elseif (($key == 'owner_code') && ($type != _STUDENT_GROUP)) {
                $output .= $attribute_str . '<br/>';
                $output .= '<i>' . tt('You can use the following code to invite a colleague to manage this %1$s together ', t_type($type)) . '</i>';
                $output .= '<br/>';
                $output .= '<br/>';
                $output .= tt('To sign up a %1$s for your %2$s, send them this code and direct them to ' .
                        $server . '/user/register where they can use it to sign up.', t($parent_member_type), t_type($type));
                $output .= '<br/>';
                $output .= '<br/>';
                $output .= t('Alternatively click the button below to send an email containing signup instructions') . '</i>';
                $output .= '<br/>';
                $invite_org_admin_action = "onclick='ajaxCall(\"administration\", \"inviteform\", {type: \"$type\", id: $id, path: \"\", target: \"$target\", subtype: \"$parent_member_type\"}, \"formResult\", \"html\", \"$target\");'";
                $output .= "<input type='button' value='" . tt('Invite %1$s', t($parent_member_type)) . "' $invite_org_admin_action/>";
            } elseif ($key !== 'owner_code') {
                $output .= $attribute_str;
            }
            $output .= '</dd>';
        }
    }
    $output .= '</dl>';
    return $output;
}

function renderStudents($group_selection = '', $students = '') {

    if (!$students) {
        //if we pass empty value to getStudents the current supervisor is assumed and we
        //get all his students
        $students = Users::getAllStudents($group_selection);
    }
    return formatUsersNice($students, _STUDENT_TYPE, t('There are no students yet'));
}

function renderSupervisors($group_selection = '', $supervisors = '') {
    if (!$supervisors) {
        //if we pass empty value to getSupervisors the current institute_admin is assumed and we
        //get all his supervisors in his/her institute
        $supervisors = Users::getSupervisors($group_selection);
    }
    return formatUsersNice($supervisors, _SUPERVISOR_TYPE, t('There are no supervisors yet in this institute'), TRUE);
}

function renderUsers($type = '', $users = '', $group_selection = '', $group_type = '', $show_title = FALSE) {
    //If no Users dataset is passed on, retrieve them based on the other arguments
    if (!$users) {
        $users = Users::getUsers($type, $group_type, $group_selection);
        if (!($users && $users->rowCount())) {
            $users = null;
        }
    }

    $group_type = $group_type ? t_type($group_type) : t('environment');
    $type_nice = str_replace('_', ' ', $type);
    $empty_message = $group_selection ? tt('There is no %1$s yet in this %2$s', t($type_nice), $group_type) :
            tt('There are no %1$s yet.', t($type_nice));
    return formatUsersNice($users, $type, $empty_message, $show_title);
}

function renderDefaultField($field, $obj, $alternative_field = '', $not_found_item = '') {
    static $unknown = null;

    if (!$unknown) {
        $unknown = t('The %1$s is not known yet');
    }
    if (isset($obj->$field) && $obj->$field) {
        return $obj->$field;
    } elseif ($alternative_field && isset($obj->$alternative_field) && $obj->$alternative_field) {
        return $obj->$alternative_field;
    } else {
        return sprintf($unknown, $not_found_item ? t($not_found_item) : t(str_replace('_', ' ', $field))
        );
    }
}

function renderProposal($proposal, $target = 'none', $follow_action = 'show') {
    //A proposal consists of: fields = array('proposal_id', 'owner_id', 'org_id', 'inst_id',
    //'supervisor_id', 'pid', 'solution_short', 'solution_long', 'state',);
    $propid = $proposal->proposal_id;
    $buttons = '';
    if (Users::isStudent() && Groups::isOwner(_PROPOSAL_OBJ, $propid) && $proposal->state != 'published') {
        $delete_action = "onclick='if(confirm(\"" . t('Are you sure you want to delete this proposal?') .
                '")){ajaxCall("proposal", "delete", ' .
                '{type: "proposal", proposal_id: ' . $propid . ', target: "' . $target . '"}, "refreshTabs", "json", ' .
                "[\"proposal\", \"$target\", \"proposal\", \"our_content\", \"$follow_action\"]);}'";
        $edit_action = "onclick='ajaxCall(\"proposal\", \"edit\", {type: \"proposal\", proposal_id: $propid, target: " .
                "\"$target\", format:\"html\"}, \"formResult\", \"html\", [\"$target\", \"proposal\"]);'";
        $buttons .= "<div class='totheright' id='proposal_buttons'><input type='button' value='" . t('edit') . "' $edit_action/>";
        $buttons .= "<input type='button' value='" . t('delete') . "' $delete_action/></div>";
    }
    $content = "<div id='msg_$target'></div>
	$buttons" .
            "<h1>" . ($proposal->title ? $proposal->title : Proposal::getDefaultName('', $proposal)) . " (" .
            renderDefaultField('state', $proposal) . ")</h1>

	<div id='personalia'>
	<h3>Parties involved</h3>
	<ul>
	<li>" . t('Supervisor') . ": " . renderDefaultField('supervisor_name', $proposal, 'supervisor_user_name') . "</i>" .
            "<li>" . t('Mentor') . ": " . renderDefaultField('mentor_name', $proposal, 'mentor_user_name') . "</i>" .
            "<li>" . t('Student') . ": " . renderDefaultField('student_name', $proposal, 'name') . "</i>" .
            "<li>" . t('Institute') . ": " . renderDefaultField('i_name', $proposal) . "</i>" .
            "<li>" . t('Organisation') . ": " . renderDefaultField('o_name', $proposal) . "</i>" .
            "</ul>
			</div>" .
            "<div id='project'>
			" . t('Project') . ": " . $proposal->pr_title . "
			</div>" .
            "<div id='proposal_text'>
			<h3>" . t('Solution Summary') . "</h3>
			" . renderDefaultField('solution_short', $proposal) . "<br/>" .
            "<a href='javascript:void(0)' data='off' onclick='makeVisible(\"solution_$propid\");'>" . t('more') . "</a>" .
            //"<input type='button' value='View more' onclick='makeVisible(\"solution_$propid\");'/>
            "
			<div id='solution_$propid' class='invisible'>
			<h3>Solution</h3>
			" . renderDefaultField('solution_long', $proposal, '', 'solution text') . "
			</div>
			</div>";

    module_load_include('inc', 'vals_soc', 'includes/ui/comments/threaded_comments');
    $content .= initComments($propid, _PROPOSAL_OBJ);
    return $content;
}

/*
 * This is a new function to show an overview of the proposals
 */

function renderProposals($type = '', $proposals = '', $target = '', $render_details = TRUE) {
    //If no proposals dataset is passed on, retrieve them based on the other arguments
    if (!$proposals) {
        $proposals = Proposal::getProposalsPerOrganisation('', '', $type, $render_details);
        if (!($proposals)) {
            $proposals = null;
        }
    }
    if ($proposals) {
        $key = 'proposal_id';
        $s = "<ul class='grouplist'>";
        foreach ($proposals as $member) {
            $id = $member->$key;
            $s .= "<li>";
            $s .= "<a href='javascript:void(0);' onclick=\"ajaxCall('proposal', " .
                    "'view', {type:'$type', id:$id, target:'$target'}, '$target');\">" .
                    ($member->title ? : ($render_details ? "Project: " . $member->pr_title : 'No title yet')) . "</a>" .
                    " <i>From: " . $member->i_name . "</i>";
            $s .= "</li>";
        }
        $s .= "</ul>";
    } else {
        $type = $type ? : _STUDENT_GROUP;
        $s = tt('There are no %1$s %2$s yet.', t_type($type), t('proposals'));
    }
    return $s;
}

function renderProjects($organisation_selection = '', $projects = '', $target = '', $inline = FALSE, $reload_data = TRUE, $owner_only = FALSE) {
    if (!$projects && $reload_data) {
        $projects = Project::getProjects('', ($owner_only ? $GLOBALS['user']->uid : null), $organisation_selection);
    }
    $target_set = !empty($target);
    if ($projects) {
        $s = "<ol class='projectlist'>";
        foreach ($projects as $project) {
            $project = objectToArray($project);
            $s .= "<li>";
            if (!$target_set || $inline) {
                $target = "show_${project['pid']}";
            }
            $inline = $inline ? 1 : 0;
            $s .= "<a href='javascript: void(0);' onclick='" .
                    //($target_set ? "" : "\$jq(\"#$target\").toggle();").
                    "ajaxCall(\"project\", \"view\", {id:${project['pid']},type:\"project\", target:\"$target\", inline:$inline}, \"$target\");'>${project['title']}</a>";
            if (!$target_set || $inline) {
                $s .= "<div id='$target' ></div>";
            }
            $s .= "</li>";
        }
        $s .= "</ol>";
        return $s;
    } else {
        return t('You have no projects yet');
    }
}

function renderProject($project = '', $target = '', $inline = FALSE, $all_can_edit = _VALS_SOC_MENTOR_ACCESS_ALL) {
    if (!$project) {
        return t('I cannot show this project. It seems empty.');
    }
    if (is_object($project)) {
        $project = objectToArray($project);
    } else {
        //It is NOT an object, so: array
    }
    $key_name = Groups::keyField(_PROJECT_OBJ);
    $id = $project[$key_name];
    $type = _PROJECT_OBJ;
    $role = getRole();

    $content = "<div class=\"totheright\">";
    if (_STUDENT_TYPE == getRole()) {
        $content .="<br/><br/><input type='button' onclick=\"getProposalFormForProject(" . $project['pid'] .
                ")\" value='.t( 'Submit proposal for this project').'/>";
    }
    $is_inproject_organisation = Groups::isAssociate(_PROJECT_OBJ, $id);
    //If not inline and either owner or mentor and mentors allowed to edit...
    if (!$inline && (($all_can_edit && $is_inproject_organisation) || Groups::isOwner(_PROJECT_OBJ, $id))) {
        $delete_action = "onclick='if(confirm(\"" . t('Are you sure you want to delete this project?') . "\")){ajaxCall(\"project\", \"delete\", {type: \"$type\", id: $id, target: \"$target\"}, \"refreshTabs\", \"json\", [\"$type\", \"$target\", \"project\"]);}'";
        $edit_action = "onclick='ajaxCall(\"project\", \"edit\", {type: \"$type\", id: $id, target: \"$target\"}, \"formResult\", \"html\", [\"$target\", \"project\"]);'";
        $content .= "<input type='button' value='" . t('edit') . "' $edit_action/>";
        $content .= "<input type='button' value='" . t('delete') . "' $delete_action/>";
    }
    $content .="</div>";
    $content .= "<h2>" . $project['title'] . "</h2>";
    if ($is_inproject_organisation) {
        $content .= "<h3>Statistics</h3>";
        $content .= "<p>Number of student views: " . $project['views'] . "<BR>" .
                "Number of times marked by a student: " . $project['likes'] . "</p>";
    }
    $content .= '<p>' . $project['description'] . '</p>';
    if ($project['url']) {
        $content .= '<p>' . tt('More information can be found at %1$s', "<a href='${project['url']}'> ${project['url']}</a>") . '</p>';
    }

    if (!$inline) {
        if (getRole() != _ANONYMOUS_TYPE) {
            module_load_include('inc', 'vals_soc', 'includes/ui/comments/threaded_comments');
            $content .= initComments($id, _PROJECT_OBJ);
        }
    }

    return $content;
}

function renderGroups($supervisor_selection = '', $groups = '') {
    if (!$groups) {
        //if we pass empty value to getGroups the current supervisor is assumed
        $groups = Groups::getGroups($supervisor_selection);
    }
    if ($groups) {
        $s = "<ul class='grouplist'>";
        foreach ($groups as $group) {
            $s .= "<li>";
            // $member_url = "/vals/actions/group"
            $s .= "<a href='javascript: void(0);' onclick='ajaxCall(\"administration\", \"showmembers\", {studentgroup_id:${group['studentgroup_id']},type:\"group\"}, \"members_${group['studentgroup_id']}\");'>${group['name']}</a>: ${group['description']}";
            $s .= "<div id='members_${group['studentgroup_id']}'></div>";
            $s .= "</li>";
        }
        $s .= "</ul>";
        return $s;
    } else {
        return t('You have no groups yet');
    }
}

function renderOrganisations($type = '', $organisations = '', $organisation_head = '', $target = '') {
    //If no organisations dataset is passed on, retrieve them based on the other arguments
    if (!$organisations) {
        $organisations = Groups::getGroups($type, $organisation_head);
        if (!($organisations && $organisations->rowCount())) {
            $organisations = null;
        }
    }
    if ($organisations && $organisations->rowCount()) {
        $key = Groups::keyField($type);
        $s = "<ul class='grouplist'>";
        foreach ($organisations as $member) {
            $id = $member->$key;
            $s .= "<li>";
            $s .= "<a href='javascript:void(0);' onclick=\"ajaxCall('administration', 'view', {type:'$type', id:$id, target:'$target'}, '$target');\">" . $member->name . "</a>";
            $s .= "</li>";
        }
        $s .= "</ul>";
        return $s;
    } else {
        $type = $type ? : _STUDENT_GROUP;
        return $organisation_head ? tt('You have no %1$s yet.', t_type($type)) :
                tt('There is no %1$s yet.', t_type($type));
    }
}

function renderOrganisation($type, $organisation = '', $organisation_owner = '', $target = '', $show_buttons = true) {
    if (!$organisation) {
        $organisation = Groups::getGroup($type, '', $organisation_owner);
    }
    $key_name = Groups::keyField($type);
    $id = $organisation->$key_name;
    if ($organisation) {
        $s = '';
        if ($show_buttons && user_access('vals admin register')) {
            $pPath = request_path();
            $edit_action = "onclick='ajaxCall(\"administration\", \"edit\", {type: \"$type\", id: $id, path: \"$pPath\", target: \"$target\"}, " .
                    (($type == _STUDENT_GROUP) ? "\"$target\");'" : "\"formResult\", \"html\", \"$target\");'");
            $s .= "<div class='totheright'>";
            $s .= "	<input type='button' value='" . t('edit') . "' $edit_action/>";
            // has the org signup period ended if so user cant add/delete entries, only edit
            if (vals_soc_access_check("dashboard/$type/administer/add_or_delete")) {
                $delete_action = "onclick='if(confirm(\"" . tt('Are you sure you want to delete this %1$s?', t_type($type)) .
                        "\")){ajaxCall(\"administration\", \"delete\", {type: \"$type\", id: $id, path: \"$pPath\", target: \"$target\"}, \"refreshTabs\", \"json\", [\"$type\", \"$target\", \"administration\"]);}'";
                $s .= "	<input type='button' value='" . t('delete') . "' $delete_action/>";
            }
            $s .= "</div>";
            //$sub_type_user = '';
        }
        $s .= formatMemberRecordNice($organisation, $type, $target);
        if ($type == _STUDENT_GROUP) {
            $s .= "<h2>" . t('Members') . "</h2>";
            $students = Users::getStudents($id);
            $s .= renderStudents('', $students);
        }
        return $s;
    } else {
        return tt('You have no %1$s registered yet', $type);
    }
}

function renderAgreement($type, $agreement = '', $agreement_owner = '', $target = '', $show_buttons = true) {
    if (!$agreement) {
        //$agreement = Groups::getGroup($type, '', $organisation_owner);
    }
    $key_name = Groups::keyField($type);
    $id = $agreement->$key_name;
    if ($agreement) {
        $s = '';
        //if ($show_buttons && user_access('vals admin register')){
        $pPath = request_path();
        $edit_action = "onclick='ajaxCall(\"agreement\", \"edit\", {type: \"$type\", id: $id, path: \"$pPath\", target: \"$target\"}, " .
                (($type == _STUDENT_GROUP) ? "\"$target\");'" : "\"formResult\", \"html\", \"$target\");'");
        $s .= "<div class='totheright'>";
        $s .= "	<input type='button' value='" . t('edit') . "' $edit_action/>";
        $s .= "</div>";
        //$sub_type_user = '';
        //}
        $s .= formatAgreementRecordNice($agreement, $target);
        return $s;
    } else {
        return tt('You have no %1$s registered yet', $type);
    }
}

function renderFinalisation($type, $agreement = '', $agreement_owner = '', $target = '', $show_buttons = true) {
    if (!$agreement) {
        //$agreement = Groups::getGroup($type, '', $organisation_owner);
    }
    $key_name = Groups::keyField($type);
    $id = $agreement->$key_name;
    $my_type = getRole();
    $completed_prop = "${my_type}_completed";
    $disabled = ($agreement->$completed_prop) ? "disabled='disabled' " : "";
    if ($agreement) {
        $s = '';
        $pPath = request_path();
        $edit_action = "onclick='ajaxCall(\"agreement\", \"sign_complete\", {type: \"$type\", id: $id, path: \"$pPath\", target: \"$target\"}, " .
                (($type == _STUDENT_GROUP) ? "\"$target\");'" : "\"formResult\", \"html\", \"$target\");'");
        $s .= "<div class='totheright'>";
        $s .= "	<input type='button' $disabled value='" . t('sign as complete') . "' $edit_action/>";
        $s .= "</div>";
        $s .= formatFinalisationRecordNice($agreement, $target);
        return $s;
    } else {
        return tt('You have no %1$s registered yet', $type);
    }
}
