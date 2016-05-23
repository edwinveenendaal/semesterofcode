<?php
@define('_DEBUG', FALSE);// Will be preemtied by the definition (set to TRUE) in the development settings, if loaded
define('_VALS_SOC_PATH', drupal_get_path('module', 'vals_soc'));
define('_VALS_SOC_ROOT', DRUPAL_ROOT.'/'._VALS_SOC_PATH);
variable_set('configurable_timezones', 0);
variable_set('user_pictures', 0); //omit the user avatar picture
include_once(DRUPAL_ROOT.'/initial.php');
define('_VALS_SOC_REL_URL', _WEB_URL.'/'._VALS_SOC_PATH);
$scheme = ((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') ? 'https': 'http');
define('_VALS_SOC_FULL_URL', "$scheme://".$_SERVER['HTTP_HOST']._WEB_URL);
define('_VALS_SOC_FULL_MODULE_URL', 'http://'.$_SERVER['HTTP_HOST']._VALS_SOC_REL_URL);
define('_VALS_TEST_UI_ONLY', TRUE && _DEBUG);
define('_FULL_NAME_LENGTH', 50);
define('_VALS_SOC_MENTOR_ACCESS_ALL', FALSE);
@define('_VALS_ADMIN_EMAIL_ADDRESS', 'edwin@raycom.com');
@define('_TEST_EMAIL_ADDRESS', 'edwin@raycom.com');
define('_VALS_USE_TIME_SCHEDULE', FALSE);//We allow students to their work whenever
define('_VALS_INITIAL_PROJECT_STATE', 'open'); //Can be 'open', 'pending' (mentors can indicate it is 'draft'
@define('_INSTALL_BY_SCRIPT', FALSE);
//Some convenient constants
//user types
define('_ADMINISTRATOR_TYPE', 'administrator');
define('_ORGADMIN_TYPE', 'organisation_admin');
define('_INSTADMIN_TYPE', 'institute_admin');
define('_SOC_TYPE', 'soc');
define('_STUDENT_TYPE', 'student');
define('_SUPERVISOR_TYPE', 'supervisor');
define('_MENTOR_TYPE', 'mentor');
define('_ANONYMOUS_TYPE', 'anonymous user');
define('_USER_TYPE', 'authenticated user');
//Grouping types
define('_STUDENT_GROUP', 'studentgroup');
define('_ORGANISATION_GROUP', 'organisation');
define('_INSTITUTE_GROUP', 'institute');
//Object types
define('_PROJECT_OBJ', 'project');
define('_PROPOSAL_OBJ', 'proposal');
define('_AGREEMENT_OBJ', 'agreement');