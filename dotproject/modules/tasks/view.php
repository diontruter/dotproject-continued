<?php /* $Id$ */
$task_id = intval( dPgetParam( $_GET, "task_id", 0 ) );

// check permissions for this record
$canRead = !getDenyRead( $m, $task_id );
$canEdit = !getDenyEdit( $m, $task_id );
// check permissions for this record
$canReadModule = !getDenyRead( $m );


if (!$canRead) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

$sql = "
SELECT tasks.*,
	project_name, project_color_identifier,
	u1.user_username as username,
	ROUND(SUM(task_log_hours),2) as log_hours_worked
FROM tasks
LEFT JOIN users u1 ON u1.user_id = task_owner
LEFT JOIN projects ON project_id = task_project
LEFT JOIN task_log ON task_log_task=$task_id
WHERE task_id = $task_id
GROUP BY task_id
";

// check if this record has dependancies to prevent deletion
$msg = '';
$obj = new CTask();
$canDelete = $obj->canDelete( $msg, $task_id );

//$obj = null;
if (!db_loadObject( $sql, $obj, true, false )) {
	$AppUI->setMsg( 'Task' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
} else {
	$AppUI->savePlace();
}

if (!$obj->canAccess( $AppUI->user_id )) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

// retrieve any state parameters
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'TaskLogVwTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'TaskLogVwTab' ) !== NULL ? $AppUI->getState( 'TaskLogVwTab' ) : 0;

// get the prefered date format
$df = $AppUI->getPref('SHDATEFORMAT');
//Also view the time
$df .= " " . $AppUI->getPref('TIMEFORMAT');

$start_date = intval( $obj->task_start_date ) ? new CDate( $obj->task_start_date ) : null;
$end_date = intval( $obj->task_end_date ) ? new CDate( $obj->task_end_date ) : null;

//check permissions for the associated project
$canReadProject = !getDenyRead( 'projects', $obj->task_project);

// get the users on this task
$sql = "
SELECT u.user_id, u.user_username, contact_email
FROM users u, user_tasks t
LEFT JOIN contacts ON user_contact = contact_id
WHERE t.task_id =$task_id AND
	t.user_id = u.user_id
ORDER by u.user_username
";
$users = db_loadList( $sql );

$durnTypes = dPgetSysVal( 'TaskDurationType' );

// setup the title block
$titleBlock = new CTitleBlock( 'View Task', 'applet-48.png', $m, "$m.$a" );
$titleBlock->addCell(
);
if ($canEdit) {
	$titleBlock->addCell(
		'<input type="submit" class="button" value="'.$AppUI->_('new task').'">', '',
		'<form action="?m=tasks&a=addedit&task_project='.$obj->task_project.'&task_parent=' . $task_id . '" method="post">', '</form>'
	);
	$titleBlock->addCell(
		'<input type="submit" class="button" value="'.$AppUI->_('new file').'">', '',
		'<form action="?m=files&a=addedit&project_id=' . $obj->task_project . '&file_task=' . $obj->task_id . '" method="post">', '</form>'
	);
}
$titleBlock->addCrumb( "?m=tasks", "tasks list" );
if ($canReadProject) {
	$titleBlock->addCrumb( "?m=projects&a=view&project_id=$obj->task_project", "view this project" );
}
if ($canEdit) {
	$titleBlock->addCrumb( "?m=tasks&a=addedit&task_id=$task_id", "edit this task" );
}
if ($canEdit) {
	$titleBlock->addCrumbDelete( 'delete task', $canDelete, $msg );
}
$titleBlock->show();

$task_types = dPgetSysVal("TaskType");

?>

<script language="JavaScript">
var calendarField = '';

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.editFrm.task_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate, 'calwin', 'width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.editFrm.task_' + calendarField );
	fld_fdate = eval( 'document.editFrm.' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;
}

