<?php

module_load_include('inc', 'vals_soc', 'includes/module/vals_soc.access');
/*
 * Note: changing type of MENU_NORMAL_ITEM to MENU_CALLBACK and refreshing the cache
 * does NOT make the menu item disappear.  (its still cached. you have to manually do it
 * at http://localhost/vals/admin/structure/menu/manage/navigation)
 * 
 */

function vals_soc_menu_handler() {
    $items = array();
    //########################## HOME LINK #######################
    $items[_VALS_SOC_FULL_URL] = array(
        'title' => t('Home'),
        'type' => MENU_NORMAL_ITEM,
        'access callback' => TRUE,
        'page callback' => 'drupal_goto',
        'page arguments' => array(_VALS_SOC_FULL_URL),
    );
    //########################## PROGRAM SETTINGS LINKS #######################
    $progSettings = t('Program Settings');
    $items['admin/config/vals-soc'] = getProgramSettingsLink('access administration pages', t('VALS Summer of code'), MENU_NORMAL_ITEM);
    $items['admin/config/vals-soc/program_settings'] = getProgramSettingsLink('access administration pages', $progSettings, MENU_NORMAL_ITEM);
    $items['admin/config/vals-soc/program_settings/messages'] = getEditMessagesLink('access administration pages', MENU_NORMAL_ITEM);
    $items['admin/config/vals-soc/program_settings/timeline'] = getEditTimelineLink('access administration pages', MENU_NORMAL_ITEM);
    $items['admin/config/vals-soc/program_settings/frontpage'] = getEditFrontPageLink('access administration pages', MENU_NORMAL_ITEM);



    //########################## DASHBOARD LINKS ##############################
    $items['dashboard'] = getDashboardLink(); // main dashboard url
    # admin pages (as above) also accessible from the dashboard (only for admin user)
    $items['dashboard/program_settings'] = getProgramSettingsLink('access administration pages', $progSettings, MENU_CALLBACK);
    $items['dashboard/program_settings/messages'] = getEditMessagesLink('access administration pages', MENU_CALLBACK);
    $items['dashboard/program_settings/frontpage'] = getEditFrontPageLink('access administration pages', MENU_CALLBACK);
    $items['dashboard/program_settings/timeline'] = getEditTimelineLink('access administration pages', MENU_CALLBACK);

    $items['dashboard/members'] = getMembersLink('dashboard/members');
    $items['dashboard/overview'] = getOverviewLink('dashboard/overview');

    //allow institutes to add/edit their institute details
    $items['dashboard/institute/administer'] = getAdministerLink('dashboard/institute/administer', _INSTITUTE_GROUP, false);
    $items['dashboard/institute/view'] = getAdministerLink('dashboard/institute/view', _INSTITUTE_GROUP, false);
    // allow institutes to view supervisors/other instadmins
    $items['dashboard/institute/members'] = getEntityMembersLink('dashboard/institute/members', _INSTITUTE_GROUP, false);
    // used just for institutes to allow creation of student groups
    $items['dashboard/institute/group/administer'] = getGroupAdministerLink('dashboard/institute/group/administer');

    //allow organisations to add/edit their organisation details
    $items['dashboard/organisation/administer'] = getAdministerLink('dashboard/organisation/administer', _ORGANISATION_GROUP);
    // allow organisations to view mentors/other orgadmins
    $items['dashboard/organisation/members'] = getEntityMembersLink('dashboard/organisation/members', _ORGANISATION_GROUP);
    // allow mentors to VIEW the admin details but not add new orgs or edit like org admin can do
    $items['dashboard/organisation/view'] = getAdministerLink('dashboard/organisation/view', _ORGANISATION_GROUP);

    // organisation actions - let users see all the announced organisations
    //$items['dashboard/organisations/browse'] = getOrganisationBrowseLink('dashboard/organisations/browse');
    // project actions
    $items['dashboard/projects/administer'] = getProjectsAdministerLink('dashboard/projects/administer');
    $items['dashboard/projects/administer/mine'] = getMyProjectsAdministerLink('dashboard/projects/administer/mine');
    $items['dashboard/projects/browse'] = getProjectsBrowseLink('dashboard/projects/browse');
    $items['dashboard/projects/marked'] = getFavouriteProjectsLink('dashboard/projects/marked');
    $items['dashboard/projects/offers'] = getMyProjectOffersLink('dashboard/projects/offers');
    $items['dashboard/projects/mine'] = getMyProjectLink('dashboard/projects/mine'); // link for the student to get his/her accepted project info
    $items['dashboard/projects/mine/agreement'] = getMyProjectAgreementLink('dashboard/projects/mine/agreement');
    $items['dashboard/projects/mine/finalise'] = getMyProjectAgreementLink('dashboard/projects/mine/finalise');
    // proposal actions
    $items['dashboard/proposals/browse'] = getProposalsBrowseLink('dashboard/proposals/browse'); // browse all proposals - not used yet - might be part of admin view at some stage
    $items['dashboard/proposals/overview'] = getProposalsOverviewLink('dashboard/proposals/overview');
    $items['dashboard/proposals/mine'] = getMyProposalBrowseLink('dashboard/proposals/mine'); //browse only my proposals - student view
    $proposal_grouping = (Users::isMentor() ? 'project' : 'student');
    $items['dashboard/proposals/browsebytype'] = getProposalsBrowseByTypeLink('dashboard/proposals/browsebytype', $proposal_grouping); // browse all proposals 
    $items['dashboard/proposals/browsebytype/mine'] = getMyProposalsBrowseByTypeLink('dashboard/proposals/browsebytype/mine', $proposal_grouping); // browse all proposals belonging to my projects only
    //########################## TOP LEVEL LINKS ##############################
    // These are usually used when we want unauthenticated users to view something not via the dashboard
    $items['institutes/browse'] = getInstituteBrowseLink('institutes/browse');
    $items['organisations/browse'] = getOrganisationBrowseLink('organisations/browse');
    // Allows user to browse projects registered in the system
    $items['projects/browse'] = getProjectsBrowseLink('projects/browse');

    $items['http://semesterofcode.com/'] = array(
        'title' => t('About Vals'),
        'type' => MENU_NORMAL_ITEM,
        'access callback' => TRUE,
        'page callback' => 'drupal_goto',
        'page arguments' => array('http://semesterofcode.com/')
    );
    // open this in a new window
    $items['http://semesterofcode.com/']['options']['attributes']['target'] = '_blank';

    $items['user-guide'] = array(
        'title' => t('User guide'),
        'type' => MENU_NORMAL_ITEM,
        'access callback' => TRUE,
        'description' => t('User guide page'),
        'page callback' => 'drupal_goto',
        'page arguments' => array(_VALS_SOC_FULL_URL . '/sites/all/modules' . '/vals_soc/help/index.php')
    );
    // open this in a new window
    $items['user-guide']['options']['attributes']['target'] = '_blank';



    $items[_VALS_SOC_FULL_URL]['weight'] = 0;
    $items['dashboard']['weight'] = 1;
    $items['institutes/browse']['weight'] = 4;
    $items['organisations/browse']['weight'] = 5;
    $items['projects/browse']['weight'] = 6;
    $items['http://semesterofcode.com/']['weight'] = 8;



    return $items;
}

