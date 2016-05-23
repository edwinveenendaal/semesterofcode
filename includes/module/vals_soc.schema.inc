<?php
/*
 * Utility to change the max length of a text field
*/
function vals_soc_change_text_field_max_length($field_name, $new_length) {
	$field_table = 'field_data_' . $field_name;
	$field_revision_table = 'field_revision_' . $field_name;
	$field_column = $field_name . '_value';

	// Alter value field length in fields table
	db_query("ALTER TABLE `{$field_table}` CHANGE `{$field_column}` `{$field_column}` VARCHAR( {$new_length} )");
	// Alter value field length in fields revision table
	db_query("ALTER TABLE `{$field_revision_table}` CHANGE `{$field_column}` `{$field_column}` VARCHAR( {$new_length} )");

	// Update field config with new max length
	$result = db_query("SELECT CAST(`data` AS CHAR(10000) CHARACTER SET utf8) FROM `field_config` WHERE field_name = '{$field_name}'");
	$config = $result->fetchField();
	$config_array = unserialize($config);
	$config_array['settings']['max_length'] = $new_length;
	$config = serialize($config_array);
	db_update('field_config')
	->fields(array('data' => $config))
	->condition('field_name', $field_name)
	->execute();
}

function mymodule_update_7002() {
    //When you have fields to enlarge, replace field_name for the name of the field
    //you want to change
	//vals_soc_change_text_field_max_length('field_name', 50);
    //vals_soc_change_text_field_max_length('field_name', 50);//....
}

