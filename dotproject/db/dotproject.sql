#
# dotproject.sql Database Schema
#   updated by JRP (08 July 2002)
#   updated by JCP (29 November 2002)
#
# Use this schema for creating your database for
# a new installation of dotProject.
#

#
# TODO
#
# * replace "task_owner" with "task_creator"
#

CREATE TABLE `companies` (
  `company_id` INT(10) NOT NULL auto_increment,
  `company_module` INT(10) NOT NULL default 0,
  `company_name` varchar(100) default '',
  `company_phone1` varchar(30) default '',
  `company_phone2` varchar(30) default '',
  `company_fax` varchar(30) default '',
  `company_address1` varchar(50) default '',
  `company_address2` varchar(50) default '',
  `company_city` varchar(30) default '',
  `company_state` varchar(30) default '',
  `company_zip` varchar(11) default '',
  `company_primary_url` varchar(255) default '',
  `company_owner` int(11) NOT NULL default '0',
  `company_description` text NOT NULL default '',
  `company_type` int(3) NOT NULL DEFAULT '0',
  `company_email` varchar(255),
  `company_custom` LONGTEXT,
  PRIMARY KEY (`company_id`)
) TYPE=MyISAM;

#
# New to version 1.0
#
CREATE TABLE `departments` (
  `dept_id` int(10) unsigned NOT NULL auto_increment,
  `dept_parent` int(10) unsigned NOT NULL default '0',
  `dept_company` int(10) unsigned NOT NULL default '0',
  `dept_name` tinytext NOT NULL,
  `dept_phone` varchar(30) default NULL,
  `dept_fax` varchar(30) default NULL,
  `dept_address1` varchar(30) default NULL,
  `dept_address2` varchar(30) default NULL,
  `dept_city` varchar(30) default NULL,
  `dept_state` varchar(30) default NULL,
  `dept_zip` varchar(11) default NULL,
  `dept_url` varchar(25) default NULL,
  `dept_desc` text,
  `dept_owner` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`dept_id`)
) TYPE=MyISAM COMMENT='Department heirarchy under a company';

CREATE TABLE `contacts` (
  `contact_id` int(11) NOT NULL auto_increment,
  `contact_first_name` varchar(30) default NULL,
  `contact_last_name` varchar(30) default NULL,
  `contact_order_by` varchar(30) NOT NULL default '',
  `contact_title` varchar(50) default NULL,
  `contact_birthday` date default NULL,
  `contact_company` varchar(100) NOT NULL default '',
  `contact_department` TINYTEXT,
  `contact_type` varchar(20) default NULL,
  `contact_email` varchar(255) default NULL,
  `contact_email2` varchar(255) default NULL,
  `contact_phone` varchar(30) default NULL,
  `contact_phone2` varchar(30) default NULL,
  `contact_fax` varchar(30) default NULL,
  `contact_mobile` varchar(30) default NULL,
  `contact_address1` varchar(60) default NULL,
  `contact_address2` varchar(60) default NULL,
  `contact_city` varchar(30) default NULL,
  `contact_state` varchar(30) default NULL,
  `contact_zip` varchar(11) default NULL,
  `contact_country` varchar(30) default NULL,
  `contact_icq` varchar(20) default NULL,
  `contact_aol` varchar(30) default NULL,
  `contact_notes` text,
  `contact_project` int(11) NOT NULL default '0',
  `contact_icon` varchar(20) default 'obj/contact',
  `contact_owner` int unsigned default '0',
  `contact_private` tinyint unsigned default '0',
  PRIMARY KEY  (`contact_id`),
  KEY `idx_oby` (`contact_order_by`),
  KEY `idx_co` (`contact_company`),
  KEY `idx_prp` (`contact_project`)
) TYPE=MyISAM;

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL auto_increment,
  `event_title` varchar(255) NOT NULL default '',
  `event_start_date` datetime default null,
  `event_end_date` datetime default null,
  `event_parent` int(11) unsigned NOT NULL default '0',
  `event_description` text,
  `event_times_recuring` int(11) unsigned NOT NULL default '0',
  `event_recurs` int(11) unsigned NOT NULL default '0',
  `event_remind` int(10) unsigned NOT NULL default '0',
  `event_icon` varchar(20) default 'obj/event',
  `event_owner` int(11) default '0',
  `event_project` int(11) default '0',
  `event_private` tinyint(3) default '0',
  `event_type` tinyint(3) default '0',
  `event_cwd` tinyint(3) default '0',
  PRIMARY KEY  (`event_id`),
  KEY `id_esd` (`event_start_date`),
  KEY `id_eed` (`event_end_date`),
  KEY `id_evp` (`event_parent`)
) TYPE=MyISAM;

