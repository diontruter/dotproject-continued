<?php

// check permissions
if (!$canEdit) {
    $AppUI->redirect( "m=public&a=access_denied" );
}

$do_report 		= dPgetParam( $_POST, "do_report", true );
$log_start_date 	= dPgetParam( $_POST, "log_start_date", 0 );
$log_end_date 	        = dPgetParam( $_POST, "log_end_date", 0 );
$log_all		= dPgetParam($_POST,"log_all", true);
$log_all_projects       = dPgetParam($_POST,"log_all_projects", true);
$use_period		= dPgetParam($_POST,"use_period",0);
$display_week_hours	= dPgetParam($_POST,"display_week_hours",0);
$max_levels        	= dPgetParam($_POST,"max_levels","max");
$log_userfilter		= dPgetParam($_POST,"log_userfilter","");

$durnTypes = dPgetSysVal( 'TaskDurationType' );

$table_header = "";
$table_rows="";

// create Date objects from the datetime fields
$start_date = intval( $log_start_date ) ? new CDate( $log_start_date ) : new CDate();
$end_date   = intval( $log_end_date )   ? new CDate( $log_end_date ) : new CDate();

if (!$log_start_date) {
	$start_date->subtractSpan( new Date_Span( "14,0,0,0" ) );
}
$end_date->setTime( 23, 59, 59 );
?>

<script language="javascript">
var calendarField = '';

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.editFrm.log_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.editFrm.log_' + calendarField );
	fld_fdate = eval( 'document.editFrm.' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;
}

function checkAll(user_id) {
        var f = eval( 'document.assFrm' + user_id );
        var cFlag = f.master.checked ? false : true;

        for (var i=0;i< f.elements.length;i++){
                var e = f.elements[i];
                // only if it's a checkbox.
                if(e.type == "checkbox" && e.checked == cFlag && e.name != 'master')
                {
                         e.checked = !e.checked;
                }
        }

}


function chAssignment(user_id, rmUser, del) {
        var f = eval( 'document.assFrm' + user_id );
        var fl = f.add_users.length-1;
        var c = 0;
        var a = 0;

        f.hassign.value = "";
        f.htasks.value = "";

        // harvest all checked checkboxes (tasks to process)
        for (var i=0;i< f.elements.length;i++){
                var e = f.elements[i];
                // only if it's a checkbox.
                if(e.type == "checkbox" && e.checked == true && e.name != 'master')
                {
                         c++;
                         f.htasks.value = f.htasks.value +","+ e.value;
                }
        }

        // harvest all selected possible User Assignees
        for (fl; fl > -1; fl--){
                if (f.add_users.options[fl].selected) {
                        a++;
                        f.hassign.value = "," + f.hassign.value +","+ f.add_users.options[fl].value;
                }
        }

        if (del == true) {
                        if (c == 0) {
                                 alert ('<?php echo $AppUI->_('Please select at least one Task!'); ?>');
                        } else {
                                if (confirm( '<?php echo $AppUI->_('Are you sure you want to unassign the User from Task(s)?'); ?>' )) {
                                        f.del.value = 1;
                                        f.rm.value = 0;
                                        f.user_id.value = user_id;
                                        f.submit();
                                }
                        }
        } else {

                if (c == 0) {
                        alert ('<?php echo $AppUI->_('Please select at least one Task!'); ?>');
                } else {

                        if (a == 0) {
                                alert ('<?php echo $AppUI->_('Please select at least one Assignee!'); ?>');
                        } else {
                                f.rm.value = rmUser;
                                f.del.value = 0;
                                f.user_id.value = user_id;
                                f.submit();

                        }
                }
        }


}

function chPriority(user_id) {
        var f = eval( 'document.assFrm' + user_id );
        var c = 0;

        f.htasks.value = "";

        // harvest all checked checkboxes (tasks to process)
        for (var i=0;i< f.elements.length;i++){
                var e = f.elements[i];
                // only if it's a checkbox.
                if(e.type == "checkbox" && e.checked == true && e.name != 'master')
                {
                         c++;
                         f.htasks.value = f.htasks.value +","+ e.value;
                }
        }

        if (c == 0) {
                alert ('<?php echo $AppUI->_('Please select at least one Task!'); ?>');
        } else {
                f.rm.value = 0;
                f.del.value = 0;
                f.store.value = 1;
                f.user_id.value = user_id;
                f.submit();
        }
}
</script>
<form name="editFrm" action="index.php?m=tasks&a=tasksperuser" method="post">
<input type="hidden" name="project_id" value="<?php echo $project_id;?>" />
<input type="hidden" name="report_type" value="<?php echo $report_type;?>" />