/* For the moment being we work with the sql scripts in the code. So when the module is installed it will call this function
 * to know about the db schema's to use for the db

function vals_soc_schema_handler() {
	// NOTE: {variable} needs to be created before all other tables, as
	// some database drivers, e.g. Oracle and DB2, will require variable_get()
	// and variable_set() for overcoming some database specific limitations.
	$schema['soc_proposals'] = array(
			'description' => 'The table of the proposals',
			'fields' => array(
					'pid' => array(
							'description' => 'Proposal id.',
							'type' => 'serial',
							'length' => 'medium',
							'not null' => TRUE,
					),
					'owner_id' => array(
							'description' => 'The student id, owner of the proposal.',
							'type' => 'int',
							'length' => 'medium',
							'not null' => TRUE,
					),
					'org_id' => array(
							'description' => 'The organisation.',
							'type' => 'int',
							'length' => 'medium',
							'not null' => TRUE,
					),
					'inst_id' => array(
							'description' => 'The institute, for convenience',
							'type' => 'int',
							'length' => 'medium',
							'not null' => TRUE,
					),
					'supervisor_id' => array(
							'description' => 'The id of the supervisor',
							'type' => 'int',
							'length' => 'medium',
							'not null' => TRUE,
					),
					'pid' => array(
							'description' => 'The project id',
							'type' => 'int',
							'length' => 'medium',
							'not null' => TRUE,
					),
					'solution_short' => array(
							'description' => 'The short description of the proposal.',
							'type' => 'varchar',
							'length' => 255, // TODO perhaps change this to another data type
							'not null' => FALSE,
							'default' => '',
					),
					'solution_long' => array(
							'description' => 'A longer description of the proposal',
							'type' => 'text',
							//'size' => '1024',
							'not null' => TRUE,
					),
					'state' => array(
							'description' => 'The state of the proposal',
							'type' => 'varchar',
							'length' => 128,
							'not null' => FALSE,
					),
			),
			'primary key' => array('pid'),
			/*
			 * ALTER TABLE `soc_proposals` ADD `modules` VARCHAR( 1024 ) NULL AFTER `solution_long` 
			 * ALTER TABLE `soc_proposals` CHANGE `studid` `owner_id` MEDIUMINT( 8 ) UNSIGNED NOT NULL 
	*
	* /
	);

	$schema['soc_projects'] = array(
			'description' => 'The table of the projects',
			'fields' => array(
					'pid' => array(
							'description' => 'Project id.',
							'type' => 'varchar',
							'length' => 255,
							'not null' => TRUE,
							'default' => '',
					),
					'title' => array(
							'description' => 'The title of the project.',
							'type' => 'varchar',
							'length' => 255,
							'not null' => TRUE,
							'default' => '',
					),
					'description' => array(
							'description' => 'The description of the project.',
							'type' => 'varchar',
							'length' => 255, // TODO perhaps change this to another data type
							'not null' => FALSE,
							'default' => '',
					),
					'selected' => array(
							'description' => 'Whether the project is chosen by a student',
							'type' => 'int',
							'size' => 'tiny',
							'not null' => TRUE,
							'default' => 0,
					),
                'state' => array(
							'description' => 'The state of the project',
							'type' => 'varchar',
							'length' => 128,
							'not null' => FALSE,
					),
			),
			'primary key' => array('pid'),
        /*
         * ALTER TABLE `soc_projects` ADD `organisation` MEDIUMINT NOT NULL AFTER `state` ,
ADD `mentor_id` MEDIUMINT NOT NULL AFTER `organisation` ,
ADD `student` MEDIUMINT NOT NULL AFTER `mentor` ,
ADD `supervisor` MEDIUMINT NOT NULL AFTER `student`
         * ALTER TABLE `soc_projects` ADD `url` VARCHAR( 1024 ) NOT NULL AFTER `description` 
         * 
         * /
	);
	$schema['soc_codes'] = array(
			'description' => 'Create some random codes so that not just anybody can register.',
			'fields' => array(
                    'code_id' => array(
							'description' => 'Code id.',
							'type' => 'serial',
							'length' => 'medium',
							'not null' => TRUE,
							//'default' => '',
					),
					'type' => array(
							'description' => 'The type of user.',
							'type' => 'varchar',
							'length' => 128,
							'not null' => TRUE,
							'default' => '',
					),
					'code' => array(
							'description' => 'The code to enter at registration.',
							'type' => 'varchar',
							'length' => 10,
							'not null' => TRUE,
							'default' => '',
					),
                    'entity_id' => array(
							'description' => 'The organisation/institute etc.',
							'type' => 'int',
							'length' => 'medium',
							'not null' => TRUE,
							'default' => 0,
					),
                    'studentgroup_id' => array(
							'description' => 'To make it easier to retrieve the group of the code a student uses to register',
							'type' => 'int',
							'length' => 'medium',
							'not null' => FALSE,
					),
			),
			'primary key' => array('code_id'),
	);
    
    $schema['soc_institutes'] = array(
			'description' => 'The institutes gettting involved in the Semester of Code',
			'fields' => array(
					'inst_id' => array(
							'description' => 'Institute id.',
							'type' => 'serial',
							'length' => 'small',
							'not null' => TRUE,
							//'default' => '',
					),
                    'name' => array(
							'description' => 'The name of the institute.',
							'type' => 'varchar',
							'length' => 255,
							'not null' => TRUE,
							'default' => '',
					),
					'contact_name' => array(
							'description' => 'The name of the contact person.',
							'type' => 'varchar',
							'length' => 128,
							'not null' => TRUE,
							'default' => '',
					),
                    'contact_email' => array(
							'description' => 'The email of the contact person.',
							'type' => 'varchar',
							'length' => 128,
							'not null' => TRUE,
							'default' => '',
					),
			),
			'primary key' => array('inst_id'),
	);
    $schema['soc_organisations'] = array(
			'description' => 'The organisations gettting involved in the Semester of Code',
			'fields' => array(
					'org_id' => array(
							'description' => 'Institute id.',
							'type' => 'serial',
							'length' => 'small',
							'not null' => TRUE,
							//'default' => '',
					),
                    'name' => array(
							'description' => 'The name of the organisation.',
							'type' => 'varchar',
							'length' => 255,
							'not null' => TRUE,
							'default' => '',
					),
					'contact_name' => array(
							'description' => 'The name of the contact person.',
							'type' => 'varchar',
							'length' => 128,
							'not null' => TRUE,
							'default' => '',
					),
                    'contact_email' => array(
							'description' => 'The email of the contact person.',
							'type' => 'varchar',
							'length' => 128,
							'not null' => TRUE,
							'default' => '',
					),
                    'url' => array(
							'description' => 'The website of the organisation',
							'type' => 'varchar',
							'length' => 256,
							'not null' => FALSE,
							'default' => '',
					),
					'description' => array(
							'description' => 'Description of the organisation',
							'type' => 'text',
							//'length' => 1024,
							'not null' => FALSE,
							//'default' => '',
					)
			),
			'primary key' => array('org_id'),
	);
    $schema['soc_studentgroups'] = array(
			'description' => 'The students will be divided in groups, each teacher probably having one or more groups',
			'fields' => array(
					'studentgroup_id' => array(
							'description' => 'Group id.',
							'type' => 'serial',
							'length' => 'small',
							'not null' => TRUE,
					),
                    'owner_id' => array(
							'description' => 'The id of the teacher',
							'type' => 'int',
							'length' => 'medium',
							'not null' => TRUE,
					),
                    'inst_id' => array(
							'description' => 'Institute id.',
							'type' => 'int',
							'length' => 'small',
							'not null' => TRUE,
					),
                    'name' => array(
							'description' => 'The name of the group to remind.',
							'type' => 'varchar',
							'length' => 255,
							'not null' => TRUE,
					),
					'description' => array(
							'description' => 'Some description or comment',
							'type' => 'varchar',
							'length' => 512,
							'not null' => FALSE,
							'default' => '',
					),
			),
			'primary key' => array('studentgroup_id'),
	);
    $schema['soc_user_membership'] = array(
			'description' => 'All users are member of some organisation, either a university or some software company etc.',
			'fields' => array(
					'mem_id' => array(
							'description' => 'org relation id.',
							'type' => 'serial',
							'length' => 'small',
							'not null' => TRUE,
					),
                    'uid' => array(
							'description' => 'The id of the user.',
							'type' => 'int',
							'length' => 'medium',
							'not null' => TRUE,
							
					),
					'type' => array(
							'description' => 'The type of the organisation.',
							'type' => 'varchar',
							'length' => 128,
							'not null' => TRUE,
					),
                    'group_id' => array(
							'description' => 'The id of the organisation/institute/group etc.',
							'type' => 'int',
							'length' => 'medium',
							'not null' => TRUE,
					),
			),
			'primary key' => array('mem_id'),
	);

	return $schema;
}
*/