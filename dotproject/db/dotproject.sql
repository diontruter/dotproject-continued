#
# dotproject.sql Database Schema
# 	updated by JRP (08 July 2002)
#
# Use this schema for creating your database for 
# a new installation of dotProject.
#

CREATE TABLE companies (
  company_id smallint(6) NOT NULL auto_increment,
  company_username varchar(20) NOT NULL default '',
  company_password varchar(20) NOT NULL default '',
  company_name varchar(100) default NULL,
  company_phone1 varchar(30) default NULL,
  company_phone2 varchar(30) default NULL,
  company_fax varchar(30) default NULL,
  company_address1 varchar(30) default NULL,
  company_address2 varchar(30) default NULL,
  company_city varchar(30) default NULL,
  company_state varchar(30) default NULL,
  company_zip varchar(11) default NULL,
  company_primary_url varchar(255) default NULL,
  company_owner int(11) NOT NULL default '0',
  company_description tinytext,
  PRIMARY KEY  (company_id)
) TYPE=MyISAM;

CREATE TABLE contacts (
  contact_id int(11) NOT NULL auto_increment,
  contact_first_name varchar(30) default NULL,
  contact_last_name varchar(30) default NULL,
  contact_order_by varchar(30) NOT NULL default '',
  contact_title varchar(50) default NULL,
  contact_birthday datetime default NULL,
  contact_company varchar(100) default NULL,
  contact_type varchar(20) default NULL,
  contact_email varchar(100) default NULL,
  contact_email2 varchar(100) default NULL,
  contact_phone varchar(30) default NULL,
  contact_phone2 varchar(30) default NULL,
  contact_mobile varchar(30) default NULL,
  contact_address1 varchar(30) default NULL,
  contact_address2 varchar(30) default NULL,
  contact_city varchar(30) default NULL,
  contact_state varchar(30) default NULL,
  contact_zip varchar(11) default NULL,
  contact_country varchar(30) default NULL,
  contact_icq varchar(20) default NULL,
  contact_notes text,
  contact_project int(11) NOT NULL default '0',
  contact_icon varchar(20) default 'obj/contact',
  PRIMARY KEY  (contact_id),
  KEY idx_oby (contact_order_by),
  KEY idx_co (contact_company),
  KEY idx_prp (contact_project)
) TYPE=MyISAM;

CREATE TABLE events (
  event_id int(11) NOT NULL auto_increment,
  event_title varchar(255) NOT NULL default '',
  event_start_date bigint(20) unsigned NOT NULL default '0',
  event_end_date bigint(20) unsigned NOT NULL default '0',
  event_parent int(11) unsigned NOT NULL default '0',
  event_description text,
  event_times_recuring int(11) unsigned NOT NULL default '0',
  event_recurs int(11) unsigned NOT NULL default '0',
  event_remind int(10) unsigned NOT NULL default '0',
  event_icon varchar(20) default 'obj/event',
  PRIMARY KEY  (event_id),
  KEY id_esd (event_start_date),
  KEY id_eed (event_end_date),
  KEY id_evp (event_parent)
) TYPE=MyISAM;

CREATE TABLE files (
  file_id int(11) NOT NULL auto_increment,
  file_real_filename varchar(255) NOT NULL default '',
  file_project int(11) NOT NULL default '0',
  file_task int(11) NOT NULL default '0',
  file_name varchar(255) NOT NULL default '',
  file_parent int(11) default '0',
  file_description varchar(255) default NULL,
  file_content text,
  file_type varchar(100) default NULL,
  file_owner int(11) default '0',
  file_date datetime default NULL,
  file_size int(11) default '0',
  file_version float NOT NULL default '0',
  file_icon varchar(20) default 'obj/',
  PRIMARY KEY  (file_id),
  KEY idx_file_task (file_task),
  KEY idx_file_project (file_project),
  KEY idx_file_parent (file_parent)
) TYPE=MyISAM;

CREATE TABLE files_index (
  file_id int(11) NOT NULL default '0',
  word varchar(50) NOT NULL default '',
  word_placement int(11) default '0',
  PRIMARY KEY  (file_id,word),
  KEY idx_fwrd (word),
  KEY idx_wcnt (word_placement)
) TYPE=MyISAM;

CREATE TABLE forum_messages (
  message_id int(11) NOT NULL auto_increment,
  message_forum int(11) NOT NULL default '0',
  message_parent int(11) NOT NULL default '0',
  message_author int(11) NOT NULL default '0',
  message_title varchar(255) NOT NULL default '',
  message_date datetime default '0000-00-00 00:00:00',
  message_body text,
  message_published tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (message_id),
  KEY idx_mparent (message_parent),
  KEY idx_mdate (message_date),
  KEY idx_mforum (message_forum)
) TYPE=MyISAM;