<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">

<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_('For period');?>:</td>
	<td nowrap="nowrap">
		<input type="hidden" name="log_start_date" value="<?php echo $start_date->format( FMT_TIMESTAMP_DATE );?>" />
		<input type="text" name="start_date" value="<?php echo $start_date->format( $df );?>" class="text" disabled="disabled" />
		<a href="#" onClick="popCalendar('start_date')">
			<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
		</a>
	</td>
	<td nowrap="nowrap">
	<?php
		  $usersql = "
		  SELECT user_id, concat(user_first_name,' ',user_last_name) as name
		  FROM users
		  ORDER by user_last_name,user_first_name
		  ";
//echo "<pre>$usersql</pre>";
		$system_users = arrayMerge( array( 0 => $AppUI->_('All Users') ), db_loadHashList( $usersql ) );
	?>
	<?=arraySelect( $system_users, 'log_userfilter', 'class="text" STYLE="width: 200px"',$company_id )?>


	</td>

	<td nowrap="nowrap">
                <!-- // not in use anymore <input type="checkbox" name="log_all_projects" <?php if ($log_all_projects) echo "checked" ?> >
		<?php echo $AppUI->_( 'Log All Projects' );?>
		</input>
		<br> -->
		<input type="checkbox" name="display_week_hours" <?php if ($display_week_hours) echo "checked" ?> >
		<?php echo $AppUI->_( 'Display allocated hours/week' );?>
		</input><br />
                <input type="checkbox" name="use_period" <?php if ($use_period) echo "checked" ?> >
		<?php echo $AppUI->_( 'Use the period' );?>
		</input>


	</td>

	<td align="left" width="50%" nowrap="nowrap">
		<input class="button" type="submit" name="do_report" value="<?php echo $AppUI->_('submit');?>" />
	</td>
</tr>
<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_('to');?>:</td>
	<td>
		<input type="hidden" name="log_end_date" value="<?php echo $end_date ? $end_date->format( FMT_TIMESTAMP_DATE ) : '';?>" />
		<input type="text" name="end_date" value="<?php echo $end_date ? $end_date->format( $df ) : '';?>" class="text" disabled="disabled" />
		<a href="#" onClick="popCalendar('end_date')">
			<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
		</a>
	</td>
        <td>
                <?php echo $AppUI->_( 'Levels to display' ); ?>
		<input type="text" name="max_levels" size="10" maxlength="3" value="<?php echo $max_levels; ?>" />

	</td>
        <td></td>

</tr>

