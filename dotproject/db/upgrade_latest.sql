#
# $Id$
# 
# DO NOT USE THIS SCRIPT DIRECTLY - USE THE INSTALLER INSTEAD.
#
# All entries must be date stamped in the correct format.
#

# 20101216
# Manage contacts properly
INSERT INTO `%dbprefix%config` VALUES (0, 'user_contact_inactivate', 'true', 'auth', 'checkbox');
INSERT INTO `%dbprefix%config` VALUES (0, 'user_contact_activate', 'false', 'auth', 'checkbox');