CREATE TABLE `files` (
  `file_id` int(11) NOT NULL auto_increment,
  `file_real_filename` varchar(255) NOT NULL default '',
  `file_project` int(11) NOT NULL default '0',
  `file_task` int(11) NOT NULL default '0',
  `file_name` varchar(255) NOT NULL default '',
  `file_parent` int(11) default '0',
  `file_description` text,
  `file_type` varchar(100) default NULL,
  `file_owner` int(11) default '0',
  `file_date` datetime default NULL,
  `file_size` int(11) default '0',
  `file_version` float NOT NULL default '0',
  `file_icon` varchar(20) default 'obj/',
  `file_category` int(11) default '0',
  PRIMARY KEY  (`file_id`),
  KEY `idx_file_task` (`file_task`),
  KEY `idx_file_project` (`file_project`),
  KEY `idx_file_parent` (`file_parent`)
) TYPE=MyISAM;

CREATE TABLE `files_index` (
  `file_id` int(11) NOT NULL default '0',
  `word` varchar(50) NOT NULL default '',
  `word_placement` int(11) default '0',
  PRIMARY KEY  (`file_id`,`word`),
  KEY `idx_fwrd` (`word`),
  KEY `idx_wcnt` (`word_placement`)
) TYPE=MyISAM;

CREATE TABLE `forum_messages` (
  `message_id` int(11) NOT NULL auto_increment,
  `message_forum` int(11) NOT NULL default '0',
  `message_parent` int(11) NOT NULL default '0',
  `message_author` int(11) NOT NULL default '0',
  `message_editor` int(11) NOT NULL default '0',
  `message_title` varchar(255) NOT NULL default '',
  `message_date` datetime default '0000-00-00 00:00:00',
  `message_body` text,
  `message_published` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`message_id`),
  KEY `idx_mparent` (`message_parent`),
  KEY `idx_mdate` (`message_date`),
  KEY `idx_mforum` (`message_forum`)
) TYPE=MyISAM;

#
# new field forum_last_id in Version 1.0
#
CREATE TABLE `forums` (
  `forum_id` int(11) NOT NULL auto_increment,
  `forum_project` int(11) NOT NULL default '0',
  `forum_status` tinyint(4) NOT NULL default '-1',
  `forum_owner` int(11) NOT NULL default '0',
  `forum_name` varchar(50) NOT NULL default '',
  `forum_create_date` datetime default '0000-00-00 00:00:00',
  `forum_last_date` datetime default '0000-00-00 00:00:00',
  `forum_last_id` INT UNSIGNED DEFAULT '0' NOT NULL,
  `forum_message_count` int(11) NOT NULL default '0',
  `forum_description` varchar(255) default NULL,
  `forum_moderated` int(11) NOT NULL default '0',
  PRIMARY KEY  (`forum_id`),
  KEY `idx_fproject` (`forum_project`),
  KEY `idx_fowner` (`forum_owner`),
  KEY `forum_status` (`forum_status`)
) TYPE=MyISAM;

#
# New to Version 1.0
#
CREATE TABLE `forum_watch` (
  `watch_user` int(10) unsigned NOT NULL default '0',
  `watch_forum` int(10) unsigned default NULL,
  `watch_topic` int(10) unsigned default NULL
) TYPE=MyISAM COMMENT='Links users to the forums/messages they are watching';


