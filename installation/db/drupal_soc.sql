CREATE TABLE IF NOT EXISTS `soc_agreements` (
  `agreement_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` mediumint(8) unsigned NOT NULL,
  `supervisor_id` mediumint(8) NOT NULL,
  `mentor_id` mediumint(8) NOT NULL,
  `proposal_id` mediumint(8) unsigned NOT NULL,
  `project_id` mediumint(8) unsigned NOT NULL,
  `description` text COMMENT 'Description of the agreement',
  `student_signed` tinyint(4) DEFAULT '0' COMMENT 'Whether the project is signed off by a student',
  `supervisor_signed` tinyint(4) DEFAULT '0' COMMENT 'Whether the project is signed off by a student',
  `mentor_signed` tinyint(4) DEFAULT '0' COMMENT 'Whether the project is signed off by a student',
  `student_completed` tinyint(4) DEFAULT '0' COMMENT 'Whether the project is signed as finished by a student',
  `supervisor_completed` tinyint(4) DEFAULT '0' COMMENT 'Whether the project is signed as finished  by a supervisor',
  `mentor_completed` tinyint(4) DEFAULT '0' COMMENT 'Whether the project is signed as finished  by a mentor',
  `evaluation` text COMMENT 'Space for evaluation text',
  PRIMARY KEY (`agreement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `soc_codes` (
  `code_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Code id.',
  `type` varchar(128) NOT NULL DEFAULT '' COMMENT 'The type of user.',
  `code` varchar(10) NOT NULL DEFAULT '' COMMENT 'The code to enter at registration.',
  `entity_id` mediumint(9) NOT NULL DEFAULT '0' COMMENT 'The organisation/institute etc.',
  `studentgroup_id` int(11) DEFAULT NULL COMMENT 'To make it easier to retrieve the group of the code a student uses to register',
  PRIMARY KEY (`code_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Create some random codes so that not just anybody can...';


CREATE TABLE IF NOT EXISTS `soc_comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `entity_id` int(11) unsigned NOT NULL,
  `entity_type` varchar(128) NOT NULL,
  `author` int(11) unsigned NOT NULL,
  `date_posted` datetime NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `soc_institutes` (
  `inst_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Institute id.',
  `owner_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT 'The name of the institute.',
  `contact_name` varchar(128) NOT NULL DEFAULT '' COMMENT 'The name of the contact person.',
  `contact_email` varchar(128) NOT NULL DEFAULT '' COMMENT 'The email of the contact person.',
  PRIMARY KEY (`inst_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='The institutes gettting involved in the Semester of Code';


CREATE TABLE IF NOT EXISTS `soc_names` (
  `names_uid` int(11) NOT NULL,
  `type` varchar(32) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `soc_organisations` (
  `org_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Institute id.',
  `owner_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT 'The name of the organisation.',
  `contact_name` varchar(128) NOT NULL DEFAULT '' COMMENT 'The name of the contact person.',
  `contact_email` varchar(128) NOT NULL DEFAULT '' COMMENT 'The email of the contact person.',
  `url` varchar(256) DEFAULT '' COMMENT 'The website of the organisation',
  `description` text COMMENT 'Description of the organisation',
  PRIMARY KEY (`org_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='The organisations gettting involved in the Semester of Code';


CREATE TABLE IF NOT EXISTS `soc_projects` (
  `pid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Project id.',
  `owner_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT 'The title of the project.',
  `description` text COMMENT 'The description of the project.',
  `url` varchar(1024) DEFAULT NULL,
  `state` varchar(128) DEFAULT NULL COMMENT 'The state of the project',
  `available` tinyint(4) NOT NULL DEFAULT '0',
  `begin` int(11) DEFAULT NULL,
  `end` int(11) DEFAULT NULL,
  `org_id` int(11) unsigned NOT NULL,
  `mentor_id` mediumint(9) NOT NULL DEFAULT '0',
  `proposal_id` mediumint(9) DEFAULT NULL,
  `selected` tinyint(4) DEFAULT '0' COMMENT 'Whether the project is chosen by a student',
  `views` smallint(5) unsigned DEFAULT '0',
  `likes` smallint(5) unsigned DEFAULT '0',
  `tags` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='The table of the projects';


CREATE TABLE IF NOT EXISTS `soc_proposals` (
  `proposal_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` mediumint(8) unsigned NOT NULL,
  `org_id` mediumint(8) unsigned NOT NULL,
  `inst_id` mediumint(8) unsigned NOT NULL,
  `supervisor_id` mediumint(9) NOT NULL,
  `pid` mediumint(8) unsigned NOT NULL,
  `title` varchar(512) DEFAULT NULL,
  `solution_short` text NOT NULL,
  `solution_long` longtext NOT NULL,
  `state` enum('draft','open','published','accepted','rejected','finished','archived','retracted') NOT NULL,
  `reason` varchar(512) DEFAULT NULL COMMENT 'reason for rejection or withdraw',
  PRIMARY KEY (`proposal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `soc_studentgroups` (
  `studentgroup_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Group id.',
  `owner_id` int(11) NOT NULL COMMENT 'The id of the teacher',
  `inst_id` int(11) NOT NULL COMMENT 'Institute id.',
  `name` varchar(255) NOT NULL COMMENT 'The name of the group to remind.',
  `description` text COMMENT 'Some description or comment',
  PRIMARY KEY (`studentgroup_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='The students will be divided in groups, each teacher...';


CREATE TABLE IF NOT EXISTS `soc_student_favourites` (
  `favour_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'favourite auto id.',
  `uid` int(10) unsigned NOT NULL COMMENT 'the student uid.',
  `pid` int(10) unsigned NOT NULL COMMENT 'The project id',
  PRIMARY KEY (`favour_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='The students will mark some projects as their favourites';


CREATE TABLE IF NOT EXISTS `soc_supervisor_rates` (
  `rate_id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `rate` tinyint(4) NOT NULL,
  `uid` int(11) NOT NULL,
  PRIMARY KEY (`rate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `soc_user_membership` (
  `mem_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'org relation id.',
  `uid` int(11) NOT NULL COMMENT 'The id of the user.',
  `type` varchar(128) NOT NULL COMMENT 'The type of the organisation.',
  `group_id` int(11) NOT NULL COMMENT 'The id of the organisation/institute/group etc.',
  PRIMARY KEY (`mem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='All users are member of some organisation, either a...';