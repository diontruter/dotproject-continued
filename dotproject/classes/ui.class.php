<?php /* CLASSES $Id$ */
/**
* @package dotproject
* @subpackage core
* @license http://opensource.org/licenses/bsd-license.php BSD License
*/

// Message No Constants
define( 'UI_MSG_OK', 1 );
define( 'UI_MSG_ALERT', 2 );
define( 'UI_MSG_WARNING', 3 );
define( 'UI_MSG_ERROR', 4 );

// global variable holding the translation array
$GLOBALS['translate'] = array();

define( "UI_CASE_UPPER", 1 );
define( "UI_CASE_LOWER", 2 );
define( "UI_CASE_UPPERFIRST", 3 );

/**
* The Application User Interface Class.
*
* @author Andrew Eddie <eddieajau@users.sourceforge.net>
* @version $Revision$
*/
class CAppUI {
/** @var array generic array for holding the state of anything */
	var $state=null;
/** @var int */
	var $user_id=null;
/** @var string */
	var $user_first_name=null;
/** @var string */
	var $user_last_name=null;
/** @var string */
	var $user_company=null;
/** @var int */
	var $user_department=null;
/** @var string */
	var $user_email=null;
/** @var int */
	var $user_type=null;
/** @var array */
	var $user_prefs=null;
/** @var int Unix time stamp */
	var $day_selected=null;

// localisation
/** @var string */
	var $user_locale=null;
/** @var string */
	var $base_locale = 'en'; // do not change - the base 'keys' will always be in english

/** @var string Message string*/
	var $msg = '';
/** @var string */
	var $msgNo = '';
/** @var string Default page for a redirect call*/
	var $defaultRedirect = '';

/** @var array Configuration variable array*/
	var $cfg=null;

/**
* CAppUI Constructor
*/
	function CAppUI() {
		$this->state = array();

		$this->user_id = -1;
		$this->user_first_name = '';
		$this->user_last_name = '';
		$this->user_company = 0;
		$this->user_department = 0;
		$this->user_type = 0;

		$this->project_id = 0;

		$this->defaultRedirect = "";
// set up the default preferences
		$this->user_locale = $this->base_locale;
		$this->user_prefs = array();
	}
/**
* Used to load a php class file from the system classes directory
* @param string $name The class root file name (excluding .class.php)
* @return string The path to the include file
 */
	function getSystemClass( $name=null ) {
		if ($name) {
			if ($root = $this->getConfig( 'root_dir' )) {
				return "$root/classes/$name.class.php";
			}
		}
	}

/**
* Used to load a php class file from the lib directory
*
* @param string $name The class root file name (excluding .class.php)
* @return string The path to the include file
*/
	function getLibraryClass( $name=null ) {
		if ($name) {
			if ($root = $this->getConfig( 'root_dir' )) {
				return "$root/lib/$name.php";
			}
		}
	}

/**
* Used to load a php class file from the module directory
* @param string $name The class root file name (excluding .class.php)
* @return string The path to the include file
 */
	function getModuleClass( $name=null ) {
		if ($name) {
			if ($root = $this->getConfig( 'root_dir' )) {
				return "$root/modules/$name/$name.class.php";
			}
		}
	}

/**
* Sets the internal confuration settings array.
* @param array A named array of configuration variables (usually from config.php)
*/
	function setConfig( &$cfg ) {
		$this->cfg = $cfg;
	}

/**
* Retrieves a configuration setting.
* @param string The name of a configuration setting
* @return The value of the setting, otherwise null if the key is not found in the configuration array
*/
	function getConfig( $key ) {
		if (array_key_exists( $key, $this->cfg )) {
			return $this->cfg[$key];
		} else {
			return null;
		}
	}

/**
* Checks that the current user preferred style is valid/exists.
*/
	function checkStyle() {
		// check if default user's uistyle is installed
		$uistyle = $this->getPref("UISTYLE");

		if ($uistyle && !is_dir("{$this->cfg['root_dir']}/style/$uistyle")) {
			// fall back to host_style if user style is not installed
			$this->setPref( 'UISTYLE', $this->cfg['host_style'] );
		}
	}

/**
* Utility function to read the 'directories' under 'path'
*
* This function is used to read the modules or locales installed on the file system.
* @param string The path to read.
* @return array A named array of the directories (the key and value are identical).
*/
	function readDirs( $path ) {
		$dirs = array();
		$d = dir( "{$this->cfg['root_dir']}/$path" );
		while (false !== ($name = $d->read())) {
			if(is_dir( "{$this->cfg['root_dir']}/$path/$name" ) && $name != "." && $name != ".." && $name != "CVS") {
				$dirs[$name] = $name;
			}
		}
		$d->close();
		return $dirs;
	}

/**
* Utility function to read the 'files' under 'path'
* @param string The path to read.
* @param string A regular expression to filter by.
* @return array A named array of the files (the key and value are identical).
*/
	function readFiles( $path, $filter='.' ) {
		$files = array();

		if ($handle = opendir( $path )) {
			while (false !== ($file = readdir( $handle ))) { 
				if ($file != "." && $file != ".." && preg_match( "/$filter/", $file )) { 
					$files[$file] = $file; 
				} 
			}
			closedir($handle); 
		}
		return $files;
	}

/**
* Utility function to make a file name 'safe'
*
* Strips out mallicious insertion of relative directories (eg ../../dealyfile.php);
* @param string The file name.
* @return array A named array of the files (the key and value are identical).
*/
	function makeFileNameSafe( $file ) {
		$file = str_replace( '../', '', $file );
		$file = str_replace( '..\\', '', $file );
		return $file;
	}

/**
* Sets the user locale.
*
* Looks in the user preferences first.  If this value has not been set by the user it uses the system default set in config.php.
* @param string Locale abbreviation corresponding to the sub-directory name in the locales directory (usually the abbreviated language code).
*/
	function setUserLocale( $loc='' ) {
		if ($loc) {
			$this->user_locale = $loc;
		} else {
			$this->user_locale = @$this->user_prefs['LOCALE'] ? $this->user_prefs['LOCALE'] : $this->cfg['host_locale'];
		}
	}
/**
* Translate string to the local language [same form as the gettext abbreviation]
*
* This is the order of precedence:
* <ul>
* <li>If the key exists in the lang array, return the value of the key
* <li>If no key exists and the base lang is the same as the local lang, just return the string
* <li>If this is not the base lang, then return string with a red star appended to show
* that a translation is required.
* </ul>
* @param string The string to translate
* @param int Option to change the case of the string
* @return string
*/
	function _( $str, $case=0 ) {
		$str = trim($str);
		if (empty( $str )) {
			return '';
		}
		$x = @$GLOBALS['translate'][$str];
		if ($x) {
			$str = $x;
		} else if (@$this->cfg['locale_warn']) {
			if ($this->base_locale != $this->user_locale ||
				($this->base_locale == $this->user_locale && !in_array( $str, @$GLOBALS['translate'] )) ) {
				$str .= @$this->cfg['locale_alert'];
			}
		}
		switch ($case) {
			case UI_CASE_UPPER:
				$str = strtoupper( $str );
				break;
			case UI_CASE_LOWER:
				$str = strtolower( $str );
				break;
			case UI_CASE_UPPERFIRST:
				break;
		}
		return $str;
	}
/**
* Set the display of warning for untranslated strings
* @param string
*/
	function setWarning( $state=true ) {
		$temp = @$this->cfg['locale_warn'];
		$this->cfg['locale_warn'] = $state;
		return $temp;
	}
/**
* Save the url query string
*
* Also saves one level of history.  This is useful for returning from a delete
* operation where the record more not now exist.  Returning to a view page
* would be a nonsense in this case.
* @param string If not set then the current url query string is used
*/
	function savePlace( $query='' ) {
		if (!$query) {
			$query = @$_SERVER['QUERY_STRING'];
		}
		if ($query != @$this->state['SAVEDPLACE']) {
			$this->state['SAVEDPLACE-1'] = @$this->state['SAVEDPLACE'];
			$this->state['SAVEDPLACE'] = $query;
		}
	}
/**
* Resets the internal variable
*/
	function resetPlace() {
		$this->state['SAVEDPLACE'] = '';
	}
/**
* Get the saved place (usually one that could contain an edit button)
* @return string
*/
	function getPlace() {
		return @$this->state['SAVEDPLACE'];
	}
/**
* Redirects the browser to a new page.
*
* Mostly used in conjunction with the savePlace method. It is generally used
* to prevent nasties from doing a browser refresh after a db update.  The
* method deliberately does not use javascript to effect the redirect.
*
* @param string The URL query string to append to the URL
* @param string A marker for a historic 'place, only -1 or an empty string is valid.
*/
	function redirect( $params='', $hist='' ) {
		$session_id = SID;

		session_write_close();
	// are the params empty
		if (!$params) {
		// has a place been saved
			$params = !empty($this->state["SAVEDPLACE$hist"]) ? $this->state["SAVEDPLACE$hist"] : $this->defaultRedirect;
		}
		// Fix to handle cookieless sessions
		if ($session_id != "") {
		  if (!$params)
		    $params = $session_id;
		  else
		    $params .= "&" . $session_id;
		}
		header( "Location: index.php?$params" ); 
		exit();	// stop the PHP execution
	}
/**
* Set the page message.
*
* The page message is displayed above the title block and then again
* at the end of the page.
*
* IMPORTANT: Please note that append should not be used, since for some
* languagues atomic-wise translation doesn't work. Append should be
* deprecated.
*
* @param string The (translated) message
* @param int The type of message
* @param boolean If true, $msg is appended to the current string otherwise
* the existing message is overwritten with $msg.
*/
	function setMsg( $msg, $msgNo=0, $append=false ) {
		$msg = $this->_( $msg );
		$this->msg = $append ? $this->msg.' '.$msg : $msg;
		$this->msgNo = $msgNo;
	}
/**
* Display the formatted message and icon
* @param boolean If true the current message state is cleared.
*/
	function getMsg( $reset=true ) {
		$img = '';
		$class = '';
		$msg = $this->msg;

		switch( $this->msgNo ) {
		case UI_MSG_OK:
			$img = dPshowImage( dPfindImage( 'stock_ok-16.png' ), 16, 16, '' );
			$class = "message";
			break;
		case UI_MSG_ALERT:
			$img = dPshowImage( dPfindImage( 'rc-gui-status-downgr.png' ), 16, 16, '' );
			$class = "message";
			break;
		case UI_MSG_WARNING:
			$img = dPshowImage( dPfindImage( 'rc-gui-status-downgr.png' ), 16, 16, '' );
			$class = "warning";
			break;
		case UI_MSG_ERROR:
			$img = dPshowImage( dPfindImage( 'stock_cancel-16.png' ), 16, 16, '' );
			$class = "error";
			break;
		default:
			$class = "message";
			break;
		}
		if ($reset) {
			$this->msg = '';
			$this->msgNo = 0;
		}
		return $msg ? '<table cellspacing="0" cellpadding="1" border="0"><tr>'
			. "<td>$img</td>"
			. "<td class=\"$class\">$msg</td>"
			. '</tr></table>'
			: '';
	}
/**
* Set the value of a temporary state variable.
*
* The state is only held for the duration of a session.  It is not stored in the database.
* @param string The label or key of the state variable
* @param mixed Value to assign to the label/key
*/
	function setState( $label, $value ) {
		$this->state[$label] = $value;
	}
/**
* Get the value of a temporary state variable.
* @return mixed
*/
	function getState( $label ) {
		return array_key_exists( $label, $this->state) ? $this->state[$label] : NULL;
	}
/**
* Login function
*
* A number of things are done in this method to prevent illegal entry:
* <ul>
* <li>The username and password are trimmed and escaped to prevent malicious
*     SQL being executed
* <li>The username and encrypted password are selected from the database but
*     the comparision is not made by the database, for example
*     <code>...WHERE user_username = '$username' AND password=MD5('$password')...</code>
*     to further prevent the injection of malicious SQL
* </ul>
* The schema previously used the MySQL PASSWORD function for encryption.  This
* is not the recommended technique so a procedure was introduced to first check
* for a match using the PASSWORD function.  If this is successful, then the
* is upgraded to the MD5 encyption format.  This check can be controlled by the
* <code>check_legacy_password</code> configuration variable in </code>config.php</code>
*
* Upon a successful username and password match, several fields from the user
* table are loaded in this object for convenient reference.  The style, localces
* and preferences are also loaded at this time.
*
* @param string The user login name
* @param string The user password
* @return boolean True if successful, false if not
*/
	function login( $username, $password ) {
		$username = trim( db_escape( $username ) );
		$password = trim( db_escape( $password ) );

		$sql = "
		SELECT user_id, user_password AS pwd, password('$password') AS pwdpwd, md5('$password') AS pwdmd5
		FROM users, permissions
		WHERE user_username = '$username'
			AND users.user_id = permissions.permission_user
			AND permission_value <> 0
		";

		$row = null;
		if (!db_loadObject( $sql, $row )) {
			return false;
		}

		if (strcmp( $row->pwd, $row->pwdmd5 )) {
			if ($this->cfg['check_legacy_password']) {
			/* next check the legacy password */
				if (strcmp( $row->pwd, $row->pwdpwd )) {
					/* no match - failed login */
					return false;
				} else {
					/* valid legacy login - update the md5 password */
					$sql = "UPDATE users SET user_password=MD5('$password') WHERE user_id=$row->user_id";
					db_exec( $sql ) or die( "Password update failed." );
					$this->setMsg( 'Password updated', UI_MSG_ALERT );
				}
			} else {
				return false;
			}
		}

		$sql = "
		SELECT user_id, user_first_name, user_last_name, user_company, user_department, user_email, user_type
		FROM users
		WHERE user_id = $row->user_id AND user_username = '$username'
		";

		writeDebug( $sql, 'Login SQL', __FILE__, __LINE__ );

		if( !db_loadObject( $sql, $this ) ) {
			return false;
		}

// load the user preferences
		$this->loadPrefs( $this->user_id );
		$this->setUserLocale();
		$this->checkStyle();
		return true;
	}
/**
* @deprecated
*/
	function logout() {
	}
/**
* Checks whether there is any user logged in.
*/
	function doLogin() {
		return ($this->user_id < 0) ? true : false;
	}
/**
* Gets the value of the specified user preference
* @param string Name of the preference
*/
	function getPref( $name ) {
		return @$this->user_prefs[$name];
	}
/**
* Sets the value of a user preference specified by name
* @param string Name of the preference
* @param mixed The value of the preference
*/
	function setPref( $name, $val ) {
		$this->user_prefs[$name] = $val;
	}
/**
* Loads the stored user preferences from the database into the internal
* preferences variable.
* @param int User id number
*/
	function loadPrefs( $uid=0 ) {
		$sql = "SELECT pref_name, pref_value FROM user_preferences WHERE pref_user = $uid";
		//writeDebug( $sql, "Preferences for user $uid, SQL", __FILE__, __LINE__ );
		$prefs = db_loadHashList( $sql );
		$this->user_prefs = array_merge( $this->user_prefs, db_loadHashList( $sql ) );
	}

// --- Module connectors

/**
* Gets a list of the installed modules
* @return array Named array list in the form 'module directory'=>'module name'
*/
	function getInstalledModules() {
		$sql = "
		SELECT mod_directory, mod_ui_name
		FROM modules
		ORDER BY mod_directory
		";
		return (db_loadHashList( $sql ));
	}
/**
* Gets a list of the active modules
* @return array Named array list in the form 'module directory'=>'module name'
*/
	function getActiveModules() {
		$sql = "
		SELECT mod_directory, mod_ui_name
		FROM modules
		WHERE mod_active > 0
		ORDER BY mod_directory
		";
		return (db_loadHashList( $sql ));
	}
/**
* Gets a list of the modules that should appear in the menu
* @return array Named array list in the form
* ['module directory', 'module name', 'module_icon']
*/
	function getMenuModules() {
		$sql = "
		SELECT mod_directory, mod_ui_name, mod_ui_icon
		FROM modules
		WHERE mod_active > 0 AND mod_ui_active > 0
		ORDER BY mod_ui_order
		";
		return (db_loadList( $sql ));
	}
}