CREATE TABLE `permissions` (
  `permission_id` int(11) NOT NULL auto_increment,
  `permission_user` int(11) NOT NULL default '0',
  `permission_grant_on` varchar(12) NOT NULL default '',
  `permission_item` int(11) NOT NULL default '0',
  `permission_value` int(11) NOT NULL default '0',
  PRIMARY KEY  (`permission_id`),
  UNIQUE KEY `idx_pgrant_on` (`permission_grant_on`,`permission_item`,`permission_user`),
  KEY `idx_puser` (`permission_user`),
  KEY `idx_pvalue` (`permission_value`)
) TYPE=MyISAM;

CREATE TABLE `projects` (
  `project_id` int(11) NOT NULL auto_increment,
  `project_company` int(11) NOT NULL default '0',
  `project_department` int(11) NOT NULL default '0',
  `project_name` varchar(255) default NULL,
  `project_short_name` varchar(10) default NULL,
  `project_owner` int(11) default '0',
  `project_url` varchar(255) default NULL,
  `project_demo_url` varchar(255) default NULL,
  `project_start_date` datetime default NULL,
  `project_end_date` datetime default NULL,
  `project_status` int(11) default '0',
  `project_percent_complete` tinyint(4) default '0',
  `project_color_identifier` varchar(6) default 'eeeeee',
  `project_description` text,
  `project_target_budget` int(11) default '0',
  `project_actual_budget` int(11) default '0',
  `project_creator` int(11) default '0',
  `project_active` tinyint(4) default '1',
  `project_private` tinyint(3) unsigned default '0',
  `project_departments` CHAR( 100 ) ,
  `project_contacts` CHAR( 100 ) ,
  `project_priority` tinyint(4) default '0',
  `project_type` SMALLINT DEFAULT '0' NOT NULL,
  PRIMARY KEY  (`project_id`),
  KEY `idx_project_owner` (`project_owner`),
  KEY `idx_sdate` (`project_start_date`),
  KEY `idx_edate` (`project_end_date`),
  KEY `project_short_name` (`project_short_name`)

) TYPE=MyISAM;

CREATE TABLE `project_contacts` (
  `project_id` INT(10) NOT NULL,
  `contact_id` INT(10) NOT NULL
) TYPE=MyISAM;

CREATE TABLE `project_departments` (
  `project_id` INT(10) NOT NULL,
  `department_id` INT(10) NOT NULL
) TYPE=MyISAM;

CREATE TABLE `task_log` (
  `task_log_id` INT(11) NOT NULL auto_increment,
  `task_log_task` INT(11) NOT NULL default '0',
  `task_log_name` VARCHAR(255) default NULL,
  `task_log_description` TEXT,
  `task_log_creator` INT(11) NOT NULL default '0',
  `task_log_hours` FLOAT DEFAULT "0" NOT NULL,
  `task_log_date` DATETIME,
  `task_log_costcode` VARCHAR(8) NOT NULL default '',
  `task_log_problem` TINYINT( 1 ) DEFAULT '0',
  `task_log_reference` TINYINT( 4 ) DEFAULT '0'
  `task_log_related_url` VARCHAR( 255 ) DEFAULT NULL,
  PRIMARY KEY  (`task_log_id`),
  KEY `idx_log_task` (`task_log_task`)
) TYPE=MyISAM;

CREATE TABLE `tasks` (
  `task_id` int(11) NOT NULL auto_increment,
  `task_name` varchar(255) default NULL,
  `task_parent` int(11) default '0',
  `task_milestone` tinyint(1) default '0',
  `task_project` int(11) NOT NULL default '0',
  `task_owner` int(11) NOT NULL default '0',
  `task_start_date` datetime default NULL,
  `task_duration` float unsigned default '0',
  `task_duration_type` int(11) NOT NULL DEFAULT 1,
  `task_hours_worked` float unsigned default '0',
  `task_end_date` datetime default NULL,
  `task_status` int(11) default '0',
  `task_priority` tinyint(4) default '0',
  `task_percent_complete` tinyint(4) default '0',
  `task_description` text,
  `task_target_budget` int(11) default '0',
  `task_related_url` varchar(255) default NULL,
  `task_creator` int(11) NOT NULL default '0',
  `task_order` int(11) NOT NULL default '0',
  `task_client_publish` tinyint(1) NOT NULL default '0',
  `task_dynamic` tinyint(1) NOT NULL default 0,
  `task_access` int(11) NOT NULL default '0',
  `task_notify` int(11) NOT NULL default '0',
  `task_departments` CHAR( 100 ),
  `task_contacts` CHAR( 100 ),
  `task_custom` LONGTEXT,
  `task_type` SMALLINT DEFAULT '0' NOT NULL,
  PRIMARY KEY  (`task_id`),
  KEY `idx_task_parent` (`task_parent`),
  KEY `idx_task_project` (`task_project`),
  KEY `idx_task_owner` (`task_owner`),
  KEY `idx_task_order` (`task_order`)
) TYPE=MyISAM;