/* * *******************************************************************************************
 * 	 PLACE FUNCTIONS BELOW THAT ARE ONLY ACCESSIBLE TO THE ADMIN USER
 * *******************************************************************************************
 */

/**
 * ADMIN ONLY LINK
 * @param unknown $path
 * @param unknown $title
 * @param unknown $menu_type
 * @return multitype:string unknown multitype:unknown  NULL
 */
function getProgramSettingsLink($path, $title, $menu_type) {
    return array(
        'title' => $title,
        'type' => $menu_type,
        'page callback' => 'vals_soc_admin_program_settings_page',
        'access arguments' => array($path),
        'description' => t('Allow Administration on the summer of code module.'),
        'file' => 'vals_soc.pages.inc',
        'file path' => _VALS_SOC_PATH . '/includes/module',
    );
}

/**
 * ADMIN ONLY LINK
 * @param unknown $path
 * @param unknown $menu_type
 * @return multitype:string unknown multitype:string  NULL multitype:unknown
 */
function getEditMessagesLink($path, $menu_type) {
    return array(
        'title' => t('Edit Messages'),
        'type' => $menu_type,
        'page callback' => 'drupal_get_form',
        'page arguments' => array('vals_soc_admin_messages_form'),
        'access arguments' => array($path),
        'description' => t('Edit the email messages that are sent to users.'),
        'file' => 'vals_soc.forms.inc',
        'file path' => _VALS_SOC_PATH . '/includes/module',
    );
}

