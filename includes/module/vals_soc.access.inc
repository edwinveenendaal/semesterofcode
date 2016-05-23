<?php

module_load_include('php', 'vals_soc', 'includes/classes/Timeline');

function hasRole($roles)
{
    global $user;
    $check = array_intersect($roles, array_values($user->roles));
    return empty($check) ? FALSE : TRUE;
}

function hasPermission($permission)
{
    return user_access($permission);
}

function checkPeriodOrTimelineIndependence($check){
    return (!_VALS_USE_TIME_SCHEDULE) || Timeline::getInstance()->$check();
}

/*
 * The access strings take the form of relative path being requested a la
 * 'dashboard/organisations/browse' where the actual url was
 * http://localhost/vals/dashboard/organisations/browse. 
 * (although in some cases there is no real url eg 'dashboard/projects/apply')
 * The default case will also check the permission...
 * 		hasPermission($access_string);
 * so you can also pass the permission a la
 * 	   'vals browse proposals'
 */

function pageAccessCheck($access_string)
{
    global $user;
    
    if (_VALS_TEST_UI_ONLY && Users::isAdmin()) {
        return TRUE;
    }
    switch ($access_string) {
        case 'dashboard/members':
            return hasPermission('vals admin register') && 
            checkPeriodOrTimelineIndependence('organisationSignupPeriodOpened');
            break;
        case 'dashboard/overview':
            return !Users::isStudent();
            break;
        case 'dashboard/organisation/view':
            return hasPermission('vals admin view') && checkPeriodOrTimelineIndependence('organisationSignupPeriodOpened');
            break;
        case 'dashboard/organisation/members':
            return (hasPermission('vals admin register') || hasPermission('vals admin view')) && 
                checkPeriodOrTimelineIndependence('organisationSignupPeriodOpened');
            break;
        // The following can be accessed by 'vals admin register' once the org applications open
        case 'dashboard/organisation/administer':
        case 'dashboard/institute/administer':
        case 'dashboard/institute/members':
        case 'dashboard/institute/group/administer':
            return hasPermission('vals admin register') && checkPeriodOrTimelineIndependence('organisationSignupPeriodOpened');
            break;
        case 'dashboard/institute/view':
            return 
                (hasPermission('vals admin register') && checkPeriodOrTimelineIndependence('organisationSignupPeriodOpened')) ||
                (hasRole(array(_STUDENT_TYPE)) && checkPeriodOrTimelineIndependence('isAfterOrgsAnnouncedDate')); // let students see this once orgs announced
            break;
        // The following can be accessed by 'vals admin register' once the org applications open
        // but adding/deleting entries is not allowed after the org signup period ends (edit existing only)
        case 'dashboard/organisation/administer/add_or_delete':
        case 'dashboard/institute/administer/add_or_delete':
            return hasPermission('vals admin register') && checkPeriodOrTimelineIndependence('organisationSignupPeriodOpened') && 
                !checkPeriodOrTimelineIndependence('isAfterOrganisationSignupPeriod');
            break;
        case 'dashboard/studentgroup/administer/add_or_delete':
            return hasPermission('vals admin register') && checkPeriodOrTimelineIndependence('isStudentsSignupPeriod');
            break;
        case 'dashboard/projects/administer':
        case 'dashboard/projects/administer/mine':
            return hasPermission('vals admin projects') && checkPeriodOrTimelineIndependence('organisationSignupPeriodOpened');
            break;
        case 'dashboard/projects/marked': return hasRole(array(_STUDENT_TYPE));
            break;
        case 'projects/rate': return hasRole(array(_SUPERVISOR_TYPE)) && Timeline::getInstance()->isProgramActive();
            break;
        case 'projects/comment': return Users::isUser() && Timeline::getInstance()->isProgramActive();
            break;
        case 'projects/browse':
        case 'dashboard/projects/browse':
            // any user must have 'val browse projects' permission
            if (hasPermission('vals browse projects')) {
                // students can only see the projects after the organisations have been announced
                if (hasRole(array(_STUDENT_TYPE)) &&
                    checkPeriodOrTimelineIndependence('isAfterOrgsAnnouncedDate')) {
                    return TRUE;
                } else {
                    // everyone else with 'vals browse projects' permission can see projects once org applications open
                    return (checkPeriodOrTimelineIndependence('organisationSignupPeriodOpened'));
                }
            } else {
                return FALSE;
            }
            break;
        case 'projects/apply':
        case 'dashboard/projects/apply':
            // only students with 'vals apply projects' permission can apply for a project AND only during the student signup period
            if (hasRole(array(_STUDENT_TYPE)) && hasPermission('vals apply projects') && checkPeriodOrTimelineIndependence('isStudentsSignupPeriod')) {
                return TRUE;
            } else {
                return FALSE;
            }
            break;
        case 'organisations/browse':
        case 'dashboard/organisations/browse':
            // any user must have 'val browse organisations' permission
            if (hasPermission('vals browse organisations')) {
                // students AND unauthenticated users can only see the orgs after the organisations have been announced
                if ((hasRole(array(_STUDENT_TYPE)) || hasRole(array(_ANONYMOUS_TYPE))) &&
                    checkPeriodOrTimelineIndependence('isAfterOrgsAnnouncedDate')) {
                    return TRUE;
                } else {
                    return (checkPeriodOrTimelineIndependence('organisationSignupPeriodOpened'));
                }
            } else {
                return FALSE;
            }
            break;
        case 'dashboard/proposals/overview':
            return (!hasRole(array(_ANONYMOUS_TYPE))) && (!hasRole(array(_STUDENT_TYPE, _MENTOR_TYPE, _ORGADMIN_TYPE)));
            break;
        case 'dashboard/proposals/browsebytype':
        case 'dashboard/proposals/browsebytype/mine':
        case 'dashboard/proposals/browse':
            return (!hasRole(array(_ANONYMOUS_TYPE))) && (!hasRole(array(_STUDENT_TYPE)));
            break;
        case 'dashboard/proposals/mine':
            return hasRole(array(_STUDENT_TYPE));
            break;
        case 'institutes/browse':
            if ((hasRole(array(_STUDENT_TYPE)) || hasRole(array(_ANONYMOUS_TYPE))) &&
                checkPeriodOrTimelineIndependence('isAfterOrgsAnnouncedDate')) {
                return TRUE;
            } else {
                // everyone else with 'vals browse projects' permission can see projects once org applications open
                return (checkPeriodOrTimelineIndependence('organisationSignupPeriodOpened'));
            };
            break;
        case 'dashboard/projects/mine':
        case 'dashboard/projects/mine/finalise'://TODO THIS CONDITION SHOULD BE STRONGER
        case 'dashboard/projects/mine/agreement':
            if (!hasRole(array(_ANONYMOUS_TYPE)) && checkPeriodOrTimelineIndependence('hasStudentSignupPeriodOpened')) {
                return TRUE;
            } else {
                return FALSE;
            }
            break;

        case 'dashboard/projects/offers':
            if (hasRole(array(_STUDENT_TYPE)) && checkPeriodOrTimelineIndependence('hasStudentSignupPeriodOpened')) {
                return TRUE;
            } else {
                return FALSE;
            }
            break;
        default:
            return hasPermission($access_string);
    }
}