CREATE TABLE `task_contacts` (
  `task_id` INT(10) NOT NULL,
  `contact_id` INT(10) NOT NULL
) TYPE=MyISAM;

CREATE TABLE `task_departments` (
  `task_id` INT(10) NOT NULL,
  `department_id` INT(10) NOT NULL
) TYPE=MyISAM;

CREATE TABLE `tickets` (
  `ticket` int(10) unsigned NOT NULL auto_increment,
  `author` varchar(100) NOT NULL default '',
  `recipient` varchar(100) NOT NULL default '',
  `subject` varchar(100) NOT NULL default '',
  `attachment` tinyint(1) unsigned NOT NULL default '0',
  `timestamp` int(10) unsigned NOT NULL default '0',
  `type` varchar(15) NOT NULL default '',
  `assignment` int(10) unsigned NOT NULL default '0',
  `parent` int(10) unsigned NOT NULL default '0',
  `activity` int(10) unsigned NOT NULL default '0',
  `priority` tinyint(1) unsigned NOT NULL default '1',
  `cc` varchar(255) NOT NULL default '',
  `body` text NOT NULL,
  `signature` text,
  PRIMARY KEY  (`ticket`),
  KEY `parent` (`parent`),
  KEY `type` (`type`)
) TYPE=MyISAM;

CREATE TABLE `user_tasks` (
  `user_id` int(11) NOT NULL default '0',
  `user_type` tinyint(4) NOT NULL default '0',
  `task_id` int(11) NOT NULL default '0',
  `perc_assignment` int(11) NOT NULL default '100',
  `user_task_priority` tinyint(4) default '0',
  PRIMARY KEY  (`user_id`,`task_id`),
  KEY `user_type` (`user_type`)
) TYPE=MyISAM;

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL auto_increment,
  `user_contact` int(11) NOT NULL default '0',
  `user_username` varchar(255) NOT NULL default '',
  `user_password` varchar(32) NOT NULL default '',
  `user_parent` int(11) NOT NULL default '0',
  `user_type` tinyint(3) not null default '0',
  `user_company` int(11) default '0',
  `user_department` int(11) default '0',
/*  `user_first_name` varchar(50) default '',
  `user_last_name` varchar(50) default '',
  `user_email` varchar(255) default '',
  `user_phone` varchar(30) default '',
  `user_home_phone` varchar(30) default '',
  `user_mobile` varchar(30) default '',
  `user_address1` varchar(30) default '',
  `user_address2` varchar(30) default '',
  `user_city` varchar(30) default '',
  `user_state` varchar(30) default '',
  `user_zip` varchar(11) default '',
  `user_country` varchar(30) default '',
  `user_icq` varchar(20) default '',
  `user_aol` varchar(20) default '',
  `user_birthday` datetime default NULL,
  `user_pic` TEXT,*/
  `user_owner` int(11) NOT NULL default '0',
  `user_signature` TEXT,
  PRIMARY KEY  (`user_id`),
  KEY `idx_uid` (`user_username`),
  KEY `idx_pwd` (`user_password`),
  KEY `idx_user_parent` (`user_parent`)
) TYPE=MyISAM;

CREATE TABLE `task_dependencies` (
    `dependencies_task_id` int(11) NOT NULL,
    `dependencies_req_task_id` int(11) NOT NULL,
    PRIMARY KEY (`dependencies_task_id`, `dependencies_req_task_id`)
);