</table>
</form>
<?php
if($do_report){
	// Let's figure out which users we have
	$sql = "SELECT  u.user_id,
	 				u.user_username,
					u.user_first_name,
					u.user_last_name
	        FROM users AS u";

	if ($log_userfilter!=0) {
			$sql.=" WHERE user_id=".
						  $log_userfilter
					      ;//$log_userfilter_users[$log_userfilter]["user_id"];
	}
	$sql.=" ORDER by user_last_name, user_first_name";

//echo "<pre>$sql</pre>";
	$user_list = db_loadHashList($sql, "user_id");

	$ss="'".$start_date->format( FMT_DATETIME_MYSQL )."'";
	$se="'".$end_date->format( FMT_DATETIME_MYSQL )."'";

	$and=false;
	$where=false;

	$sql = 	 "SELECT t.* "
			."FROM tasks AS t ";

	if ($use_period) {
		if (!$where) { $sql.=" WHERE ";$where=true; }
		$sql.=" ( "
			."  ( task_start_date >= $ss AND task_start_date <= $se ) "
			." OR "
			."  ( task_end_date <= $se AND task_end_date >= $ss ) "
			." ) ";
		$and=true;
	}

	if ($and) {
        	$sql .= " AND ";
      	}

    	if (!$where) { $sql.=" WHERE ";$where=true; }
     	$sql .= " (task_percent_complete < 100)";
     	$and=true;
	
	        //AND !isnull(task_end_date) AND task_end_date != '0000-00-00 00:00:00'
	        //AND !isnull(task_start_date) AND task_start_date != '0000-00-00 00:00:00';
	        //AND task_dynamic   ='0'
	        //AND task_milestone = '0'
	        //AND task_duration  > 0";
			//;

	if(!$log_all_projects){
		if (!$where) { $sql.=" WHERE ";$where=true; }
		if ($and) {
			$sql .= " AND ";
		}
		$sql.=" task_project='$project_id' ";
	}

	$sql .= " ORDER BY task_end_date;";

//echo "<pre>$sql</pre>";
	$task_list_hash 	 = db_loadHashList($sql, "task_id");
	$task_list      	 = array();
	$task_assigned_users = array();
	$i = 0;
	foreach($task_list_hash as $task_id => $task_data){
		$task = new CTask();
		$task->bind($task_data);
		$task_list[$i] = $task;
		$task_assigned_users[$i] = $task->getAssignedUsers();
		$i+=1;
	}
	$Ntasks=$i;

	//for($i=0;$i<$Ntasks;$i++) {
		//print $task_list[$i]->task_name."<br>\n";
	//}

	$user_usage            = array();
	$task_dates            = array();

	$actual_date = $start_date;
	$days_header = ""; // we will save days title here

	if (strtolower($max_levels)=="max") {
		$max_levels=-1;
	}
	elseif ($max_levels=="") {
		$max_levels=-1;
	}
	else {
		$max_levels=atoi($max_levels);
	}
	if ($max_levels==0) { $max_levels=1; }
	if ($max_levels<0) { $max_levels=-1; }

	if ( count($task_list) == 0 ) {
		echo "<p>" . $AppUI->_( 'No data available' ) ."</p>";
	} else {

		$sss=$ss;$sse=$se;
		if (!$use_period) {	$sss=-1; $sse=-1; }
		if ($display_week_hours and !$use_period) {
			foreach($task_list as $t) {
				if ($sss==-1) {
					$sss=$t->task_start_date;
					$sse=$t->task_end_date;
				}
				else {
					if ($t->task_start_date<$sss) { $sss=$t->task_start_date; }
					if ($t->task_end_date>$sse) { $sse=$t->task_end_date; }
				}
			}
		}

		$table_header = "<tr>".
                                                "<th nowrap=\"nowrap\" ></th>".
                                                "<th nowrap=\"nowrap\" >".$AppUI->_("P")."</th>".
                                                "<th nowrap=\"nowrap\" >".$AppUI->_("Task")."</th>".
                                                "<th nowrap=\"nowrap\" >".$AppUI->_("Proj.")."</th>".
                                                "<th nowrap=\"nowrap\">".$AppUI->_("Duration")."</th>".
						"<th nowrap=\"nowrap\" >".$AppUI->_("Start Date")."</th>".
						"<th nowrap=\"nowrap\" >".$AppUI->_("End[d]")."</th>".
                                                weekDates($display_week_hours,$sss,$sse).
                                                "<th nowrap=\"nowrap\" >".$AppUI->_("Current Assignees")."</th>".
                                                "<th nowrap=\"nowrap\" >".$AppUI->_("Possible Assignees")."</th>".
						"</tr>";
		$table_rows = "";

		foreach($user_list as $user_id => $user_data){

                        // count tasks per user;
                        $z=0;
                        foreach($task_list as $task) {
		                if (isMemberOfTask($task_list,$task_assigned_users,$Ntasks,$user_id,$task)) { $z++; }
			}

			$tmpuser= "<form name=\"assFrm$user_id\" action=\"index.php?m=tasks&a=tasksperuser\" method=\"post\">
                                <input type=\"hidden\" name=\"del\" value=\"1\" />
                                <input type=\"hidden\" name=\"rm\" value=\"0\" />
                                <input type=\"hidden\" name=\"store\" value=\"0\" />
                                <input type=\"hidden\" name=\"dosql\" value=\"do_task_assign_aed\" />
                                <input type=\"hidden\" name=\"user_id\" value=\"$user_id\" />
                                <input type=\"hidden\" name=\"hassign\" />
                                <input type=\"hidden\" name=\"htasks\" />
                                <tr>
                                <td bgcolor='#D0D0D0'><input onclick=\"javascript:checkAll($user_id);\" type=\"checkbox\" name=\"master\" value=\"true\"/></td>
                                <td colspan='2' align='left' nowrap='nowrap' bgcolor='#D0D0D0'>
                                <font color='black'>
                                <B><a href='index.php?m=calendar&a=day_view&user_id=$user_id&tab=1'>"
					  .$user_data["user_first_name"]
				      ." "
					  .$user_data["user_last_name"]
					  ."</a></B></font></td>";
		    for($w=0;$w<=(4+weekCells($display_week_hours,$sss,$sse));$w++) {
				 $tmpuser.="<td bgcolor='#D0D0D0'></td>";
			}

                        $tmpuser .="<td bgcolor=\"#D0D0D0\"><table width=\"100%\"><tr>";
                        $tmpuser .="<td align=\"left\">
                        <a href='javascript:chAssignment($user_id, 0, true);'>".
                        dPshowImage(dPfindImage('remove.png', 'tasks'), 16, 16, 'Unassign User', 'Unassign User from Task')."</a>&nbsp;".
                        "<a href='javascript:chAssignment($user_id, 1, false);'>".
                        dPshowImage(dPfindImage('exchange.png', 'tasks'), 24, 16, 'Hand Over', 'Unassign User from Task and handing-over to selected Users')."</a>&nbsp;".
                        "<a href='javascript:chAssignment($user_id, 0, false);'>".
                        dPshowImage(dPfindImage('add.png', 'tasks'), 16, 16, 'Assign Users', 'Assign selected Users to selected Tasks')."</a></td>";
                        $tmpuser .= "<td align=\"center\"><select class=\"text\" name=\"percentage_assignment\" title=\"".$AppUI->_('Assign with Percentage')."\">";
                        for ($i = 5; $i <= 100; $i+=5) {
                                        $tmpuser .= "<option ".(($i==100)? "selected=\"true\"" : "" )." value=\"".$i."\">".$i."%</option>";
                        }
                        $tmpuser .= "</select></td>";
                        $tmpuser .= "<td align=\"center\">".arraySelect( $priority, 'task_priority', 'onchange="javascript:chPriority('.$user_id.');" size="1" class="text" title="'.$AppUI->_('Change Priority of selected Tasks').'"', 0, true );
                        $tmpuser .= "</td></tr></table></td>";

			$tmpuser.="</tr>";

			$tmptasks="";
			$actual_date = $start_date;

                        $zi=0;
			foreach($task_list as $task) {
				if (!isChildTask($task)) {
					if (isMemberOfTask($task_list,$task_assigned_users,$Ntasks,$user_id,$task)) {
						$tmptasks.=displayTask($task_list,$task,0,$display_week_hours,$sss,$sse, $user_id);
						// Get children
						$tmptasks.=doChildren($task_list,$task_assigned_users,$Ntasks,
											  $task->task_id,$user_id,
											  1,$max_levels,$display_week_hours,$sss,$sse);
					}
				}
			}
			if ($tmptasks != "") {
				$table_rows.=$tmpuser;
				$table_rows.=$tmptasks."</form>";
			}

		}
	}
}