CREATE TABLE forums (
  forum_id int(11) NOT NULL auto_increment,
  forum_project int(11) NOT NULL default '0',
  forum_status tinyint(4) NOT NULL default '-1',
  forum_owner int(11) NOT NULL default '0',
  forum_name varchar(50) NOT NULL default '',
  forum_create_date datetime default '0000-00-00 00:00:00',
  forum_last_date datetime default '0000-00-00 00:00:00',
  forum_message_count int(11) NOT NULL default '0',
  forum_description varchar(255) default NULL,
  forum_moderated tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (forum_id),
  KEY idx_fproject (forum_project),
  KEY idx_fowner (forum_owner),
  KEY forum_status (forum_status)
) TYPE=MyISAM;

CREATE TABLE permissions (
  permission_id int(11) NOT NULL auto_increment,
  permission_user int(11) NOT NULL default '0',
  permission_grant_on varchar(12) NOT NULL default '',
  permission_item int(11) NOT NULL default '0',
  permission_value int(11) NOT NULL default '0',
  PRIMARY KEY  (permission_id),
  UNIQUE KEY idx_pgrant_on (permission_grant_on,permission_item,permission_user),
  KEY idx_puser (permission_user),
  KEY idx_pvalue (permission_value)
) TYPE=MyISAM;

CREATE TABLE projects (
  project_id int(11) NOT NULL auto_increment,
  project_company int(11) NOT NULL default '0',
  project_name varchar(255) default NULL,
  project_short_name varchar(10) default NULL,
  project_owner int(11) default '0',
  project_url varchar(255) default NULL,
  project_demo_url varchar(255) default NULL,
  project_start_date datetime default NULL,
  project_end_date datetime default NULL,
  project_actual_end_date datetime default NULL,
  project_status int(11) default '0',
  project_precent_complete tinyint(4) default '0',
  project_color_identifier varchar(6) default 'eeeeee',
  project_description text,
  project_target_budget int(11) default '0',
  project_actual_budget int(11) default '0',
  project_creator int(11) default '0',
  project_active tinyint(4) default '1',
  PRIMARY KEY  (project_id),
  KEY idx_project_owner (project_owner),
  KEY idx_sdate (project_start_date),
  KEY idx_edate (project_end_date),
  KEY project_short_name (project_short_name)
) TYPE=MyISAM;

CREATE TABLE task_comments (
  comment_id int(11) NOT NULL auto_increment,
  comment_user int(11) NOT NULL default '0',
  comment_task int(11) NOT NULL default '0',
  comment_title varchar(255) NOT NULL default '',
  comment_unique_id varchar(13) NOT NULL default '',
  comment_body text,
  comment_date datetime default NULL,
  PRIMARY KEY  (comment_id),
  UNIQUE KEY idx_tc (comment_task,comment_unique_id),
  KEY idx_tc2 (comment_user)
) TYPE=MyISAM;

CREATE TABLE task_log (
  task_log_id int(11) NOT NULL auto_increment,
  task_log_task int(11) NOT NULL default '0',
  task_log_parent int(11) default '0',
  task_log_name varchar(255) default NULL,
  task_log_description text,
  task_log_creator int(11) NOT NULL default '0',
  PRIMARY KEY  (task_log_id),
  KEY idx_log_task (task_log_task),
  KEY idx_log_parent (task_log_parent)
) TYPE=MyISAM;

CREATE TABLE tasks (
  task_id int(11) NOT NULL auto_increment,
  task_name varchar(255) default NULL,
  task_parent int(11) default '0',
  task_milestone tinyint(1) default '0',
  task_project int(11) NOT NULL default '0',
  task_owner int(11) NOT NULL default '0',
  task_start_date datetime default NULL,
  task_duration float unsigned default '0',
  task_hours_worked int(10) unsigned default '0',
  task_end_date datetime default NULL,
  task_status int(11) default '0',
  task_priority tinyint(4) default '0',
  task_precent_complete tinyint(4) default '0',
  task_description text,
  task_target_budget int(11) default '0',
  task_related_url varchar(255) default NULL,
  task_creator int(11) NOT NULL default '0',
  task_order int(11) NOT NULL default '0',
  task_client_publish tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (task_id),
  KEY idx_task_parent (task_parent),
  KEY idx_task_project (task_project),
  KEY idx_task_owner (task_project),
  KEY task_order (task_order)
) TYPE=MyISAM;