CREATE TABLE `user_preferences` (
  `pref_user` varchar(12) NOT NULL default '',
  `pref_name` varchar(72) NOT NULL default '',
  `pref_value` varchar(32) NOT NULL default '',
  KEY `pref_user` (`pref_user`,`pref_name`)
) TYPE=MyISAM;

#
# ATTENTION:
# Customize this section for your installation.
# Recommended changes include:
#   New admin username -> replace {admin}
#   New admin password -> replace {passwd]
#   New admin email -> replace {admin@localhost}
#

INSERT INTO `users` VALUES (1,1,'admin',MD5('passwd'),0,1,0,0,0,'');
INSERT INTO `contacts` (contact_id, contact_first_name, contact_last_name, contact_email) 
VALUES (1,'Admin','Person','admin@localhost');

INSERT INTO `permissions` VALUES (1,1,"all",-1, -1);

INSERT INTO `user_preferences` VALUES("0", "LOCALE", "en");
INSERT INTO `user_preferences` VALUES("0", "TABVIEW", "0");
INSERT INTO `user_preferences` VALUES("0", "SHDATEFORMAT", "%d/%m/%Y");
INSERT INTO `user_preferences` VALUES("0", "TIMEFORMAT", "%I:%M %p");
INSERT INTO `user_preferences` VALUES("0", "UISTYLE", "default");
INSERT INTO `user_preferences` VALUES("0", "TASKASSIGNMAX", "100");

#
# AJE (24/Jan/2003)
# ---------
# N O T E !
#
# MODULES TABLE IS STILL IN DEVELOPMENT STAGE
#

#
# Table structure for table 'modules'
#
#DROP TABLE modules;
CREATE TABLE `modules` (
  `mod_id` int(11) NOT NULL auto_increment,
  `mod_name` varchar(64) NOT NULL default '',
  `mod_directory` varchar(64) NOT NULL default '',
  `mod_version` varchar(10) NOT NULL default '',
  `mod_setup_class` varchar(64) NOT NULL default '',
  `mod_type` varchar(64) NOT NULL default '',
  `mod_active` int(1) unsigned NOT NULL default '0',
  `mod_ui_name` varchar(20) NOT NULL default '',
  `mod_ui_icon` varchar(64) NOT NULL default '',
  `mod_ui_order` tinyint(3) NOT NULL default '0',
  `mod_ui_active` int(1) unsigned NOT NULL default '0',
  `mod_description` varchar(255) NOT NULL default '',
  `permissions_item_table` CHAR( 100 ),
  `permissions_item_field` CHAR( 100 ),
  `permissions_item_label` CHAR( 100 ),
  PRIMARY KEY  (`mod_id`,`mod_directory`)
) TYPE=MyISAM;

#
# Dumping data for table 'modules'
#
INSERT INTO `modules` VALUES("1", "Companies", "companies", "1.0.0", "", "core", "1", "Companies", "handshake.png", "1", "1", "", "companies", "company_id", "company_name");
INSERT INTO `modules` VALUES("2", "Projects", "projects", "1.0.0", "", "core", "1", "Projects", "applet3-48.png", "2", "1", "", "projects", "project_id", "project_name");
INSERT INTO `modules` VALUES("3", "Tasks", "tasks", "1.0.0", "", "core", "1", "Tasks", "applet-48.png", "3", "1", "", "tasks", "task_id", "task_name");
INSERT INTO `modules` VALUES("4", "Calendar", "calendar", "1.0.0", "", "core", "1", "Calendar", "myevo-appointments.png", "4", "1", "", "", "", "");
INSERT INTO `modules` VALUES("5", "Files", "files", "1.0.0", "", "core", "1", "Files", "folder5.png", "5", "1", "", "files", "file_id", "file_name");
INSERT INTO `modules` VALUES("6", "Contacts", "contacts", "1.0.0", "", "core", "1", "Contacts", "monkeychat-48.png", "6", "1", "", "", "", "");
INSERT INTO `modules` VALUES("7", "Forums", "forums", "1.0.0", "", "core", "1", "Forums", "support.png", "7", "1", "", "forums", "forum_id", "forum_name");
INSERT INTO `modules` VALUES("8", "Tickets", "ticketsmith", "1.0.0", "", "core", "1", "Tickets", "ticketsmith.gif", "8", "1", "", "", "", "");
INSERT INTO `modules` VALUES("9", "User Administration", "admin", "1.0.0", "", "core", "1", "User Admin", "helix-setup-users.png", "9", "1", "", "users", "user_id", "user_username");
INSERT INTO `modules` VALUES("10", "System Administration", "system", "1.0.0", "", "core", "1", "System Admin", "48_my_computer.png", "10", "1", "", "", "", "");
INSERT INTO `modules` VALUES("11", "Departments", "departments", "1.0.0", "", "core", "1", "Departments", "users.gif", "11", "0", "", "", "", "");
INSERT INTO `modules` VALUES("12", "Help", "help", "1.0.0", "", "core", "1", "Help", "dp.gif", "12", "0", "", "", "", "");
INSERT INTO `modules` VALUES("13", "Public", "public", "1.0.0", "", "core", "1", "Public", "users.gif", "13", "0", "", "", "", "");