function doChildren($list,$Lusers,$N,$id,$uid,$level,$maxlevels,$display_week_hours,$ss,$se) {
	$tmp="";
	if ($maxlevels==-1 || $level<$maxlevels) {
		for($c=0;$c<$N;$c++) {
			$task=$list[$c];
			if (($task->task_parent==$id) and isChildTask($task)) {
				// we have a child, do we have the user as a member?
				if (isMemberOfTask($list,$Lusers,$N,$uid,$task)) {
					$tmp.=displayTask($list,$task,$level,$display_week_hours,$ss,$se, $uid);
					$tmp.=doChildren($list,$Lusers,$N,$task->task_id,
                                     $uid,$level+1,$maxlevels,
                                     $display_week_hours,$ss,$se);
				}
			}
		}
	}
return $tmp;
}

function isMemberOfTask($list,$Lusers,$N,$user_id,$task) {

	for($i=0;$i<$N && $list[$i]->task_id!=$task->task_id;$i++);
	$users=$Lusers[$i];

	//$users=$Lusers[$task->getAssignedUsers();
	foreach($users as $task_user_id => $user_data) {
		if ($task_user_id==$user_id) { return true; }
	}

	// check child tasks if any

	for($c=0;$c<$N;$c++) {
		$ntask=$list[$c];
		if (($ntask->task_parent==$task->task_id) and isChildTask($ntask)) {
			// we have a child task
			if (isMemberOfTask($list,$Lusers,$N,$user_id,$ntask)) {
				return true;
			}
		}
	}
return false;
}