CREATE TABLE tickets (
  ticket int(10) unsigned NOT NULL auto_increment,
  author varchar(100) NOT NULL default '',
  recipient varchar(100) NOT NULL default '',
  subject varchar(100) NOT NULL default '',
  attachment tinyint(1) unsigned NOT NULL default '0',
  timestamp int(10) unsigned NOT NULL default '0',
  type varchar(15) NOT NULL default '',
  assignment int(10) unsigned NOT NULL default '0',
  parent int(10) unsigned NOT NULL default '0',
  activity int(10) unsigned NOT NULL default '0',
  priority tinyint(1) unsigned NOT NULL default '1',
  cc varchar(100) NOT NULL default '',
  body text NOT NULL,
  signature text,
  PRIMARY KEY  (ticket),
  KEY parent (parent),
  KEY type (type)
) TYPE=MyISAM;

CREATE TABLE user_tasks (
  user_id int(11) NOT NULL default '0',
  user_type tinyint(4) NOT NULL default '0',
  task_id int(11) NOT NULL default '0',
  PRIMARY KEY  (user_id,task_id),
  KEY user_type (user_type)
) TYPE=MyISAM;

CREATE TABLE users (
  user_id int(11) NOT NULL auto_increment,
  user_username varchar(20) NOT NULL default '',
  user_password varchar(20) NOT NULL default '',
  user_parent int(11) NOT NULL default '0',
  user_type set('user','client','admin') default NULL,
  user_first_name varchar(50) default NULL,
  user_last_name varchar(50) default NULL,
  user_company smallint(6) default '0',
  user_email varchar(60) default NULL,
  user_phone varchar(30) default NULL,
  user_home_phone varchar(30) default NULL,
  user_mobile varchar(30) default NULL,
  user_address1 varchar(30) default NULL,
  user_address2 varchar(30) default NULL,
  user_city varchar(30) default NULL,
  user_state varchar(30) default 'FL',
  user_zip varchar(11) default NULL,
  user_country varchar(30) default 'US',
  user_icq varchar(20) default NULL,
  user_aol varchar(20) default NULL,
  user_birthday datetime default NULL,
  user_pic text,
  user_owner int(11) NOT NULL default '0',
  signature text,
  PRIMARY KEY  (user_id),
  KEY idx_uid (user_username),
  KEY idx_pwd (user_password),
  KEY idx_user_parent (user_parent)
) TYPE=MyISAM;

CREATE TABLE eventlog (
  objecturl varchar(30) NOT NULL default 'unknown',
  actiontype varchar(30) default 'unknown',
  status int(11) NOT NULL default '0',
  userid int(11) NOT NULL default '0',
  dt datetime NOT NULL default '0000-00-00 00:00:00'
) TYPE=MyISAM;

CREATE TABLE localization (
  lang varchar(5) NOT NULL,
  name varchar(255) NOT NULL,
  value varchar(255) default NULL,
  PRIMARY KEY (lang,name)
) TYPE=MyISAM;
    
CREATE TABLE attendees (
  event_id int(11) NOT NULL,
  attendee_id int(11) NOT NULL,
  attendee_status smallint,
  attendee_reminder smallint,
  PRIMARY KEY(event_id,attendee_id),
  FOREIGN KEY(event_id) REFERENCES events(event_id)
);

CREATE TABLE logs (
 objecturl varchar(50) NOT NULL,
 actiontype varchar(20) NOT NULL,
 status int NOT NULL,
 userid varchar(10) DEFAULT NULL,
 dt datetime
);

INSERT INTO localization VALUES 
('fr','Calendar','Agenda'),
('fr','Projects','Projets'),
('fr','Files','Documents'),
('fr','Forums','Forums'),
('fr','Localization','Traductions'),
('fr','Module','Module'),
('fr','Permission Type','Type de permission'),
('fr','Select user','Choisir utilisateur'),
('fr','Tasks','T�ches'),
('fr','Tickets','Tickets'),
('fr','Translation','Traduction'),
('fr','User Admin','Admin utilisateurs'),
('fr','deny','interdire'),
('fr','read-only','lecture seule'),
('fr','read-write','lecture-�criture'),
('fr','Locale key','Cl�'),
('fr','sort by','trier par'),
('fr','Admin','Administration'),
('fr','All','Tout'),
('fr','Clients & Companies','Clients & Soci�t�s'),
('fr','Companies','Soci�t�s'),
('fr','Contacts','Contacts'),
('fr','No permissions for this User','Pas de permissions pour cet utilisateur');

#
# ATTENTION: 
# Customize this section for your installation.
# Recommended changes include:
#   New admin username -> replace {admin}
#   New admin password -> replace {passwd]
#   New admin email -> replace {admin@localhost}
#

INSERT INTO users VALUES (1,'admin',password('passwd'),0,'','Admin','Person',1,'admin@localhost','','','','','','','','','','','','0000-00-00 00:00:00',NULL,0,'');

INSERT INTO permissions VALUES (1,1,"all",-1, -1);