#
# Table structure for table 'syskeys'
#

DROP TABLE IF EXISTS `syskeys`;
CREATE TABLE `syskeys` (
  `syskey_id` int(10) unsigned NOT NULL auto_increment,
  `syskey_name` varchar(48) NOT NULL default '',
  `syskey_label` varchar(255) NOT NULL default '',
  `syskey_type` int(1) unsigned NOT NULL default '0',
  `syskey_sep1` char(2) default '\n',
  `syskey_sep2` char(2) NOT NULL default '|',
  PRIMARY KEY  (`syskey_id`),
  UNIQUE KEY `idx_syskey_name` (`syskey_id`)
) TYPE=MyISAM;

#
# Table structure for table 'sysvals'
#

DROP TABLE IF EXISTS `sysvals`;
CREATE TABLE `sysvals` (
  `sysval_id` int(10) unsigned NOT NULL auto_increment,
  `sysval_key_id` int(10) unsigned NOT NULL default '0',
  `sysval_title` varchar(48) NOT NULL default '',
  `sysval_value` text NOT NULL,
  PRIMARY KEY  (`sysval_id`)
) TYPE=MyISAM;

#
# Table structure for table 'sysvals'
#

INSERT INTO `syskeys` VALUES("1", "SelectList", "Enter values for list", "0", "\n", "|");
INSERT INTO `syskeys` VALUES (2, 'CustomField', 'Serialized array in the following format:\r\n<KEY>|<SERIALIZED ARRAY>\r\n\r\nSerialized Array:\r\n[type] => text | checkbox | select | textarea | label\r\n[name] => <Field\'s name>\r\n[options] => <html capture options>\r\n[selects] => <options for select and checkbox>', 0, '\n', '|');
INSERT INTO `syskeys` VALUES("3", "ColorSelection", "Hex color values for type=>color association.", "0", "\n", "|");