function displayTask($list,$task,$level,$display_week_hours,$fromPeriod,$toPeriod, $user_id) {

        global $AppUI, $df, $durnTypes, $log_userfilter_users, $priority, $z, $zi, $x;
	$zi++;
        $users = $task->getAssignedUsers();
        $projects = $task->getProjectName();
	$tmp="<tr>";
        $tmp.="<td align=\"center\" nowrap=\"nowrap\">";
        $tmp .= "<input type=\"checkbox\" name=\"task_id$task->task_id\" value=\"$task->task_id\"/>";
        $tmp.="</td>";
        $tmp.="<td align=\"center\" nowrap=\"nowrap\">";
        if ($task->task_priority > 0) {
                $tmp .= "<img src=\"./images/icons/1.gif\" width=13 height=16 alt=\"high\">";
        }
        elseif ($task->task_priority < 0) {
                $tmp .= "<img src=\"./images/icons/low.gif\" width=13 height=16 alt=\"low\">";
        }
        $tmp.="</td>";
	$tmp.="<td nowrap=\"nowrap\">";

	for($i=0;$i<$level;$i++) {
		$tmp.="&#160";
	}

	if ($task->task_milestone == true) { $tmp.="<B>"; }
	if ($level >= 1) { $tmp.= dPshowImage(dPfindImage('corner-dots.gif', 'tasks'), 16, 12, 'Subtask')."&nbsp;"; }
	$tmp.= "<a href='?m=tasks&a=view&task_id=$task->task_id'>".$task->task_name."</a>";
	if ($task->task_milestone == true) { $tmp.="</B>"; }
	$tmp.="</td>";
        $tmp.="<td align=\"center\" nowrap=\"nowrap\" >";
        $tmp.= "<a href='?m=projects&a=view&project_id=$task->task_project' style='background-color:#".@$projects["project_color_identifier"]."; color:".bestColor(@$projects['project_color_identifier'])."'>".$projects['project_short_name']."</a>";
        $tmp.="</td>";
        $tmp.="<td align=\"center\" nowrap=\"nowrap\">";
        $tmp .= $task->task_duration."&nbsp;".$AppUI->_($durnTypes[$task->task_duration_type]);
        $tmp.="</td>";
	$tmp.="<td align=\"center\" nowrap=\"nowrap\">";
	$dt=new CDate($task->task_start_date);
	$tmp.=$dt->format($df);
	$tmp.="&#160&#160&#160</td>";
	$tmp.="<td align=\"center\" nowrap=\"nowrap\">";
	$ed=new CDate($task->task_end_date);
        $now=new CDate();
        $dt=$now->dateDiff($ed);
        $sgn = $now->compare($ed,$now);
	$tmp.=($dt*$sgn);
	$tmp.="</td>";
        if ($display_week_hours) {
		$tmp.=displayWeeks($list,$task,$level,$fromPeriod,$toPeriod);
	}
	$tmp.="<td>";
        $sep = $us = "";
	foreach ($users as $row) {
                if ($row["user_id"]) {
                        $us .= "<a href='?m=admin&a=viewuser&user_id=$row[0]'>".$sep.$row['user_username']."&nbsp;(".$row['perc_assignment']."%)</a>";
                        $sep = ", ";
                }
        }
        $tmp .= $us;
        $tmp.="</td>";
        // create the list of possible assignees
        if ($zi == 1){
                //  selectbox may not have a size smaller than 2, use 5 here as minimum
                $zz = ($z < 5) ? 5 : ($z*1.5);
                if (sizeof($users) >= 7) {
                        $zz = $zz *2;
                }
		$zm1 = $z - 1;
                if ($zm1 ==0) $zm1 = 1;

                $tmp.="<td valign=\"top\" align=\"center\" nowrap=\"nowrap\" rowspan=\"$zm1\">";
		Global $system_users;
		$tmp.= arraySelect( $system_users, 'add_users', 'class="text" STYLE="width: 200px" size="'.($zz-1).'" multiple="multiple"',NULL );
               $tmp .= "</td>";
        }


	$tmp.="</tr>\n";
return $tmp;
}

