# $Id$
#
# Upgrade dotProject DB Schema
# Version 1.0.1 to release 1.0.2
#
# NOTE: This will NOT upgrade older releases to release 1.0.1
#       You must apply older upgrade script first
#
# !                  W A R N I N G                !
# !BACKUP YOU DATABASE BEFORE APPLYING THIS SCRIPT!
# !                  W A R N I N G                !
#
# add task_departments and contacts to task table
ALTER TABLE `tasks` ADD `task_departments` CHAR( 100 ) ;
ALTER TABLE `tasks` ADD `task_contacts` CHAR( 100 ) ;

# add contact_department to contacts table
ALTER TABLE `contacts` ADD `contact_department` TINYTEXT AFTER `contact_company` ;

# add custom info to tasks
ALTER TABLE `tasks` ADD `task_custom` LONGTEXT;

# custom info on companies
ALTER TABLE `companies` ADD `company_custom` LONGTEXT;