INSERT INTO `sysvals` (`sysval_key_id`,`sysval_title`,`sysval_value`) VALUES("1", "ProjectStatus", "0|Not Defined\r\n1|Proposed\r\n2|In Planning\r\n3|In Progress\r\n4|On Hold\r\n5|Complete\r\n6|Template");
INSERT INTO `sysvals` (`sysval_key_id`,`sysval_title`,`sysval_value`) VALUES("1", "CompanyType", "0|Not Applicable\n1|Client\n2|Vendor\n3|Supplier\n4|Consultant\n5|Government\n6|Internal");
INSERT INTO `sysvals` (`sysval_key_id`,`sysval_title`,`sysval_value`) VALUES("1", "TaskDurationType", "1|hours\n24|days");
INSERT INTO `sysvals` (`sysval_key_id`,`sysval_title`,`sysval_value`) VALUES("1", "EventType", "0|General\n1|Appointment\n2|Meeting\n3|All Day Event\n4|Anniversary\n5|Reminder");
INSERT INTO `sysvals` VALUES (null, 1, 'TaskStatus', '0|Active\n-1|Inactive');
INSERT INTO `sysvals` VALUES (null, 1, 'TaskType', '0|Unknown\n1|Administrative\n2|Operative');
INSERT INTO `sysvals` VALUES (null, 1, 'ProjectType', '0|Unknown\n1|Administrative\n2|Operative');
INSERT INTO `sysvals` (`sysval_key_id`,`sysval_title`,`sysval_value`) VALUES("3", "ProjectColors", "Web|FFE0AE\nEngineering|AEFFB2\nHelpDesk|FFFCAE\nSystem Administration|FFAEAE");
INSERT INTO `sysvals` VALUES (null, 1, 'FileType', '0|Unknown\n1|Document\n2|Application');
INSERT INTO `sysvals` ( `sysval_id` , `sysval_key_id` , `sysval_title` , `sysval_value` ) VALUES (null, '1', 'TaskPriority', '-1|low\n0|normal\n1|high');
INSERT INTO `sysvals` ( `sysval_id` , `sysval_key_id` , `sysval_title` , `sysval_value` ) VALUES (null, '1', 'ProjectPriority', '-1|low\n0|normal\n1|high');
INSERT INTO `sysvals` ( `sysval_id` , `sysval_key_id` , `sysval_title` , `sysval_value` ) VALUES (null, '1', 'ProjectPriorityColor', '-1|#E5F7FF\n0|\n1|#FFDCB3');
INSERT INTO `sysvals` ( `sysval_id` , `sysval_key_id` , `sysval_title` , `sysval_value` ) VALUES (null, '1', 'TaskLogReference', '0|Not Defined\n1|Email\n2|Helpdesk\n3|Phone Call\n4|Fax');
INSERT INTO `sysvals` ( `sysval_id` , `sysval_key_id` , `sysval_title` , `sysval_value` ) VALUES (null, '1', 'TaskLogReferenceImage', '0| 1|./images/obj/email.gif 2|./modules/helpdesk/images/helpdesk.png 3|./images/obj/phone.gif 4|./images/icons/stock_print-16.png');


#
# Table structure for table 'roles'
#

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `role_id` int(10) unsigned NOT NULL auto_increment,
  `role_name` varchar(24) NOT NULL default '',
  `role_description` varchar(255) NOT NULL default '',
  `role_type` int(3) unsigned NOT NULL default '0',
  `role_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`role_id`)
) TYPE=MyISAM;

#
# Table structure for table 'user_roles'
#

DROP TABLE IF EXISTS `user_roles`;
CREATE TABLE `user_roles` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `role_id` int(10) unsigned NOT NULL default '0'
) TYPE=MyISAM;

# Host: localhost
# Database: dotproject
# Table: 'common_notes'
# 
DROP TABLE IF EXISTS `common_notes`;
CREATE TABLE `common_notes` (
  `note_id` int(10) unsigned NOT NULL auto_increment,
  `note_author` int(10) unsigned NOT NULL default '0',
  `note_module` int(10) unsigned NOT NULL default '0',
  `note_record_id` int(10) unsigned NOT NULL default '0',
  `note_category` int(3) unsigned NOT NULL default '0',
  `note_title` varchar(100) NOT NULL default '',
  `note_body` text NOT NULL,
  `note_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `note_hours` float NOT NULL default '0',
  `note_code` varchar(8) NOT NULL default '',
  `note_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `note_modified` timestamp(14) NOT NULL,
  `note_modified_by` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`note_id`)
) TYPE=MyISAM; 



#20040823
#Added user access log
CREATE TABLE `user_access_log` (
`user_access_log_id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
`user_id` INT( 10 ) UNSIGNED NOT NULL ,
`date_time_in` DATETIME DEFAULT '0000-00-00 00:00:00',
`date_time_out` DATETIME DEFAULT '0000-00-00 00:00:00',
`date_time_last_action` DATETIME DEFAULT '0000-00-00 00:00:00',
PRIMARY KEY ( `user_access_log_id` )
) TYPE = MyISAM;