function isChildTask($task) {
	return $task->task_id!=$task->task_parent;
}

function atoi($a) {
	return $a+0;
}

function weekDates($display_allocated_hours,$fromPeriod,$toPeriod) {
	if ($fromPeriod==-1) { return ""; }
	if (!$display_allocated_hours) { return ""; }

	$s=new CDate($fromPeriod);
	$e=new CDate($toPeriod);
	$sw=getBeginWeek($s);
	$ew=getEndWeek($e); //intval($e->Format("%U"));

	$row="";
	for($i=$sw;$i<=$ew;$i++) {
		$row.="<th nowrap=\"nowrap\">".$s->getWeekofYear()."</th>";
		$s->addSeconds(168*3600);	// + one week
	}
return $row;
}

function weekCells($display_allocated_hours,$fromPeriod,$toPeriod) {

	if ($fromPeriod==-1) { return 0; }
	if (!$display_allocated_hours) { return 0; }


	$s=new CDate($fromPeriod);
	$e=new CDate($toPeriod);
	$sw=getBeginWeek($s); //intval($s->Format("%U"));
	$ew=getEndWeek($e); //intval($e->Format("%U"));

return $ew-$sw+1;
}



// Look for a user when he/she has been allocated
// to this task and when. Report this in weeks
// This function is called within 'displayTask()'
function displayWeeks($list,$task,$level,$fromPeriod,$toPeriod) {

	if ($fromPeriod==-1) { return ""; }

	$s=new CDate($fromPeriod);
	$e=new CDate($toPeriod);
	$sw=getBeginWeek($s); 	//intval($s->Format("%U"));
	$ew=getEndWeek($e); //intval($e->Format("%U"));

	$st=new CDate($task->task_start_date);
	$et=new CDate($task->task_end_date);
	$stw=getBeginWeek($st); //intval($st->Format("%U"));
	$etw=getEndWeek($et); //intval($et->Format("%U"));

	//print "week from: $stw, to: $etw<br>\n";

	$row="";
	for($i=$sw;$i<=$ew;$i++) {
		if ($i>=$stw and $i<$etw) {
			$color="blue";
			if ($level==0 and hasChildren($list,$task)) { $color="#C0C0FF"; }
			else if ($level==1 and hasChildren($list,$task)) { $color="#9090FF"; }
			$row.="<td  nowrap=\"nowrap\" bgcolor=\"$color\">";
		}
		else {
			$row.="<td nowrap=\"nowrap\">";
		}
		$row.="&#160&#160</td>";
	}

return $row;
}

function getBeginWeek($d) {
	$dn=intval($d->Format("%w"));
	$dd=new CDate($d);
	$dd->subtractSeconds($dn*24*3600);
	return intval($dd->Format("%U"));
}

function getEndWeek($d) {

	$dn=intval($d->Format("%w"));
	if ($dn>0) { $dn=7-$dn; }
	$dd=new CDate($d);
	$dd->addSeconds($dn*24*3600);
	return intval($dd->Format("%U"));
}

function hasChildren($list,$task) {
	foreach($list as $t) {
		if ($t->task_parent==$task->task_id) { return true; }
	}
return false;
}

?>

<center>
	<table width="100%" border="0" cellpadding="2" cellspacing="1" class="std">
		<?php echo $table_header . $table_rows; ?>
	</table>
</center>