<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canEdit) {
?>

function updateTask() {
	var f = document.editFrm;
	if (f.task_log_description.value.length < 1) {
		alert( "<?php echo $AppUI->_('tasksComment');?>" );
		f.task_log_description.focus();
	} else if (isNaN( parseInt( f.task_percent_complete.value+0 ) )) {
		alert( "<?php echo $AppUI->_('tasksPercent');?>" );
		f.task_percent_complete.focus();
	} else if(f.task_percent_complete.value  < 0 || f.task_percent_complete.value > 100) {
		alert( "<?php echo $AppUI->_('tasksPercentValue');?>" );
		f.task_percent_complete.focus();
	} else {
		f.submit();
	}
}
function delIt() {
	if (confirm( "<?php echo $AppUI->_('doDelete').' '.$AppUI->_('Task').'?';?>" )) {
		document.frmDelete.submit();
	}
}
<?php } ?>
</script>

<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">

<form name="frmDelete" action="./index.php?m=tasks" method="post">
	<input type="hidden" name="dosql" value="do_task_aed">
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="task_id" value="<?php echo $task_id;?>" />
</form>

<tr valign="top">
	<td width="50%">
		<table width="100%" cellspacing="1" cellpadding="2">
		<tr>
			<td nowrap="nowrap" colspan=2><strong><?php echo $AppUI->_('Details');?></strong></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project');?>:</td>
			<td style="background-color:#<?php echo $obj->project_color_identifier;?>">
				<font color="<?php echo bestColor( $obj->project_color_identifier ); ?>">
					<?php echo @$obj->project_name;?>
				</font>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Task');?>:</td>
			<td class="hilite"><strong><?php echo @$obj->task_name;?></strong></td>
		</tr>
		<?php if ( $obj->task_parent != $obj->task_id ) { 
			$obj_parent = new CTask();
			$obj_parent->load($obj->task_parent);
		?>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Task Parent');?>:</td>
			<td class="hilite"><a href="<?php echo "./index.php?m=tasks&a=view&task_id=" . @$obj_parent->task_id; ?>"><?php echo @$obj_parent->task_name;?></a></td>
		</tr>
		<?php } ?>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Creator');?>:</td>
			<td class="hilite"> <?php echo @$obj->username;?></td>
		</tr>				<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Priority');?>:</td>
			<td class="hilite">
		<?php
			if ($obj->task_priority == 0) {
				echo $AppUI->_('normal');
			} else if ($obj->task_priority < 0){
				echo $AppUI->_('low');
			} else {
				echo $AppUI->_('high');
			}
		?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Web Address');?>:</td>
			<td class="hilite" width="300"><a href="<?php echo @$obj->task_related_url;?>" target="task<?php echo $task_id;?>"><?php echo @$obj->task_related_url;?></a></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Milestone');?>:</td>
			<td class="hilite" width="300"><?php if($obj->task_milestone){echo $AppUI->_("Yes");}else{echo $AppUI->_("No");}?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Progress');?>:</td>
			<td class="hilite" width="300"><?php echo @$obj->task_percent_complete;?>%</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Time Worked');?>:</td>
			<td class="hilite" width="300"><?php echo (@$obj->task_hours_worked + @rtrim($obj->log_hours_worked, "0"));?></td>
		</tr>
		<tr>
			<td nowrap="nowrap" colspan=2><strong><?php echo $AppUI->_('Dates and Targets');?></strong></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Start Date');?>:</td>
			<td class="hilite" width="300"><?php echo $start_date ? $start_date->format( $df ) : '-';?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Finish Date');?>:</td>
			<td class="hilite" width="300"><?php echo $end_date ? $end_date->format( $df ) : '-';?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap" valign="top"><?php echo $AppUI->_('Expected Duration');?>:</td>
			<td class="hilite" width="300"><?php echo $obj->task_duration.' '.$AppUI->_( $durnTypes[$obj->task_duration_type] );?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Target Budget');?> <?php echo $dPconfig['currency_symbol'] ?>:</td>
			<td class="hilite" width="300"><?php echo $obj->task_target_budget;?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Task Type');?> :</td>
			<td class="hilite" width="300"><?php echo $task_types[$obj->task_type];?></td>
		</tr>

		</table>
	</td>

	<td width="50%">
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td colspan="3"><strong><?php echo $AppUI->_('Assigned Users');?></strong></td>
		</tr>
		<tr>
			<td colspan="3">
			<?php
				$s = '';
				$s = count( $users ) == 0 ? "<tr><td bgcolor=#ffffff>".$AppUI->_('none')."</td></tr>" : '';
				foreach($users as $row) {
					$s .= '<tr>';
					$s .= '<td class="hilite">'.dPgetUsername($row["user_username"]).'</td>';
					$s .= '<td class="hilite"><a href="mailto:'.$row["contact_email"].'">'.$row["contact_email"].'</a></td>';
					$s .= '</tr>';
				}
				echo '<table width="100%" cellspacing=1 bgcolor="black">'.$s.'</table>';
			?>
			</td>
		</tr>

		<?php
			// Pull tasks dependencies
			$sql = "
			SELECT t.task_id, t.task_name
			FROM tasks t, task_dependencies td
			WHERE td.dependencies_task_id = $task_id
			AND t.task_id = td.dependencies_req_task_id
			";
			$taskDep = db_loadHashList( $sql );
		?>
		<tr>
			<td colspan="3"><strong><?php echo $AppUI->_('Dependencies');?></strong></td>
		</tr>
		<tr>
			<td colspan="3">
			<?php 
				$s = count( $taskDep ) == 0 ? "<tr><td bgcolor=#ffffff>".$AppUI->_('none')."</td></tr>" : '';
				foreach($taskDep as $key => $value) {
					$s .= '<tr><td class="hilite">';
					$s .= '<a href="./index.php?m=tasks&a=view&task_id='.$key.'">'.$value.'</a>';
					$s .= '</td></tr>';
				}
				echo '<table width="100%" cellspacing=1 bgcolor="black">'.$s.'</table>';
			?>
			</td>
		</tr>
                <?php
			// Pull the tasks depending on this Task
			$sql = "
			SELECT t.task_id, t.task_name
			FROM tasks t, task_dependencies td
			WHERE td.dependencies_req_task_id = $task_id
			AND t.task_id = td.dependencies_task_id
			";
			$dependingTasks = db_loadHashList( $sql );
		?>
		<tr>
			<td colspan="3"><strong><?php echo $AppUI->_('Tasks depending on this Task');?></strong></td>
		</tr>
		<tr>
			<td colspan="3">
			<?php
				$s = count( $dependingTasks ) == 0 ? "<tr><td bgcolor=#ffffff>".$AppUI->_('none')."</td></tr>" : '';
				foreach($dependingTasks as $key => $value) {
					$s .= '<tr><td class="hilite">';
					$s .= '<a href="./index.php?m=tasks&a=view&task_id='.$key.'">'.$value.'</a>';
					$s .= '</td></tr>';
				}
				echo '<table width="100%" cellspacing=1 bgcolor="black">'.$s.'</table>';
			?>
			</td>
		</tr>
		<tr>
		  <td colspan='3' nowrap="nowrap">
		     <strong><?php echo $AppUI->_('Description');?></strong><br />
		  </td>
		 </tr>
		 <tr>
		  <td class='hilite' colspan='3'>
				<?php $newstr = str_replace( chr(10), "<br />", $obj->task_description);echo $newstr;?>
		  </td>
		</tr>