/**
* Tabbed box abstract class
*/
class CTabBox_core {
/** @var array */
	var $tabs=NULL;
/** @var int The active tab */
	var $active=NULL;
/** @var string The base URL query string to prefix tab links */
	var $baseHRef=NULL;
/** @var string The base path to prefix the include file */
	var $baseInc;

/**
* Constructor
* @param string The base URL query string to prefix tab links
* @param string The base path to prefix the include file
* @param int The active tab
*/
	function CTabBox( $baseHRef='', $baseInc='', $active=0 ) {
		$this->tabs = array();
		$this->active = $active;
		$this->baseHRef = ($baseHRef ? "$baseHRef&" : "?");
		$this->baseInc = $baseInc;
	}
/**
* Gets the name of a tab
* @return string
*/
	function getTabName( $idx ) {
		return $this->tabs[$idx][1];
	}
/**
* Adds a tab to the object
* @param string File to include
* @param The display title/name of the tab
*/
	function add( $file, $title ) {
		$this->tabs[] = array( $file, $title );
	}
/**
* Displays the tabbed box
*
* This function may be overridden
*
* @param string Can't remember whether this was useful
*/
	function show( $extra='' ) {
		GLOBAL $AppUI;
		reset( $this->tabs );
		$s = '';
	// tabbed / flat view options
		if (@$AppUI->getPref( 'TABVIEW' ) == 0) {
			$s .= '<table border="0" cellpadding="2" cellspacing="0" width="100%"><tr><td nowrap="nowrap">';
			$s .= '<a href="'.$this->baseHRef.'tab=0">'.$AppUI->_('tabbed').'</a> : ';
			$s .= '<a href="'.$this->baseHRef.'tab=-1">'.$AppUI->_('flat').'</a>';
			$s .= '</td>'.$extra.'</tr></table>';
			echo $s;
		} else {
			if ($extra) {
				echo '<table border="0" cellpadding="2" cellspacing="0" width="100%"><tr>'.$extra.'</tr></table>';
			} else {
				echo '<img src="./images/shim.gif" height="10" width="1" />';
			}
		}

		if ($this->active < 0 && @$AppUI->getPref( 'TABVIEW' ) != 2 ) {
		// flat view, active = -1
			echo '<table border="0" cellpadding="2" cellspacing="0" width="100%">';
			foreach ($this->tabs as $v) {
				echo '<tr><td><strong>'.$AppUI->_($v[1]).'</strong></td></tr>';
				echo '<tr><td>';
				include_once $this->baseInc.$v[0].".php";
				echo '</td></tr>';
			}
			echo '</table>';
		} else {
		// tabbed view
			$s = "<table width=\"100%\" border=\"0\" cellpadding=\"3\" cellspacing=\"0\">\n<tr>";
			foreach( $this->tabs as $k => $v ) {
				$class = ($k == $this->active) ? 'tabon' : 'taboff';
				$s .= "\n\t<td width=\"1%\" nowrap=\"nowrap\" class=\"tabsp\">";
				$s .= "\n\t\t<img src=\"./images/shim.gif\" height=\"1\" width=\"1\" alt=\"\" />";
				$s .= "\n\t</td>";
				$s .= "\n\t<td width=\"1%\" nowrap=\"nowrap\" class=\"$class\">";
				$s .= "\n\t\t<a href=\"{$this->baseHRef}tab=$k\">".$AppUI->_($v[1])."</a>";
				$s .= "\n\t</td>";
			}
			$s .= "\n\t<td nowrap=\"nowrap\" class=\"tabsp\">&nbsp;</td>";
			$s .= "\n</tr>";
			$s .= "\n<tr>";
			$s .= '<td width="100%" colspan="'.(count($this->tabs)*2 + 1).'" class="tabox">';
			echo $s;
			require_once $this->baseInc.$this->tabs[$this->active][0].'.php';
			echo "\n</td>\n</tr>\n</table>";
		}
	}
}

