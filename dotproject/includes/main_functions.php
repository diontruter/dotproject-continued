<?php /* INCLUDES $Id$ */
##
## Global General Purpose Functions
##

$CR = "\n";
define('SECONDS_PER_DAY', 60 * 60 * 24);

##
## Returns the best color based on a background color (x is cross-over)
##
function bestColor( $bg, $lt='#ffffff', $dk='#000000' ) {
// cross-over color = x
	$x = 128;
	$r = hexdec( substr( $bg, 0, 2 ) );
	$g = hexdec( substr( $bg, 2, 2 ) );
	$b = hexdec( substr( $bg, 4, 2 ) );

	if ($r < $x && $g < $x || $r < $x && $b < $x || $b < $x && $g < $x) {
		return $lt;
	} else {
		return $dk;
	}
}

##
## returns a select box based on an key,value array where selected is based on key
##
function arraySelect( &$arr, $select_name, $select_attribs, $selected, $translate=false ) {
	GLOBAL $AppUI;
	reset( $arr );
	$s = "\n<select name=\"$select_name\" $select_attribs>";
	foreach ($arr as $k => $v ) {
		if ($translate) {
			$v = @$AppUI->_( $v );
			// This is supplied to allow some Hungarian characters to
			// be translated correctly. There are probably others.
			// As such a more general approach probably based upon an
			// array lookup for replacements would be a better approach. AJD.
			$v=str_replace('&#369;','�',$v);
			$v=str_replace('&#337;','�',$v);
		}
		$s .= "\n\t<option value=\"".$k."\"".($k == $selected ? " selected=\"selected\"" : '').">" . dPformSafe( $v ) . "</option>";
	}
	$s .= "\n</select>\n";
	return $s;
}

##
## returns a select box based on an key,value array where selected is based on key
##
function arraySelectTree( &$arr, $select_name, $select_attribs, $selected, $translate=false ) {
	GLOBAL $AppUI;
	reset( $arr );

	$children = array();
	// first pass - collect children
	foreach ($arr as $k => $v ) {
		$id = $v[0];
		$pt = $v[2];
		$list = @$children[$pt] ? $children[$pt] : array();
		array_push($list, $v);
	    $children[$pt] = $list;
	}
	$list = tree_recurse($arr[0][2], '', array(), $children);
	return arraySelect( $list, $select_name, $select_attribs, $selected, $translate );
}

function tree_recurse($id, $indent, $list, $children) {
	if (@$children[$id]) {
		foreach ($children[$id] as $v) {
			$id = $v[0];
			$txt = $v[1];
			$pt = $v[2];
			$list[$id] = "$indent $txt";
			$list = tree_recurse($id, "$indent--", $list, $children);
		}
	}
	return $list;
}

##
## Merges arrays maintaining/overwriting shared numeric indicees
##
function arrayMerge( $a1, $a2 ) {
	foreach ($a2 as $k => $v) {
		$a1[$k] = $v;
	}
	return $a1;
}

##
## breadCrumbs - show a colon separated list of bread crumbs
## array is in the form url => title
##
function breadCrumbs( &$arr ) {
	GLOBAL $AppUI;
	$crumbs = array();
	foreach ($arr as $k => $v) {
		$crumbs[] = "<a href=\"$k\">".$AppUI->_( $v )."</a>";
	}
	return implode( ' <strong>:</strong> ', $crumbs );
}
##
## generate link for context help -- old version
##
function contextHelp( $title, $link='' ) {
	return dPcontextHelp( $title, $link );
}

function dPcontextHelp( $title, $link='' ) {
	global $AppUI;
	return "<a href=\"#$link\" onClick=\"javascript:window.open('?m=help&dialog=1&hid=$link', 'contexthelp', 'width=400, height=400, left=50, top=50, scrollbars=yes, resizable=yes')\">".$AppUI->_($title)."</a>";
}

##
## displays the configuration array of a module for informational purposes
##
function dPshowModuleConfig( $config ) {
	GLOBAL $AppUI;
	$s = '<table cellspacing="2" cellpadding="2" border="0" class="std" width="50%">';
	$s .= '<tr><th colspan="2">'.$AppUI->_( 'Module Configuration' ).'</th></tr>';
	foreach ($config as $k => $v) {
		$s .= '<tr><td width="50%">'.$AppUI->_( $k ).'</td><td width="50%" class="hilite">'.$AppUI->_( $v ).'</td></tr>';
	}
	$s .= '</table>';
	return ($s);
}

/**
 *	Function to recussively find an image in a number of places
 *	@param string The name of the image
 *	@param string Optional name of the current module
 */
