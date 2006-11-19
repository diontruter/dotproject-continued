#
# $Id$
# 
# DO NOT USE THIS SCRIPT DIRECTLY - USE THE INSTALLER INSTEAD.
#
# All entries must be date stamped in the correct format.
#

# 20061119 gregorerhardt
# archived status replaces project (in)active flag:
# add project archived status
# migrate existing project data
# remove project in/active flag
UPDATE `sysvals` SET `sysval_value` = '0|Not Defined\r\n1|Proposed\r\n2|In Planning\r\n3|In Progress\r\n4|On Hold\r\n5|Complete\r\n6|Template\r\n7|Archived' WHERE `sysval_id` = 1 LIMIT 1;

UPDATE `projects` SET `project_status` = 7 WHERE `project_active` = 0;

ALTER TABLE `projects` DROP `project_active`;
