<?php /* SYSTEM $Id$ */

/**
* Preferences class
*/
class CPreferences {
        var $pref_user = NULL;
        var $pref_name = NULL;
        var $pref_value = NULL;

        function CPreferences() {
                // empty constructor
        }

        function bind( $hash ) {
                if (!is_array( $hash )) {
                        return "CPreferences::bind failed";
                } else {
                        bindHashToObject( $hash, $this );
                        return NULL;
                }
        }

        function check() {
                // TODO MORE
                return NULL; // object is ok
        }

        function store() {
                $msg = $this->check();
                if( $msg ) {
                        return "CPreference::store-check failed<br />$msg";
                }
                if (($msg = $this->delete())) {
                        return "CPreference::store-delete failed<br />$msg";
                }
                if (!($ret = db_insertObject( 'user_preferences', $this, 'pref_user' ))) {
                        return "CPreference::store failed <br />" . db_error();
                } else {
                        return NULL;
                }
        }

        function delete() {
                $sql = "DELETE FROM user_preferences WHERE pref_user = $this->pref_user AND pref_name = '$this->pref_name'";
                if (!db_exec( $sql )) {
                        return db_error();
                } else {
                        return NULL;
                }
        }
}

/**
* Module class
*/
class CModule extends CDpObject {
        var $mod_id=null;
        var $mod_name=null;
        var $mod_directory=null;
        var $mod_version=null;
        var $mod_setup_class=null;
        var $mod_type=null;
        var $mod_active=null;
        var $mod_ui_name=null;
        var $mod_ui_icon=null;
        var $mod_ui_order=null;
        var $mod_ui_active=null;
        var $mod_description=null;
        var $permissions_item_label=null;
        var $permissions_item_field=null;
        var $permissions_item_table=null;

        function CModule() {
                $this->CDpObject( 'modules', 'mod_id' );
        }

        function install() {
                $sql = "SELECT mod_directory FROM modules WHERE mod_directory = '$this->mod_directory'";
                if (db_loadHash( $sql, $temp )) {
                        // the module is already installed
                        // TODO: check for older version - upgrade
                        return false;
                }
                $sql = 'SELECT max(mod_ui_order)
                        FROM modules';
                
                // We need to account for "pre-installed" modules that are "UI Inaccessible"
                // in order to make sure we get the "correct" initial value for .
                // mod_ui_order values of "UI Inaccessible" modules are irrelevant
                // and should probably be set to 0 so as not to interfere.
                $sql .= " WHERE mod_name NOT LIKE 'Public'";
                
                $this->mod_ui_order = db_loadResult($sql) + 1;

                $perms =& $GLOBALS['AppUI']->acl();
                $perms->addModule($this->mod_directory, $this->mod_name);
                // Determine if it is an admin module or not, then add it to the correct set
                if (! isset($this->mod_admin))
                        $this->mod_admin = 0;
                if ($this->mod_admin) {
                        $perms->addGroupItem($this->mod_directory, "admin");
                } else {
                        $perms->addGroupItem($this->mod_directory, "non_admin");
                }
                if (isset($this->permissions_item_table) && $this->permissions_item_table)
                  $perms->addModuleSection($this->permissions_item_table);
                $this->store();
                return true;
        }

        function remove() {
                $sql = "DELETE FROM modules WHERE mod_id = $this->mod_id";
                if (!db_exec( $sql )) {
                        return db_error();
                } else {
                        $perms =& $GLOBALS['AppUI']->acl();
                        if (! isset($this->mod_admin))
                                $this->mod_admin = 0;
                        if ($this->mod_admin) {
                                $perms->deleteGroupItem($this->mod_directory, "admin");
                        } else {
                                $perms->deleteGroupItem($this->mod_directory, "non_admin");
                        }
                        $perms->deleteModule($this->mod_directory);
                        if (isset($this->permissions_item_table) && $this->permissions_item_table)
                          $perms->deleteModuleSection($this->permissions_item_table);
                        return NULL;
                }
        }

        function move( $dirn ) {
                $new_ui_order = $this->mod_ui_order;
                
                if ($dirn == 'moveup') {
                        $other_new=$new_ui_order;
                        $new_ui_order--;
                } else if ($dirn == 'movedn') {
                        $other_new=$new_ui_order;
                        $new_ui_order++;
                }
                
                if ($new_ui_order) { //make sure we aren't going "up" to 0
                        $q  = new DBQuery;
                        if ($other_new) { //make sure value was set.
                                $q->addTable('modules');
                                $q->addUpdate('mod_ui_order', ''.$other_new);
                                $q->addWhere("mod_ui_order = $new_ui_order");
                                $q->exec();
                                $q->clear();
                        }
                        $q->addTable('modules');
                        $q->addUpdate('mod_ui_order', "$new_ui_order");
                        $q->addWhere("mod_id = $this->mod_id");
                        $q->exec();
                        $q->clear();
                        
                        $this->mod_ui_order = $new_ui_order;
                }
        }
        
// overridable functions
        function moduleInstall() {
                return null;
        }
        function moduleRemove() {
                return null;
        }
        function moduleUpgrade() {
                return null;
        }
}

/**
* Configuration class
*/
class CConfig extends CDpObject {

        function CConfig() {
                $this->CDpObject( 'config', 'config_id' );
        }

        function getChildren($id) {
                $this->_query->clear();
                $this->_query->addTable('config_list');
                $this->_query->addOrder('config_list_id');
                $this->_query->addWhere('config_id = ' . $id);
                $sql = $this->_query->prepare();
                $this->_query->clear();
                return db_loadHashList($sql, 'config_list_id');
        }

}


class bcode {
        var $_billingcode_id=NULL;
        var $company_id;
        var $billingcode_desc;
        var $billingcode_name;
        var $billingcode_value;
        var $billingcode_status;

        function bcode() {
        }

        function bind( $hash ) {
                if (!is_array($hash)) {
                        return "Billing Code::bind failed";
                } else {
                        bindHashToObject( $hash, $this );
                        return NULL;
                }
        }

        function delete() {
                $sql = "update billingcode set billingcode_status=1 where billingcode_id='".$this->_billingcode_id."'";
                if (!db_exec( $sql )) {
                        return db_error();
                } else {
                        return NULL;
                }
        }

        function store() {
         				$q = new DBQuery;
                $q->addQuery('billingcode_id');
								$q->addTable('billingcode');
								$q->addWhere('billingcode_name = \'' . $this->billingcode_name . "'");
								$q->addWhere('company_id = ' . $this->company_id);
                if ($q->loadResult())
								        return 'Billing Code::code already exists';
                else if (!($ret = db_insertObject ( 'billingcode', $this, 'billingcode_id' ))) {
                        return "Billing Code::store failed <br />" . db_error();
                } else {
                        return NULL;
                }
        }
}