function dPfindImage( $name, $module=null ) {
// uistyle must be declared globally
	global $AppUI, $uistyle;

	if (file_exists( "{$AppUI->cfg['root_dir']}/style/$uistyle/images/$name" )) {
		return "./style/$uistyle/images/$name";
	} else if ($module && file_exists( "{$AppUI->cfg['root_dir']}/modules/$module/images/$name" )) {
		return "./modules/$module/images/$name";
	} else if (file_exists( "{$AppUI->cfg['root_dir']}/images/icons/$name" )) {
		return "./images/icons/$name";
	} else if (file_exists( "{$AppUI->cfg['root_dir']}/images/obj/$name" )) {
		return "./images/obj/$name";
	} else {
		return "./images/$name";
	}
}

/**
 *	Workaround removed due to problems in Opera and other issues
 *	with IE6.
 *	Workaround to display png images with alpha-transparency in IE6.0
 *	@param string The name of the image
 *	@param string The image width
 *	@param string The image height
 *	@param string The alt text for the image
 */
function dPshowImage( $src, $wid='', $hgt='', $alt='', $title='' ) {
	global $AppUI;
	/*
	if (strpos( $src, '.png' ) > 0 && strpos( $_SERVER['HTTP_USER_AGENT'], 'MSIE 6.0' ) !== false) {
		return "<div style=\"height:{$hgt}px; width:{$wid}px; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='$src', sizingMethod='scale');\" ></div>";
	} else {
	*/
		return "<img src=\"$src\" width=\"$wid\" height=\"$hgt\" alt=\"".$AppUI->_($alt)."\" border=\"0\" title=\"".$AppUI->_($title)."\" />";
	// }
}

#
# function to return a default value if a variable is not set
#

function defVal($var, $def) {
	return isset($var) ? $var : $def;
}

/**
* Utility function to return a value from a named array or a specified default
*/
function dPgetParam( &$arr, $name, $def=null ) {
	return isset( $arr[$name] ) ? $arr[$name] : $def;
}

#
# add history entries for tracking changes
#

function addHistory( $description, $project_id = 0, $module_id = 0) {
	global $AppUI;
	/*
	 * TODO:
	 * 1) description should be something like:
	 * 		command(arg1, arg2...)
	 *  for example:
	 * 		new_forum('Forum Name', 'URL')
	 *
	 * This way, the history module will be able to display descriptions
	 * using locale definitions:
	 * 		"new_forum" -> "New forum '%s' was created" -> "Se ha creado un nuevo foro llamado '%s'"
	 *
	 * 2) project_id and module_id should be provided in order to filter history entries
	 *
	 */
	if(!$AppUI->cfg['log_changes']) return;
	$description = str_replace("'", "\'", $description);
	$hsql = "select * from modules where mod_name = 'History' and mod_active = 1";
	$qid = db_exec($hsql);

	if (! $qid || db_num_rows($qid) == 0) {
	  $AppUI->setMsg("History module is not loaded, but your config file has requested that changes be logged.  You must either change the config file or install and activate the history module to log changes.", UI_MSG_ALERT);
	  return;
	}

	$psql =	"INSERT INTO history " .
			"( history_description, history_user, history_date ) " .
	  		" VALUES ( '$description', " . $AppUI->user_id . ", now() )";
	db_exec($psql);
	echo db_error();
}

##
## Looks up a value from the SYSVALS table
##
function dPgetSysVal( $title ) {
	$sql = "
	SELECT syskey_type, syskey_sep1, syskey_sep2, sysval_value
	FROM sysvals,syskeys
	WHERE sysval_title = '$title'
		AND syskey_id = sysval_key_id
	";
	db_loadHash( $sql, $row );
// type 0 = list
	$sep1 = $row['syskey_sep1'];	// item separator
	$sep2 = $row['syskey_sep2'];	// alias separator

	// A bit of magic to handle newlines and returns as separators
	// Missing sep1 is treated as a newline.
	if (!isset($sep1))
	  $sep1 = "\n";
	if ($sep1 == "\\n")
	  $sep1 = "\n";
	if ($sep1 == "\\r")
	  $sep1 = "\r";

	$temp = explode( $sep1, $row['sysval_value'] );
	$arr = array();
	// We use trim() to make sure a numeric that has spaces
	// is properly treated as a numeric
	foreach ($temp as $item) {
		if($item) {
			$temp2 = explode( $sep2, $item );
			if (isset( $temp2[1] )) {
				$arr[trim($temp2[0])] = trim($temp2[1]);
			} else {
				$arr[trim($temp2[0])] = trim($temp2[0]);
			}
		}
	}
	return $arr;
}

function dPuserHasRole( $name ) {
	global $AppUI;
	$uid = $AppUI->user_id;
	$sql = "SELECT r.role_id FROM roles AS r,user_roles AS ur WHERE ur.user_id=$uid AND ur.role_id=r.role_id AND r.role_name='$name'";
	return db_loadResult( $sql );
}

