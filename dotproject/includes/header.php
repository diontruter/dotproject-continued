<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
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
<title><?php echo $page_title;?></title>
<link rel="stylesheet" type="text/css" href="./style/main.css">
</head>
<body bgcolor="#ffffff" topmargin="0" leftmargin="0" marginheight=0 marginwidth=0 background="images/bground.gif">
<table width="100%" cellpadding=3 cellspacing=0 bgcolor="#cccccc" style="border: outset #eeeeee 2px;">
<tr>
	<td nowrap width="33%">
		<span id="smallCompanyTitle"><?php echo $company_name;?></SPAN>
	</td>
	<td nowrap width="34%">
		<span id="smallCompanyTitle">Current user: <?php echo "$AppUI->user_first_name $AppUI->user_last_name"; ?></SPAN>
	</td>
	<td nowrap width="33%" align="right">
		<table cellpadding=1 cellspacing=1 width="200">
		<tr>
			<td class="topBtnOff" nowrap bgcolor="#cccccc" align="center"  onmouseover="doBtn();" onmouseout="tboff();" onmousedown="doBtn();" onmouseup="doBtn();"><a href="./index.php?m=admin&a=viewuser&user_id=<?php echo $AppUI->user_id;?>" onmouseover="doBtn();">My Info</a></td>
			<td class="topBtnOff" nowrap bgcolor="#cccccc" align="center"  onmouseover="doBtn();" onmouseout="tboff();" onmousedown="doBtn();" onmouseup="doBtn();"><a href="./index.php?logout=-1" onmouseover="doBtn();">Logout</a></td>
			<td class="topBtnOff" nowrap bgcolor="#cccccc" align="center"  onmouseover="doBtn();" onmouseout="tboff();" onmousedown="doBtn();" onmouseup="doBtn();">Help</td>
			<td class="topBtnOff" nowrap bgcolor="#cccccc" align="center"  onmouseover="doBtn();" onmouseout="tboff();" onmousedown="doBtn();" onmouseup="doBtn();"><a href="./index.php?m=help&a=about" onmouseover="doBtn();">About</a></td>
		</tr>
		</table>
	</td>
	<form name="frm_new" method=GET action="./index.php">
	<td>
		<select name="m" style="font-size:10px" onChange="f=document.frm_new;mod=f.m.options[f.m.selectedIndex].value;if(mod) f.submit();">
			<option value="">- New Item -
<?php
	if (!empty( $project_id ) && $project_id > 0) {
		echo '<option value="tasks">Task';
	} else if (!empty( $task_id ) && $task_id > 0) {
		$sql = "SELECT task_project FROM tasks WHERE task_id = $task_id";
		if ($rc = mysql_query( $sql )) {
			if ($row = mysql_fetch_row( $rc )) {
				$project_id = $row[0];
				echo '<option value="tasks">Task';
			}
		}
	}
?>
			<option value="projects">Project
			<option value="companies">Company
			<option value="files">File
			<option value="contacts">Contact
			<option value="calendar">Event
		</select>
	</td>
	<input type="hidden" name="a" value="addedit">

<?php //build URI string
	if (isset( $project_id )) {
		echo '<input type="hidden" name="project_id" value="'.$project_id.'">';
	}
	if (isset( $company_id )) {
		echo '<input type="hidden" name="company_id" value="'.$company_id.'">';
	}
	if (isset( $task_id )) {
		echo '<input type="hidden" name="task_parent" value="'.$task_id.'">';
	}
	if (isset( $file_id )) {
		echo '<input type="hidden" name="file_id" value="'.$file_id.'">';
	}
?>
	</form>
</tr>
</table>
<table width="100%" cellpadding=0 cellspacing=0 border=0>
<tr>
<td valign="top"><?php require "./includes/leftnav.php";?></td>
<td valign="top" align="left" width="100%">
<?php 
	echo $AppUI->getMsg();
// legacy support
	echo @$message;
?>
<!-- <img src="images/shim.gif" width="1" height="5" alt="" border="0"><br> -->