/**
 * ADMIN ONLY LINK
 * @param unknown $path
 * @param unknown $menu_type
 * @return multitype:string unknown multitype:string  NULL multitype:unknown
 */
function getEditFrontPageLink($path, $menu_type) {
    return array(
        'title' => t('Edit Frontpage blocks'),
        'type' => $menu_type,
        'page callback' => 'drupal_get_form',
        'page arguments' => array('vals_soc_admin_frontpage_form'),
        'access arguments' => array($path),
        'description' => t('Edit the frontpage.'),
        'file' => 'vals_soc.forms.inc',
        'file path' => _VALS_SOC_PATH . '/includes/module',
    );
}

/**
 * ADMIN ONLY LINK
 * @param unknown $path
 * @param unknown $menu_type
 * @return multitype:string unknown multitype:string  NULL multitype:unknown
 */
function getEditTimelineLink($path, $menu_type) {
    return array(
        'title' => t('Edit Timeline'),
        'type' => $menu_type,
        'page callback' => 'drupal_get_form',
        'page arguments' => array('vals_soc_admin_timeline_form'),
        'access arguments' => array($path),
        'description' => t('Edit the timeline and other settings.'),
        'file' => 'vals_soc.forms.inc',
        'file path' => _VALS_SOC_PATH . '/includes/module',
    );
}

/**
 * ALL USERS REGISTERED WITH A 2ND ROLE HAVE SOME SORT OF DASHBOARD
 * 
 * USES ROLE_PERMISSION 'vals access dashboard' 
 * 
 * All users have this and doesnt need to go through the access check
 * @return multitype:string multitype:string  NULL
 */
function getDashboardLink() {
    return array(
        'title' => t('Dashboard'),
        'type' => MENU_NORMAL_ITEM,
        'page callback' => 'vals_soc_dashboard_overview_page',
        'access arguments' => array('vals access dashboard'),
        'description' => t('Dashboard'),
        'file' => 'vals_soc.pages.inc',
        'file path' => _VALS_SOC_PATH . '/includes/module',
    );
}

/* * *******************************************************************************************
 * 	 PLACE FUNCTIONS BELOW THAT ARE TIMELINE DEPENDANT AND MUST GO THROUGH THE ACCESS CHECK
 * *******************************************************************************************
 */

/**
 * TODO - DEPRICATE TIHS FUNCTION
 * HOWS A DIFFERENT SET OF TABS DEPENDING ON ROLE TYPE
 * - ORG ADMIN CAN ENTER/EDIT/DELETE ORGANSATIONS
 * - INST ADMIN CAN ENTER/EDIT/DELETE INSTITUTES
 * - SUPERVISORS CAN ENTER/EDIT/DELETE STUDENTGROUPS
 * USES ROLE_PERMISSION 'vals admin register'
 * 
 * IS PASSED 'dashboard/members'
 * @return multitype:string multitype:string  NULL
 */
function getMembersLink($path) { //dashboard/members
    return array(
        'title' => t('Administer'),
        'type' => MENU_CALLBACK,
        'page callback' => 'vals_soc_administer_page',
        'access callback' => 'vals_soc_access_check',
        'access arguments' => array($path),
        'description' => t('Administer page'),
        'file' => 'vals_soc.pages.inc',
        'file path' => _VALS_SOC_PATH . '/includes/module',
    );
}

