<?php /* $Id$ */
$task_id = isset( $_GET['task_id'] ) ? $_GET['task_id'] : 0;

// check permissions
$denyRead = getDenyRead( $m );
$denyEdit = getDenyEdit( $m );

if ($denyRead) {
	$AppUI->redirect( "m=help&a=access_denied" );
}

$sql = "
SELECT tasks.*,
	project_name, project_color_identifier,
	u1.user_username as username
FROM tasks
LEFT JOIN users u1 ON u1.user_id = task_owner
LEFT JOIN projects ON project_id = task_project
WHERE task_id = $task_id
";
if (!db_loadHash( $sql, $task )) {
	// if a task has been deleted, then go the the previous page
	if ($task_id) {
		$AppUI->redirect( '', -1 );
	}
}
$AppUI->savePlace();

$AppUI->setState( 'ActiveProject', $task['task_project'] );

// Pull the task comments
$sql = "
SELECT user_username,
	comment_title, comment_body, comment_date
FROM tasks, task_comments
LEFT JOIN users ON users.user_id = task_comments.comment_user
WHERE task_id = $task_id
	AND comment_task = task_id
ORDER BY comment_date
";
$comments = db_loadList( $sql );

//Pull users on this task
$sql = "
SELECT u.user_id, u.user_username, u.user_first_name,u.user_last_name, u.user_email
FROM users u, user_tasks t
WHERE t.task_id =$task_id AND
	t.user_id = u.user_id
";
$users = db_loadList( $sql );

//Pull files on this task
$sql = "
SELECT file_id, file_name, file_size,file_type
FROM files
WHERE file_task = $task_id
	AND file_task <> 0
";
$files = db_loadList( $sql );

$crumbs = array();
$crumbs["?m=projects&a=view&project_id={$task['task_project']}"] = "view this project";
$crumbs["?m=tasks"] = "tasks list";
if (!$denyEdit) {
	$crumbs["?m=tasks&a=addedit&task_id={$task['task_id']}"] = "edit this task";
}
?>

<script language="JavaScript">
function updateTask() {
	var form = document.update;
	if (form.comments.value.length < 1) {
		alert( "<?php echo $AppUI->_('tasksComment');?>" );
		form.comments.focus();
	} else if (isNaN( parseInt( form.complete.value+0 ) )) {
		alert( "<?php echo $AppUI->_('tasksPercent');?>" );
		form.complete.focus();
	} else if(form.complete.value  < 0 || form.complete.value > 100) {
		alert( "<?php echo $AppUI->_('tasksPercentValue');?>" );
		form.complete.focus();
	} else {
		form.submit();
	}
}
</script>

<table cellspacing="1" cellpadding="1" border="0" width="98%">
<tr>
	<td><img src="./images/icons/tasks.gif" alt="" border="0"></td>
	<td nowrap="nowrap"><span class="title"><?php echo $AppUI->_('View Task');?></span></td>
	<td nowrap="nowrap"><img src="./images/shim.gif" width="16" height="16" alt="" border="0"></td>
	<td valign="top" align="right" width="100%"></td>
	<td nowrap="nowrap" width="20" align="right"><?php echo contextHelp( '<img src="./images/obj/help.gif" width="14" height="16" border="0" alt="'.$AppUI->_( 'Help' ).'">', 'ID_HELP_TASK_VIEW' );?></td>
</tr>
</table>

<table cellspacing="0" cellpadding="4" border="0" width="98%">
<tr>
	<td width="50%" nowrap="nowrap"><?php echo breadCrumbs( $crumbs );?></td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="98%" class="std">
<tr>
	<td style="border: outset #eeeeee 1px;background-color:<?php echo $task["project_color_identifier"];?>" colspan="2">
		<font color="<?php echo bestColor( $task["project_color_identifier"] ); ?>">
			<b><?php echo $AppUI->_('Task');?>: <?php echo @$task["task_name"];?></b>
		</font>
	</td>