function dPformatDuration($x) {
    global $dPconfig;
    global $AppUI;
    $dur_day = floor($x / $dPconfig['daily_working_hours']);
    //$dur_hour = fmod($x, $dPconfig['daily_working_hours']);
    $dur_hour = $x - $dur_day*$dPconfig['daily_working_hours'];
    $str = '';
    if ($dur_day > 1) {
        $str .= $dur_day .' '. $AppUI->_('days'). ' ';
    } elseif ($dur_day == 1) {
        $str .= $dur_day .' '. $AppUI->_('day'). ' ';
    }

    if ($dur_hour > 1 ) {
        $str .= $dur_hour .' '. $AppUI->_('hours');
    } elseif ($dur_hour > 0 and $dur_hour <= 1) {
        $str .= $dur_hour .' '. $AppUI->_('hour');
    }

    if ($str == '') {
        $str = $AppUI->_("n/a");
    }

    return $str;

}

/**
*/
function dPsetMicroTime() {
	global $microTimeSet;
	list($usec, $sec) = explode(" ",microtime());
	$microTimeSet = (float)$usec + (float)$sec;
}

/**
*/
function dPgetMicroDiff() {
	global $microTimeSet;
	$mt = $microTimeSet;
	dPsetMicroTime();
	return sprintf( "%.3f", $microTimeSet - $mt );
}

/**
* Make text safe to output into double-quote enclosed attirbutes of an HTML tag
*/
function dPformSafe( $txt, $deslash=false ) {
	if (is_object( $txt )) {
		foreach (get_object_vars($txt) as $k => $v) {
			if ($deslash) {
				$obj->$k = htmlspecialchars( stripslashes( $v ) );
			} else {
				$obj->$k = htmlspecialchars( $v );
			}
		}
	} else if (is_array( $txt )) {
		foreach ($txt as $k=>$v) {
			if ($deslash) {
				$txt[$k] = htmlspecialchars( stripslashes( $v ) );
			} else {
				$txt[$k] = htmlspecialchars( $v );
			}
		}
	} else {
		if ($deslash) {
			$txt = htmlspecialchars( stripslashes( $txt ) );
		} else {
			$txt = htmlspecialchars( $txt );
		}
	}
	return $txt;
}

function convert2days( $durn, $units ) {
	global $AppUI;
	switch ($units) {
	case 0:
		return $durn / $AppUI->cfg['daily_working_hours'];
		break;
	case 24:
		return $durn;
	}
}

function formatTime( $uts ) {
	global $AppUI;
	$date = new CDate();
	$date->setDate($uts, DATE_FORMAT_UNIXTIME);	
	return $date->format( $AppUI->getPref('SHDATEFORMAT') );
}

function formatCurrency( $number, $format ) {
	if (!$format) {
		$format = $AppUI->getPref('SHCURRFORMAT');
	}
	setlocale(LC_MONETARY, $format);
	if (function_exists('money_format'))
		return money_format('%i', $number);

	// NOTE: This is called if money format doesn't exist.
	// Money_format only exists on non-windows 4.3.x sites.
	// This uses localeconv to get the information required
	// to format the money.  It tries to set reasonable defaults.
	$mondat = localeconv();
	if (! isset($mondat['int_frac_digits']))
		$mondat['int_frac_digits'] = 2;
	if (! isset($mondat['int_curr_symbol']))
		$mondat['int_curr_symbol'] = '';
	if (! isset($mondat['mon_decimal_point']))
		$mondat['mon_decimal_point'] = '.';
	if (! isset($mondat['mon_thousands_sep']))
		$mondat['mon_thousands_sep'] = ',';
	$numeric_portion = number_format(abs($number),
		$mondat['int_frac_digits'],
		$mondat['mon_decimal_point'],
		$mondat['mon_thousands_sep']);
	// Not sure, but most countries don't put the sign in if it is positive.
	$letter='p';
	$currency_prefix="";
	$currency_suffix="";
	$prefix="";
	$suffix="";
	if ($number < 0) {
		$sign = $mondat['negative_sign'];
		$letter = 'n';
		switch ($mondat['n_sign_posn']) {
			case 0:
				$prefix="(";
				$suffix=")";
				break;
			case 1:
				$prefix = $sign;
				break;
			case 2:
				$suffix = $sign;
				break;
			case 3:
				$currency_prefix = $sign;
				break;
			case 4:
				$currency_suffix = $sign;
				break;
		}
	}
	$currency .= $currency_prefix . $mondat['int_curr_symbol'] . $currency_suffix;
	$space = "";
	if ($mondat[$letter . "_sep_by_space"])
		$space = " ";
	if ($mondat[$letter . "_cs_precedes"]) {
		$result = "$currency$space$numeric_portion";
	} else {
		$result = "$numeric_portion$space$currency";
	}
	return $result;
}

?>