function getProposalsOverviewLink($path = '') {
    return array(
        'title' => t('Overview Proposals'),
        'type' => MENU_CALLBACK,
        'page callback' => 'vals_soc_overview_proposals_page',
        'access callback' => 'vals_soc_access_check',
        'access arguments' => array($path),
        'description' => t('Overview Proposals page'),
        'file' => 'vals_soc.pages.inc',
        'file path' => _VALS_SOC_PATH . '/includes/module',
    );
}

function getOverviewLink($path) {
    return array(
        'title' => t('Overview'),
        'type' => MENU_CALLBACK,
        'page callback' => 'vals_soc_overview_page',
        'access callback' => 'vals_soc_access_check',
        'access arguments' => array($path),
        'description' => t('Overview page'),
        'file' => 'vals_soc.pages.inc',
        'file path' => _VALS_SOC_PATH . '/includes/module',
    );
}

function getAdministerLink($path, $type, $plural = true) {//$items['dashboard/institute/administer']
    $path_arr = explode('/', $path);
    $action = array_pop($path_arr);
    $plural_str = $plural ? '(s)' : '';

    return array(
        'title' => ($action == 'view') ? tt('View your %1$s' . $plural_str, t_type($type)) : tt('%1$s your %2$s' . $plural_str, ucfirst(t($action)), t_type($type)),
        'type' => MENU_CALLBACK,
        'page callback' => 'vals_soc_administer_entity_page',
        'page arguments' => array($action),
        'access callback' => 'vals_soc_access_check',
        'access arguments' => array($path),
        'description' => t('Administer page'),
        'file' => 'vals_soc.pages.inc',
        'file path' => _VALS_SOC_PATH . '/includes/module',
    );
}

function getEntityMembersLink($path, $type, $plural = true) { //'dashboard/institute/members'
    $plural_str = $plural ? '(s)' : '';
    return array(
        'title' => tt('Overview of the members of your %1$s', t_type($type)) . $plural_str,
        'type' => MENU_CALLBACK,
        'page callback' => 'vals_soc_members_page',
        'access callback' => 'vals_soc_access_check',
        'access arguments' => array($path),
        'description' => t('Administer page'),
        'file' => 'vals_soc.pages.inc',
        'file path' => _VALS_SOC_PATH . '/includes/module',
    );
}

function getGroupAdministerLink($path) {//$items['dashboard/institute/group/administer']
    return array(
        'title' => t('Administer your groups'),
        'type' => MENU_CALLBACK,
        'page callback' => 'vals_soc_administer_groupspage',
        'access callback' => 'vals_soc_access_check',
        'access arguments' => array($path),
        'description' => t('Administer groups in your institute page'),
        'file' => 'vals_soc.pages.inc',
        'file path' => _VALS_SOC_PATH . '/includes/module',
    );
}

/**
 * ALLOWS ORG ADMINS AND MENTORS TO ADD/EDIT/REMOVE PROJECTS
 * USES ROLE_PERMISSION 'vals admin projects'
 * 
 * IS PASSED 'dashboard/projects/administer'
 * @return multitype:string multitype:string  NULL
 */
function getProjectsAdministerLink($path) { //dashboard/projects/administer
    return array(
        'title' => t('Administer projects'),
        'type' => MENU_CALLBACK,
        'page callback' => 'vals_soc_administer_projects_page',
        'access callback' => 'vals_soc_access_check',
        'access arguments' => array($path),
        'description' => t('Administer page for projects of your organisations'),
        'file' => 'vals_soc.pages.inc',
        'file path' => _VALS_SOC_PATH . '/includes/module',
    );
}

function getMyProjectsAdministerLink($path) { //dashboard/projects/administer/mine
    return array(
        'title' => t('Administer your projects'),
        'type' => MENU_CALLBACK,
        'page callback' => 'vals_soc_administer_my_projects_page',
        'access callback' => 'vals_soc_access_check',
        'access arguments' => array($path),
        'description' => t('Administer page for projects'),
        'file' => 'vals_soc.pages.inc',
        'file path' => _VALS_SOC_PATH . '/includes/module',
    );
}