<?php
		if($obj->task_departments != "") {
			?>
		    <tr>
		    	<td><strong><?php echo $AppUI->_("Departments"); ?></strong></td>
		    </tr>
		    <tr>
		    	<td colspan='3' class="hilite">
		    		<?php
		    			$depts = db_loadHashList("select dept_id, dept_name, dept_phone
		    			                          from departments
		    			                          where dept_id in (".$obj->task_departments.")", "dept_id");
		    			foreach($depts as $dept_id => $dept_info){
		    				echo "<div>".$dept_info["dept_name"];
		    				if($dept_info["dept_phone"] != ""){
		    					echo "( ".$dept_info["dept_phone"]." )";
		    				}
		    				echo "</div>";
		    			}
		    		?>
		    	</td>
		    </tr>
	 		<?php
		}
		
		if($obj->task_contacts != "") {
			$contacts = db_loadHashList("select contact_id, contact_first_name, contact_last_name, contact_email, contact_phone, contact_department
		    			                 from contacts
		    			                 where contact_id in (".$obj->task_contacts.")
		    			                       and (contact_owner = '$AppUI->user_id' or contact_private='0')", "contact_id");
			if(count($contacts)>0){
				?>
			    <tr>
			    	<td><strong><?php echo $AppUI->_("Contacts"); ?></strong></td>
			    </tr>
			    <tr>
			    	<td colspan='3' class="hilite">
			    		<?php
			    			echo "<table cellspacing='1' cellpadding='2' border='0' width='100%' bgcolor='black'>";
			    			echo "<tr><th>".$AppUI->_("Name")."</th><th>".$AppUI->_("Email")."</th><th>".$AppUI->_("Phone")."</th><th>".$AppUI->_("Department")."</th></tr>";
			    			foreach($contacts as $contact_id => $contact_data){
			    				echo "<tr>";
			    				echo "<td class='hilite'><a href='index.php?m=contacts&a=addedit&contact_id=$contact_id'>".$contact_data["contact_first_name"]." ".$contact_data["contact_last_name"]."</a></td>";
			    				echo "<td class='hilite'><a href='mailto: ".$contact_data["contact_email"]."'>".$contact_data["contact_email"]."</a></td>";
			    				echo "<td class='hilite'>".$contact_data["contact_phone"]."</td>";
			    				echo "<td class='hilite'>".$contact_data["contact_department"]."</td>";
			    				echo "</tr>";
			    			}
			    			echo "</table>";
			    		?>
			    	</td>
			    </tr>
			    <tr>
			    	<td>
		 <?php
			}
		}
		error_reporting(E_ALL);
		require_once("./classes/customfieldsparser.class.php");
		$cfp = new CustomFieldsParser("TaskCustomFields", $obj->task_id);

		$record_type = isset($cfp->custom_record_types[$obj->task_type]) ? $cfp->custom_record_types[$obj->task_type] : null;
		echo $cfp->parseTableForm(false, $record_type);
	 ?>
	 		</td>
	 	</tr>
		</table>
	</td>
</tr>
</table>

<?php
$query_string = "?m=tasks&a=view&task_id=$task_id";
$tabBox = new CTabBox( "?m=tasks&a=view&task_id=$task_id", "", $tab );

$tabBox_show = 0;
if ( $obj->task_dynamic != 1 ) {
	// tabbed information boxes
	$tabBox->add( "{$dPconfig['root_dir']}/modules/tasks/vw_logs", 'Task Logs' );
	// fixed bug that dP automatically jumped to access denied if user does not
	// have read-write permissions on task_id and this tab is opened by default (session_vars)
	// only if user has r-w perms on this task, new or edit log is beign showed
	if (!getDenyEdit( $m, $task_id )) {
		$tabBox->add( "{$dPconfig['root_dir']}/modules/tasks/vw_log_update", 'New Log' );
	}
	$tabBox_show = 1;
}

if ( count($obj->getChildren()) > 0 ) {
	// Has children
	// settings for tasks
	$f = 'children';
	$min_view = true;
	$tabBox_show = 1;
	// in the tasks file there is an if that checks
	// $_GET[task_status]; this patch is to be able to see
	// child tasks withing an inactive task
	$_GET["task_status"] = $obj->task_status;
	$tabBox->add( "{$dPconfig['root_dir']}/modules/tasks/tasks", 'Child Tasks' );
}
foreach($all_tabs as $name => $tab)
        $tabBox->add($tab, $name);
if ( $tabBox_show == 1)	$tabBox->show();
?>