</tr>
<tr valign="top">
	<td width="50%">
		<table width="100%" cellspacing="1" cellpadding="2">
		<tr>
			<td nowrap="nowrap" colspan=2><b><?php echo $AppUI->_('Details');?></b></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project');?>:</td>
			<td class="hilite"><?php echo @$task["project_name"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Task');?>:</td>
			<td class="hilite"><?php echo @$task["task_name"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Creator');?>:</td>
			<td class="hilite"> <?php echo @$task["username"];?></td>
		</tr>				<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Priority');?>:</td>
			<td class="hilite">
		<?php
			if ($task["task_priority"] == 0) {
				echo $AppUI->_('normal');
			} else if ($task["task_priority"] < 0){
				echo $AppUI->_('low');
			} else {
				echo $AppUI->_('high');
			}
		?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Web Address');?>:</td>
			<td class="hilite" width="300"><?php echo @$task["task_related_url"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Milestone');?>:</td>
			<td class="hilite" width="300"><?php if($task["task_milestone"]){echo "Yes";}else{echo "No";}?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Progress');?>:</td>
			<td class="hilite" width="300"><?php echo @$task["task_precent_complete"];?>%</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Time Worked');?>:</td>
			<td class="hilite" width="300"><?php echo @$task["task_hours_worked"];?></td>
		</tr>
		<tr>
			<td nowrap="nowrap" colspan=2><b><?php echo $AppUI->_('Dates and Targets');?></b></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Start Date');?>:</td>
			<td class="hilite" width="300"><?php echo fromDate(substr($task["task_start_date"], 0, 10));?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Finish Date');?>:</td>
			<td class="hilite" width="300"><?php if(intval($task["task_end_date"]) == 0){echo "n/a";}else{echo fromDate(substr($task["task_end_date"], 0, 10));}?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Expected Duration');?>:</td>
			<td class="hilite" width="300"><?php
			$dur = returnDur( $task["task_duration"] );
			echo $dur["value"] . " " . $dur["type"];
			?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Target Budget');?>:</td>
			<td class="hilite" width="300"><?php echo $task["task_target_budget"];?></td>
		</tr>
		<tr>
			<td nowrap="nowrap" colspan="2"><b><?php echo $AppUI->_('Description');?></b></td>
		</tr>
		<tr>
			<td valign="top" height="75" colspan="2" class="hilite">
				<?php $newstr = str_replace( chr(10), "<BR>", $task["task_description"]);echo $newstr;?>
			</td>
		</tr>

		</table>
	</td>

	<td width="50%">
		<b><?php echo $AppUI->_('Work');?></b>:
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<form name="update" action="?m=tasks&a=view&task_id=<?php echo $task_id;?>" method="post">
		<input type="hidden" value="<?php echo uniqid("");?>" name="uniqueid">
		<input type="hidden" value="updatetask" name="dosql">
		<input type="hidden" value="<?php echo @$task["task_id"];?>" name="task_id">
		<input type="hidden" value="<?php echo $AppUI->user_id;?>" name="user_id">
		<input type="hidden" value="Update :<?php echo $$task["task_name"];?>" name="comment_title">
		<input type="hidden" value="<?php echo @$task["task_hours_worked"];?>" name="already_worked">
		<tr>
			<td rowspan="2" valign="bottom" align="left" bgcolor="#e0e0e0">
				<input type="button" class="button" value="<?php echo $AppUI->_('update task');?>" onclick="updateTask()">
				<br /><br />
				<?php echo $AppUI->_('Comments');?>:
			</td>
			<td align="right" bgcolor="#e0e0e0">
				<?php echo $AppUI->_('hoursWorked');?>
			</td>
			<td bgcolor="#e0e0e0">
				<input type="text" name="worked" maxlength="3" size="4">
			</td>
		</tr>
		<tr>
			<td bgcolor="#e0e0e0" align="right"><?php echo $AppUI->_('Progress');?></td>
			<td bgcolor="#e0e0e0">
		<?php
			echo arraySelect( $percent, 'complete', 'size=1', $task["task_precent_complete"] ) . '%';
		?>
			</td>
		</tr>
		<tr>
			<td colspan="3" bgcolor="#e0e0e0">
				<textarea name="comments" class="textarea" cols="50" rows="4"></textarea>
			</td>
		</tr>
		</form>

		<tr>
			<td colspan="3"><b><?php echo $AppUI->_('Assigned Users');?></b></td>
		</tr>
		<tr>
			<td colspan="3">
				<table width="100%" cellspacing=1 bgcolor="black">
				<?php foreach($users as $row){?>
				<tr><td class="hilite"><?php echo $row["user_username"];?></td><td class="hilite"><?php echo $row["user_email"];?></td></tr>
				<?php };?>
				</table>
			</td>
		</tr>
		<tr>
			<td><b><?php echo $AppUI->_('Attached Files');?></b></td>
			<td colspan="2" align="right">
				<A href="./index.php?m=files&a=addedit&project_id=<?php echo $task["task_project"];?>&file_task=<?php echo $task_id;?>"><?php echo $AppUI->_('Attach a file');?><img src="./images/icons/forum_folder.gif" align=absmiddle width=20 height=20 alt="attach a file to this task" border=0></a>
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<table width="100%" cellspacing=1 bgcolor="black">
					<?php if(count($files)==0)echo "<tr><td bgcolor=#ffffff>none</td></tr>";
					foreach ($files as $row){?>
					<tr><td bgcolor="#eeeeee"><A href="./fileviewer.php?file_id=<?php echo $row["file_id"];?>"><?php echo $row["file_name"];?></a></td><td class="hilite"><?php echo $row["file_type"];?></td><td bgcolor="#eeeeee"><?php echo $row["file_size"];?></td></tr>
					<?php };?>
				</table>
			</td>
		</tr>
		</table>
	</td>
</tr>
</table>

<B><?php echo $AppUI->_('Task Log and Comments');?></b>

<table border="0" cellpadding="2" cellspacing="1" width="98%" class="tbl">
<tr>
	<td></td>
</tr>
<tr>
	<th width="100"><?php echo $AppUI->_('Action');?></th>
	<th width="100"><?php echo $AppUI->_('User');?></th>
	<th><?php echo $AppUI->_('Comments');?></th>
	<th width="150"><?php echo $AppUI->_('Date');?></th>
</tr>
<?php foreach ($comments as $row) { ?>
<tr bgcolor="white" valign=top>
	<td width="100"><?php echo $row["comment_title"];?></td>
	<td width="100"><?php echo $row["user_username"];?></td>
	<td><?php $newstr = str_replace(chr(10), "<BR>",$row["comment_body"]);echo $newstr;?></td>
	<td width="150"><?php echo fromDate($row["comment_date"]);?></td>
</tr>
	<?php }?>
</table>

</body>
</html>