/**
 * ALLOWS USERS TO VIEW THE PROJECT LIST
 * 
 * Should only be available to ALL once projects are announced
 * otherwise just org/admins/mentors/supervisors during org signup period
 * (LOGIC FOR THIS GOES IN vals_soc.access)
 * 
 * USES ROLE_PERMISSION 'vals browse projects' - all authenticated users have this.
 * 
 * IS PASSED either 'dashboard/projects/browse' or 'projects/browse'
 * @param unknown $path
 * @return multitype:string NULL multitype:unknown
 */
function getProjectsBrowseLink($path) {// dashboard/projects/browse or projects/browse
    if ($path == 'dashboard/projects/browse') {
        $access = MENU_CALLBACK;
    } else {
        $access = MENU_NORMAL_ITEM;
    }
    return array(
        'title' => t('Projects'),
        'type' => $access, // register link, but dont put it in the menu
        'page callback' => 'vals_soc_browse_projects',
        'access callback' => 'vals_soc_access_check',
        'access arguments' => array($path),
        'description' => t('Projects page'),
        'file' => 'vals_soc.pages.inc',
        'file path' => _VALS_SOC_PATH . '/includes/module',
    );
}

function getFavouriteProjectsLink($path) {
    return array(
        'title' => t('Favourite marked projects'),
        'type' => MENU_CALLBACK, // register link, but dont put it in the menu
        'page callback' => 'vals_soc_favourite_projects',
        'access callback' => 'vals_soc_access_check',
        'access arguments' => array($path),
        'description' => t('Favourite projects page'),
        'file' => 'vals_soc.pages.inc',
        'file path' => _VALS_SOC_PATH . '/includes/module',
    );
}

function getMyProjectOffersLink($path) {
    return array(
        'title' => t('My project offers'),
        'type' => MENU_CALLBACK, // register link, but dont put it in the menu
        'page callback' => 'vals_soc_my_project_offers',
        'access callback' => 'vals_soc_access_check',
        'access arguments' => array($path),
        'description' => t('Project offers page'),
        'file' => 'vals_soc.pages.inc',
        'file path' => _VALS_SOC_PATH . '/includes/module',
    );
}

function getMyProjectLink($path) {
    return array(
        'title' => '', // this is set elsewhere.
        'type' => MENU_CALLBACK, // register link, but dont put it in the menu
        'page callback' => 'vals_soc_my_accepted_project',
        'access callback' => 'vals_soc_access_check',
        'access arguments' => array($path),
        'description' => t('My accepted project page'),
        'file' => 'vals_soc.pages.inc',
        'file path' => _VALS_SOC_PATH . '/includes/module',
    );
}

function getMyProjectAgreementLink($path) {//'dashboard/projects/mine/agreement'
    return array(
        'title' => t('My accepted project agreement'),
        'type' => MENU_CALLBACK, // register link, but dont put it in the menu
        'page callback' =>
        (($path == 'dashboard/projects/mine/agreement') ?
                'vals_soc_my_accepted_project_agreement' :
                'vals_soc_my_accepted_project_finalisation'
        ),
        'access callback' => 'vals_soc_access_check',
        'access arguments' => array($path),
        'description' => t('My accepted project agreement page'),
        'file' => 'vals_soc.pages.inc',
        'file path' => _VALS_SOC_PATH . '/includes/module',
    );
}

//

/**
 * ALLOWS USERS TO VIEW THE ORGANISAITONS IN THE SYSTEM
 * 
 * USES ROLE_PERMISSION - 'vals browse organisations'
 * 
 * IS PASSED either 'dashboard/organisations/browse' or 'organisations/browse'
 * @param unknown $path
 * @return multitype:string NULL multitype:unknown
 */