/**
* Title box abstract class
*/
class CTitleBlock_core {
/** @var string The main title of the page */
	var $title='';
/** @var string The name of the icon used to the left of the title */
	var $icon='';
/** @var string The name of the module that this title block is displaying in */
	var $module='';
/** @var array An array of the table 'cells' to the right of the title block and for bread-crumbs */
	var $cells=null;
/** @var string The reference for the context help system */
	var $helpref='';
/**
* The constructor
*
* Assigns the title, icon, module and help reference.  If the user does not
* have permission to view the help module, then the context help icon is
* not displayed.
*/
	function CTitleBlock_core( $title, $icon='', $module='', $helpref='' ) {
		$this->title = $title;
		$this->icon = $icon;
		$this->module = $module;
		$this->helpref = $helpref;
		$this->cells1 = array();
		$this->cells2 = array();
		$this->crumbs = array();
		$this->showhelp = !getDenyRead( 'help' );
	}
/**
* Adds a table 'cell' beside the Title string
*
* Cells are added from left to right.
*/
	function addCell( $data='', $attribs='', $prefix='', $suffix='' ) {
		$this->cells1[] = array( $attribs, $data, $prefix, $suffix );
	}
/**
* Adds a table 'cell' to left-aligned bread-crumbs
*
* Cells are added from left to right.
*/
	function addCrumb( $link, $label, $icon='' ) {
		$this->crumbs[$link] = array( $label, $icon );
	}
/**
* Adds a table 'cell' to the right-aligned bread-crumbs
*
* Cells are added from left to right.
*/
	function addCrumbRight( $data='', $attribs='', $prefix='', $suffix='' ) {
		$this->cells2[] = array( $attribs, $data, $prefix, $suffix );
	}
/**
* Creates a standarised, right-aligned delete bread-crumb and icon.
*/
	function addCrumbDelete( $title, $canDelete='', $msg='' ) {
		global $AppUI;
		$this->addCrumbRight(
			'<table cellspacing="0" cellpadding="0" border="0"?<tr><td>'
			. '<a href="javascript:delIt()" title="'.($canDelete?'':$msg).'">'
			. dPshowImage( './images/icons/'.($canDelete?'stock_delete-16.png':'stock_trash_full-16.png'), '16', '16',  '' )
			. '</a>'
			. '</td><td>&nbsp;'
			. '<a href="javascript:delIt()" title="'.($canDelete?'':$msg).'">' . $AppUI->_( $title ) . '</a>'
			. '</td></tr></table>'
		);
	}
/**
* The drawing function
*/
	function show() {
		global $AppUI;
		$CR = "\n";
		$CT = "\n\t";
		$s = $CR . '<table width="100%" border="0" cellpadding="1" cellspacing="1">';
		$s .= $CR . '<tr>';
		if ($this->icon) {
			$s .= $CR . '<td width="42">';
			$s .= dPshowImage( dPFindImage( $this->icon, $this->module ), '42', '42' );
			$s .= '</td>';
		}
		$s .= $CR . '<td align="left" width="100%" nowrap="nowrap"><h1>' . $AppUI->_($this->title) . '</h1></td>';
		foreach ($this->cells1 as $c) {
			$s .= $c[2] ? $CR . $c[2] : '';
			$s .= $CR . '<td align="right" nowrap="nowrap"' . ($c[0] ? " $c[0]" : '') . '>';
			$s .= $c[1] ? $CT . $c[1] : '&nbsp;';
			$s .= $CR . '</td>';
			$s .= $c[3] ? $CR . $c[3] : '';
		}
		if ($this->showhelp) {
			$s .= '<td nowrap="nowrap" width="20" align="right">';
			//$s .= $CT . contextHelp( '<img src="./images/obj/help.gif" width="14" height="16" border="0" alt="'.$AppUI->_( 'Help' ).'" />', $this->helpref );

			$s .= "\n\t<a href=\"#$this->helpref\" onClick=\"javascript:window.open('?m=help&dialog=1&hid=$this->helpref', 'contexthelp', 'width=400, height=400, left=50, top=50, scrollbars=yes, resizable=yes')\" title=\"".$AppUI->_( 'Help' )."\">";
			$s .= "\n\t\t" . dPshowImage( './images/icons/stock_help-16.png', '16', '16', $AppUI->_( 'Help' ) );
			$s .= "\n\t</a>";
			$s .= "\n</td>";
		}
		$s .= "\n</tr>";
		$s .= "\n</table>";

		if (count( $this->crumbs ) || count( $this->cells2 )) {
			$crumbs = array();
			foreach ($this->crumbs as $k => $v) {
				$t = $v[1] ? '<img src="' . dPfindImage( $v[1], $this->module ) . '" border="" alt="" />&nbsp;' : '';
				$t .= $AppUI->_( $v[0] );
				$crumbs[] = "<a href=\"$k\">$t</a>";
			}
			$s .= "\n<table border=\"0\" cellpadding=\"4\" cellspacing=\"0\" width=\"100%\">";
			$s .= "\n<tr>";
			$s .= "\n\t<td nowrap=\"nowrap\">";
			$s .= "\n\t\t" . implode( ' <strong>:</strong> ', $crumbs );
			$s .= "\n\t</td>";

			foreach ($this->cells2 as $c) {
				$s .= $c[2] ? "\n$c[2]" : '';
				$s .= "\n\t<td align=\"right\" nowrap=\"nowrap\"" . ($c[0] ? " $c[0]" : '') . '>';
				$s .= $c[1] ? "\n\t$c[1]" : '&nbsp;';
				$s .= "\n\t</td>";
				$s .= $c[3] ? "\n\t$c[3]" : '';
			}

			$s .= "\n</tr>\n</table>";
		}
		echo "$s";
	}
}
// !! Ensure there is no white space after this close php tag.
?>
