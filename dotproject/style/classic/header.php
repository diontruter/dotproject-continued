<?php /* STYLE/CLASSIC $Id$ */
$dialog = dPgetParam( $_GET, 'dialog', 0 );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta name="Description" content="Classic dotProject Style" />
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
	<script language="JavaScript">
	function doBtn() {
		var oEl = event.srcElement;
		var doit = event.type;
	
		while (-1 == oEl.className.indexOf( "Btn" )) {
			oEl = oEl.parentElement;
			if (!oEl) {
				return;
			}
		}
		if (doit == "mouseover" || doit == "mouseup") {
			oEl.className = "clsBtnOn";
		} else if (doit == "mousedown") {
			oEl.className = "clsBtnDown";
		} else {
			oEl.className = "clsBtnOff";
		}
	}
	function tboff(){
		var oEl = event.srcElement;
		var doit = event.type;
		oEl.className = "topBtnOff";
	}
	</script>
	<title><?php echo $AppUI->cfg['page_title'];?></title>
	<link rel="stylesheet" type="text/css" href="./style/<?php echo $uistyle;?>/main.css" />
</head>
<body class="mainpage" background="style/classic/images/bground.gif">
<table class="nav" width="100%" cellpadding="0" cellspacing="2">
<tr>
	<td nowrap width="33%"><?php echo $AppUI->cfg['company_name'];?></td>
<?php if (!$dialog) { ?>
	<td nowrap width="34%"><?php echo $AppUI->_('Current user').": $AppUI->user_first_name $AppUI->user_last_name"; ?></td>
	<td nowrap width="33%" align="right">
	<table cellpadding="1" cellspacing="1" width="150">
	<tr>
		<td class="topBtnOff" nowrap bgcolor="#cccccc" align="center"  onmouseover="doBtn();" onmouseout="tboff();" onmousedown="doBtn();" onmouseup="doBtn();"><a href="./index.php?m=admin&a=viewuser&user_id=<?php echo $AppUI->user_id;?>" onmouseover="doBtn();"><?php echo $AppUI->_('My Info');?></a></td>
		<td class="topBtnOff" nowrap bgcolor="#cccccc" align="center"  onmouseover="doBtn();" onmouseout="tboff();" onmousedown="doBtn();" onmouseup="doBtn();"><a href="./index.php?logout=-1" onmouseover="doBtn();"><?php echo $AppUI->_('Logout');?></a></td>
		<td class="topBtnOff" nowrap bgcolor="#cccccc" align="center"  onmouseover="doBtn();" onmouseout="tboff();" onmousedown="doBtn();" onmouseup="doBtn();"><a href="?m=help"><?php echo $AppUI->_( 'Help' );?></a></td>
	</tr>
	</table>
	</td>
	<form name="frm_new" method=GET action="./index.php">
<?php
	echo '<td>';
	$newItem = array( ""=>'- New Item -' );

	if ($AppUI->getProject()) {
		$newItem["tasks"] = "Task";
	} else if (!empty( $task_id ) && $task_id > 0) {
		$sql = "SELECT task_project FROM tasks WHERE task_id = $task_id";
		if ($rc = db_exec( $sql )) {
			if ($row = db_fetch_row( $rc )) {
				$AppUI->setProject( $row[0] );
				$newItem["tasks"] = "Task";
			}
		}
	}

	$newItem["projects"] = "Project";
	$newItem["companies"] = "Company";
	$newItem["files"] = "File";
	$newItem["contacts"] = "Contact";
	$newItem["calendar"] = "Event";

	echo arraySelect( $newItem, 'm', 'style="font-size:10px" onChange="f=document.frm_new;mod=f.m.options[f.m.selectedIndex].value;if(mod) f.submit();"', '', true);

	echo '</td><input type="hidden" name="a" value="addedit" />';

//build URI string
	if ($AppUI->getDaySelected()) {
		echo '<input type="hidden" name="uts" value="'.$AppUI->getDaySelected().'" />';
	}
	if (isset( $company_id )) {
		echo '<input type="hidden" name="company_id" value="'.$company_id.'" />';
	}
	if (isset( $task_id )) {
		echo '<input type="hidden" name="task_parent" value="'.$task_id.'" />';
	}
	if (isset( $file_id )) {
		echo '<input type="hidden" name="file_id" value="'.$file_id.'" />';
	}
?>
	</form>
<?php } // END DIALOG BLOCK ?>
</tr>
</table>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
	<td valign="top">
<?php if (!$dialog) { 
	// left side navigation menu
?>
		<table cellspacing=0 cellpadding=2 border=0 height="600">
		<tr>
			<td><img src="images/shim.gif" width="70" height="3"></td>
			<td rowspan="100"><img src="images/shim.gif" width="10" height="100"></td>
		</tr>
	<?php
		$nav = dPgetMenuModules();
		$s = '';
		foreach ($nav as $module) {
			if (!getDenyRead( $module['mod_directory'] )) {
				$s .= '<tr><td align="center" valign="middle" class="nav">'
					.'<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff">'
					.'<a href="?m='.$module['mod_directory'].'">'
					.'<img src="'.dPfindImage( $module['mod_ui_icon'], $module['mod_directory'] ).'" onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" alt="" border="0" width="30" height="30"></a></td></tr></table>'
					.$AppUI->_($module['mod_ui_name'])
					."</td></tr>\n";
			}
		}
		echo $s;
		?>
		<tr height="100%">
			<td>&nbsp;<img src="images/shim.gif" width="7" height="10"></td>
		</tr>
		</table>	
<?php } // END DIALOG ?>
	</td>
<td valign="top" align="left" width="100%">
<?php 
	echo $AppUI->getMsg();
?>