function getOrganisationBrowseLink($path) { // dashboard/organisations/browse or organisations/browse
    if ($path == 'dashboard/organisations/browse') {
        $access = MENU_CALLBACK;
    } else {
        $access = MENU_NORMAL_ITEM;
    }
    return array(
        'title' => t('Organisations'),
        'type' => $access,
        'page callback' => 'vals_soc_browse_organisations',
        'access callback' => 'vals_soc_access_check',
        'access arguments' => array($path),
        'description' => t('Organisations page'),
        'file' => 'vals_soc.pages.inc',
        'file path' => _VALS_SOC_PATH . '/includes/module',
    );
}

/**
 * ALLOWS USERS TO VIEW THE ORGANISAITONS IN THE SYSTEM
 *
 * USES ROLE_PERMISSION - 'vals browse organisations'
 *
 * IS PASSED either 'dashboard/organisations/browse' or 'organisations/browse'
 * @param unknown $path
 * @return multitype:string NULL multitype:unknown
 */
function getInstituteBrowseLink($path) { // dashboard/institutes/browse or institutes/browse
    if ($path == 'dashboard/institutes/browse') {
        $access = MENU_CALLBACK;
    } else {
        $access = MENU_NORMAL_ITEM;
    }
    return array(
        'title' => t('Institutes'),
        'type' => $access,
        'page callback' => 'vals_soc_browse_institutes',
        'access callback' => 'vals_soc_access_check',
        'access arguments' => array($path),
        'description' => t('Institutions page'),
        'file' => 'vals_soc.pages.inc',
        'file path' => _VALS_SOC_PATH . '/includes/module',
    );
}

// EDWIN - i'M NOT SURE OF THE FUNCTIONALITY OF THE BOTTOM TWO MENU LINKS BELOW...

/**
 * ALLOW USERS TO BROWSE PROPOSALS
 *
 * USES ROLE_PERMISSION - none at present
 *
 * is passed 'dashboard/proposals/browse'
 * @return multitype:string multitype:string  NULL
 */
function getProposalsBrowseLink($path) { //dashboard/proposals/browse
    return array(
        'title' => t('Proposals'),
        'type' => MENU_CALLBACK,
        'page callback' => 'vals_soc_browse_proposals',
        'access callback' => 'vals_soc_access_check',
        'access arguments' => array($path),
        'description' => 'Proposal page',
        'file' => 'vals_soc.pages.inc',
        'file path' => _VALS_SOC_PATH . '/includes/module',
    );
}

function getProposalsBrowseByTypeLink($path, $per) { //dashboard/proposals/browsebytype
    return array(
        'title' => tt('Overview of Proposals per %1$s', $per),
        'type' => MENU_CALLBACK,
        'page callback' => 'vals_soc_browse_proposals_by_type',
        'access callback' => 'vals_soc_access_check',
        'access arguments' => array($path),
        'description' => 'Proposal page',
        'file' => 'vals_soc.pages.inc',
        'file path' => _VALS_SOC_PATH . '/includes/module',
    );
}

function getMyProposalsBrowseByTypeLink($path, $per) { //dashboard/proposals/browsebytype/mine
    return array(
        'title' => tt('Overview of Proposals per %1$s', $per),
        'type' => MENU_CALLBACK,
        'page callback' => 'vals_soc_browse_my_proposals_by_type',
        'access callback' => 'vals_soc_access_check',
        'access arguments' => array($path),
        'description' => 'Proposal page',
        'file' => 'vals_soc.pages.inc',
        'file path' => _VALS_SOC_PATH . '/includes/module',
    );
}

/**
 * ALLOWS A USER TO BROWSE HIS/HER OWN PROPOSAL
 *  
 * USES ROLE_PERMISSION - none at present
 * 
 * is passed 'dashboard/proposals/mine'
 * @param string $path
 * @return multitype:string NULL multitype:string
 */
function getMyProposalBrowseLink($path) { //dashboard/proposals/mine
    return array(
        'title' => t('My Proposals'),
        'type' => MENU_CALLBACK,
        'page callback' => 'vals_soc_browse_my_proposals',
        'access callback' => 'vals_soc_access_check',
        'access arguments' => array($path),
        'description' => t('My Proposal page'),
        'file' => 'vals_soc.pages.inc',
        'file path' => _VALS_SOC_PATH . '/includes/module',
    );
}
