<?php /* TASKS $Id$ */
GLOBAL $m, $a, $project_id, $f, $min_view, $query_string, $durnTypes;
GLOBAL $task_sort_item1, $task_sort_type1, $task_sort_order1;
GLOBAL $task_sort_item2, $task_sort_type2, $task_sort_order2;
GLOBAL $user_id, $dPconfig;
/*
	tasks.php

	This file contains common task list rendering code used by
	modules/tasks/index.php and modules/projects/vw_tasks.php

	External used variables:

	* $min_view: hide some elements when active (used in the vw_tasks.php)
	* $project_id
	* $f
	* $query_string
*/

if (empty($query_string)) {
	$query_string = "?m=$m&a=$a";
}

/****
// Let's figure out which tasks are selected
*/

global $tasks_opened;
global $tasks_closed;

function closeOpenedTask($task_id){
    global $tasks_opened;
    global $tasks_closed;
    
    unset($tasks_opened[array_search($task_id, $tasks_opened)]);
    $tasks_closed[] = $task_id;
}

$tasks_closed = array();
$tasks_opened = $AppUI->getState("tasks_opened");
if(!$tasks_opened){
    $tasks_opened = array();
}

if(dPGetParam($_GET, "task_id", 0) > 0){
    $_GET["open_task_id"] = $_REQUEST["task_id"];
}

if(($open_task_id = dPGetParam($_GET, "open_task_id", 0)) > 0 && !in_array($_GET["open_task_id"], $tasks_opened)) {
    $tasks_opened[] = $_GET["open_task_id"];
}

// Closing tasks needs also to be within tasks iteration in order to
// close down all child tasks
if(($close_task_id = dPGetParam($_GET, "close_task_id", 0)) > 0) {
    closeOpenedTask($close_task_id);
}

// We need to save tasks_opened until the end because some tasks are closed within tasks iteration
//echo "<pre>"; print_r($tasks_opened); echo "</pre>";
/// End of tasks_opened routine


$durnTypes = dPgetSysVal( 'TaskDurationType' );

$task_project = intval( dPgetParam( $_GET, 'task_project', null ) );
$task_id = intval( dPgetParam( $_GET, 'task_id', null ) );

$task_sort_item1 = dPgetParam( $_GET, 'task_sort_item1', '' );
$task_sort_type1 = dPgetParam( $_GET, 'task_sort_type1', '' );
$task_sort_item2 = dPgetParam( $_GET, 'task_sort_item2', '' );
$task_sort_type2 = dPgetParam( $_GET, 'task_sort_type2', '' );
$task_sort_order1 = intval( dPgetParam( $_GET, 'task_sort_order1', 0 ) );
$task_sort_order2 = intval( dPgetParam( $_GET, 'task_sort_order2', 0 ) );

$show_all_assignees = isset($dPconfig['show_all_task_assignees']) ? $dPconfig['show_all_task_assignees'] : false;

$where = '';
$join = winnow( 'projects', 'project_id', $where );

// pull valid projects and their percent complete information
$psql = "
SELECT project_id, project_color_identifier, project_name,
	COUNT(t1.task_id) as total_tasks,
	SUM(t1.task_duration*t1.task_percent_complete)/SUM(t1.task_duration) as project_percent_complete,
	company_name
FROM projects
LEFT JOIN tasks t1 ON projects.project_id = t1.task_project" .
" LEFT JOIN companies ON company_id = project_company" .
$join .
"WHERE $where GROUP BY project_id
ORDER BY project_name
";

//echo "<pre>$psql</pre>";

$prc = db_exec( $psql );
echo db_error();

$projects = array();
while ($row = db_fetch_assoc( $prc )) {
	$projects[$row["project_id"]] = $row;
}

// pull tasks
$select = "
distinct tasks.task_id, task_parent, task_name, task_start_date, task_end_date, task_dynamic,
task_priority, task_percent_complete, task_duration, task_duration_type, task_project,
task_description, task_owner, usernames.user_username, usernames.user_id, task_milestone,
assignees.user_username as assignee_username, count(distinct assignees.user_id) as assignee_count, count(distinct files.file_task) as file_count, history_date as last_update
";

$from = "tasks";
$join = "LEFT JOIN history ON history_item = tasks.task_id AND history_table='tasks' ";
$join .= "LEFT JOIN projects ON project_id = task_project";
$join .= " LEFT JOIN users as usernames ON task_owner = usernames.user_id";
// patch 2.12.04 show assignee and count
$join .= " LEFT JOIN user_tasks as ut ON ut.task_id = tasks.task_id";
$join .= " LEFT JOIN users as assignees ON assignees.user_id = ut.user_id";

// to figure out if a file is attached to task
$join .= " LEFT JOIN files on tasks.task_id = files.file_task";

$where = $project_id ? "\ntask_project = $project_id" : "project_active != 0";

switch ($f) {
	case 'all':
		break;
	case 'myfinished7days':		
		$where .= " AND user_tasks.user_id = $user_id";
	case 'allfinished7days':	// patch 2.12.04 tasks finished in the last 7 days
		$from .= ", user_tasks";
		$where .= "
			AND task_project             = projects.project_id
			AND user_tasks.task_id       = tasks.task_id
			AND task_percent_complete    = '100'
		        AND task_end_date >= '" . date("Y-m-d 00:00:00", mktime(0, 0, 0, date("m"), date("d")-7, date("Y"))) . "'";
		break;		
	case 'children':
	// patch 2.13.04 2, fixed ambigious task_id
		$where .= "\n	AND task_parent = $task_id AND tasks.task_id != $task_id";	
		break;
	case 'myproj':
		$where .= "\n	AND project_owner = $user_id";
		break;
	case 'mycomp':
		$where .= "\n	AND project_company = $AppUI->user_company";
		break;
	case 'myunfinished':
		$from .= ", user_tasks";
		// This filter checks all tasks that are not already in 100% 
		// and the project is not on hold nor completed
		// patch 2.12.04 finish date required to be consider finish
		$where .= "
					AND task_project             = projects.project_id
					AND user_tasks.user_id       = $user_id
					AND user_tasks.task_id       = tasks.task_id
					AND (task_percent_complete    < '100' OR task_end_date = '')
					AND projects.project_active  = '1'
					AND projects.project_status != '4'
					AND projects.project_status != '5'";
		break;
	case 'allunfinished':
		// patch 2.12.04 finish date required to be consider finish
		// patch 2.12.04 2, also show unassigned tasks
		$from .= ", user_tasks";
		$where .= "
					AND task_project             = projects.project_id
					AND (user_tasks.task_id      = '0'   OR user_tasks.task_id = tasks.task_id)
					AND (task_percent_complete   < '100' OR task_end_date = '')
					AND projects.project_active  = '1'
					AND projects.project_status != '4'
					AND projects.project_status != '5'";
		break;
	case 'unassigned':
		$join .= "\n LEFT JOIN user_tasks ON tasks.task_id = user_tasks.task_id";
		$where .= "
					AND user_tasks.task_id IS NULL";
		break;
	case 'taskcreated':
		$where .= " AND task_owner = '$user_id'";
		break;
	default:
		$from .= ", user_tasks";
		$where .= "
	AND task_project = projects.project_id
	AND user_tasks.user_id = $user_id
	AND user_tasks.task_id = tasks.task_id";
		break;
}

if ( $min_view )
	$task_status = intval( dPgetParam( $_GET, 'task_status', null ) );
else
	$task_status = intval( $AppUI->getState( 'inactive' ) );

$where .= "\n	AND task_status = '$task_status'";

// patch 2.12.04 text search
if ( $search_text = $AppUI->getState('searchtext') )
	$where .= "\n AND (task_name LIKE ('%$search_text%') OR task_description LIKE ('%$search_text%') )";

// filter tasks considering task and project permissions
$projects_filter = '';
$tasks_filter = '';

// TODO: Enable tasks filtering

$join .= winnow( 'projects', 'tasks.task_project', $projects_filter, 'perm1' );
$join .= winnow( 'tasks', 'tasks.task_id', $tasks_filter, 'perm2' );
$where .= " AND ( ($projects_filter) )";
// echo "<pre>$where</pre>";

// Filter by company
if ( ! $min_view && $f2 != 'all' ) {
	 $join .= "\nLEFT JOIN companies ON company_id = projects.project_company";
         $where .= "\nAND company_id = $f2  ";
}

// patch 2.12.04 ADD GROUP BY clause for assignee count
$tsql = "SELECT $select FROM $from $join WHERE $where" .
  "\nGROUP BY task_id" .
  "\nORDER BY project_id, task_start_date";

// echo "<pre>$tsql</pre>";

$ptrc = db_exec( $tsql );
$nums = db_num_rows( $ptrc );
echo db_error();

//pull the tasks into an array
/*
for ($x=0; $x < $nums; $x++) {
	$row = db_fetch_assoc( $ptrc );
	$projects[$row['task_project']]['tasks'][] = $row;
}
*/
for ($x=0; $x < $nums; $x++) {
	$row = db_fetch_assoc( $ptrc );

	//add information about assigned users into the page output
	$ausql = "SELECT ut.user_id,
	u.user_username, contact_email
	FROM user_tasks ut
	LEFT JOIN users u ON u.user_id = ut.user_id
        LEFT JOIN contacts ON u.user_contact = contact_id
	WHERE ut.task_id=".$row['task_id'];
	
	$assigned_users = array ();
	$paurc = db_exec( $ausql );
	$nnums = db_num_rows( $paurc );
	echo db_error();
	for ($xx=0; $xx < $nnums; $xx++) {
		$row['task_assigned_users'][] = db_fetch_assoc($paurc);
	}
	//pull the final task row into array
	$projects[$row['task_project']]['tasks'][] = $row;
}

//This kludgy function echos children tasks as threads

if (! function_exists('showtask') ) {
function showtask( &$a, $level=0, $is_opened = true ) {
	global $AppUI, $done, $query_string, $durnTypes, $show_all_assignees;

	$df = $AppUI->getPref('SHDATEFORMAT');
	$df .= " " . $AppUI->getPref('TIMEFORMAT');

	$done[] = $a['task_id'];

	$start_date = intval( $a["task_start_date"] ) ? new CDate( $a["task_start_date"] ) : null;
	$end_date = intval( $a["task_end_date"] ) ? new CDate( $a["task_end_date"] ) : null;
        $last_update = intval( $a['last_update'] ) ? new CDate( $a['last_update'] ) : null;

	$s = "\n<tr>";
// edit icon
	$s .= "\n\t<td>";
	$canEdit = !getDenyEdit( 'tasks', $a["task_id"] );
	if ($canEdit) {
		$s .= "\n\t\t<a href=\"?m=tasks&a=addedit&task_id={$a['task_id']}\">"
			. "\n\t\t\t".'<img src="./images/icons/pencil.gif" alt="'.$AppUI->_( 'Edit Task' ).'" border="0" width="12" height="12">'
			. "\n\t\t</a>";
	}
	$s .= "\n\t</td>";
// percent complete
	$s .= "\n\t<td align=\"right\">".intval( $a["task_percent_complete"] ).'%</td>';
// priority
	$s .= "\n\t<td align='center' nowrap='nowrap'>";
	if ($a["task_priority"] < 0 ) {
		$s .= "\n\t\t<img src=\"./images/icons/low.gif\" width=13 height=16>";
	} else if ($a["task_priority"] > 0) {
		$s .= "\n\t\t<img src=\"./images/icons/" . $a["task_priority"] .'.gif" width=13 height=16>';
	}
	$s .= $a["file_count"] > 0 ? "<img src=\"./images/clip.png\" alt=\"F\">" : "";
	$s .= "</td>";
// dots
	$s .= '<td width="90%">';
	for ($y=0; $y < $level; $y++) {
		if ($y+1 == $level) {
			$s .= '<img src="./images/corner-dots.gif" width="16" height="12" border="0">';
		} else {
			$s .= '<img src="./images/shim.gif" width="16" height="12"  border="0">';
		}
	}
// name link
	$alt = htmlspecialchars( $a["task_description"] );

	$open_link = $is_opened ? "<a href='index.php$query_string&close_task_id=".$a["task_id"]."'><img src='images/icons/collapse.gif' border='0' align='center' /></a>" : "<a href='index.php$query_string&open_task_id=".$a["task_id"]."'><img src='images/icons/expand.gif' border='0' /></a>";
	if ($a["task_milestone"] > 0 ) {
		$s .= '&nbsp;<a href="./index.php?m=tasks&a=view&task_id=' . $a["task_id"] . '" title="' . $alt . '"><b>' . $a["task_name"] . '</b></a></td>';
	} else if ($a["task_dynamic"] == '1'){
		$s .= $open_link.'&nbsp;<a href="./index.php?m=tasks&a=view&task_id=' . $a["task_id"] . '" title="' . $alt . '"><b><i>' . $a["task_name"] . '</i></b></a></td>';
	} else {
		$s .= '&nbsp;<a href="./index.php?m=tasks&a=view&task_id=' . $a["task_id"] . '" title="' . $alt . '">' . $a["task_name"] . '</a></td>';
	}
// task owner
	$s .= '<td nowrap="nowrap" align="center">'."<a href='?m=admin&a=viewuser&user_id=".$a['user_id']."'>".$a['user_username']."</a>".'</td>';
//	$s .= '<td nowrap="nowrap" align="center">'. $a["user_username"] .'</td>';
	if ( isset($a['task_assigned_users']) && $assigned_users = $a['task_assigned_users']) {
		$a_u_tmp_array = array();
		if($show_all_assignees){
			$s .= '<td align="center">';
			foreach ( $assigned_users as $val) {
				//$a_u_tmp_array[] = "<A href='mailto:".$val['user_email']."'>".$val['user_username']."</A>";
				$a_u_tmp_array[] = "<a href='?m=admin&a=viewuser&user_id=".$val['user_id']."'>".$val['user_username']."</a>";
			}
			$s .= join ( ', ', $a_u_tmp_array );
			$s .= '</td>';
		} else {
			$s .= '<td align="center" nowrap="nowrap">';
//			$s .= $a['assignee_username'];
			$s .= "<a href='?m=admin&a=viewuser&user_id=".$assigned_users[0]['user_id']."'>".$assigned_users[0]['user_username'] . '</a>';
			if($a['assignee_count']>1){
                        $id = $a['task_id'];
			$s .= " <a href=\"javascript: void(0);\"  onClick=\"toggle_users('users_$id');\" title=\"" . join ( ', ', $a_u_tmp_array ) ."\">(+". ($a['assignee_count']-1) .")</a>";
                        
                        $s .= '<span style="display: none" id="users_' . $id . '">';

                                $a_u_tmp_array[] = $assigned_users[0]['user_username'];
				for ( $i = 1; $i < count( $assigned_users ); $i++) {
					$a_u_tmp_array[] = $assigned_users[$i]['user_username'];
                                        $s .= '<br /><a href="?m=admin&a=viewuser&user_id="' . $assigned_users[$i]['user_id'] . '">' . $assigned_users[$i]['user_username'] . '</a>';
				}
                        $s .= '</span>';
			}
			$s .= '</td>';
		}
	} else {
		// No users asigned to task
		$s .= '<td align="center">-</td>';
	}
	
	$s .= '<td nowrap="nowrap" align="center">'.($start_date ? $start_date->format( $df ) : '-').'</td>';
// duration or milestone
	$s .= '<td align="center" nowrap="nowrap">';
	if ( $a['task_milestone'] == '0' ) {
		$s .= $a['task_duration'] . ' ' . $AppUI->_( $durnTypes[$a['task_duration_type']] );
	} else {
		$s .= $AppUI->_("Milestone");
	}
	$s .= '</td>';
	$s .= '<td nowrap="nowrap" align="center">'.($end_date ? $end_date->format( $df ) : '-').'</td>';
	$s .= '<td nowrap="nowrap" align="center">'.($last_update ? $last_update->format( $df ) : '-').'</td>';

	$s .= '</tr>';

	echo $s;
}

}

if (! function_exists('findchild') ) {
function findchild( &$tarr, $parent, $level=0){
	GLOBAL $projects;
	global $tasks_opened;
	
	$level = $level+1;
	$n = count( $tarr );
	
	for ($x=0; $x < $n; $x++) {
		if($tarr[$x]["task_parent"] == $parent && $tarr[$x]["task_parent"] != $tarr[$x]["task_id"]){
		    $is_opened = in_array($tarr[$x]["task_id"], $tasks_opened);
			showtask( $tarr[$x], $level, $is_opened );
			if($is_opened || !$tarr[$x]["task_dynamic"]){
			    findchild( $tarr, $tarr[$x]["task_id"], $level);
			}
		}
	}
}
}

/* please throw this in an include file somewhere, its very useful */

function array_csort()   //coded by Ichier2003
{
    $args = func_get_args();
    $marray = array_shift($args);
	
	if ( empty( $marray )) return array();
	
	$i = 0;
    $msortline = "return(array_multisort(";
	$sortarr = array();
    foreach ($args as $arg) {
        $i++;
        if (is_string($arg)) {
            foreach ($marray as $row) {
                $sortarr[$i][] = $row[$arg];
            }
        } else {
            $sortarr[$i] = $arg;
        }
        $msortline .= "\$sortarr[".$i."],";
    }
    $msortline .= "\$marray));";

    eval($msortline);
    return $marray;
}

function sort_by_item_title( $title, $item_name, $item_type )
{
	global $AppUI,$project_id,$min_view,$m;
	global $task_sort_item1,$task_sort_type1,$task_sort_order1;
	global $task_sort_item2,$task_sort_type2,$task_sort_order2;

	if ( $task_sort_item2 == $item_name ) $item_order = $task_sort_order2;
	if ( $task_sort_item1 == $item_name ) $item_order = $task_sort_order1;

	if ( isset( $item_order ) ) {
		if ( $item_order == SORT_ASC )
			echo '<img src="./images/icons/low.gif" width=13 height=16>';
		else
			echo '<img src="./images/icons/1.gif" width=13 height=16>';
	} else
		$item_order = SORT_DESC;

	/* flip the sort order for the link */
	$item_order = ( $item_order == SORT_ASC ) ? SORT_DESC : SORT_ASC;
	if ( $m == 'tasks' )
		echo '<a href="./index.php?m=tasks';
	else
		echo '<a href="./index.php?m=projects&a=view&project_id='.$project_id;

	echo '&task_sort_item1='.$item_name;
	echo '&task_sort_type1='.$item_type;
	echo '&task_sort_order1='.$item_order;
	if ( $task_sort_item1 == $item_name ) {
		echo '&task_sort_item2='.$task_sort_item2;
		echo '&task_sort_type2='.$task_sort_type2;
		echo '&task_sort_order2='.$task_sort_order2;
	} else {
		echo '&task_sort_item2='.$task_sort_item1;
		echo '&task_sort_type2='.$task_sort_type1;
		echo '&task_sort_order2='.$task_sort_order1;
	}
	echo '" class="hdr">';
	
	echo $AppUI->_($title);
	
	echo '</a>';
}

?>

<script type="text/JavaScript">
function toggle_users(id){
  var element = document.getElementById(id);
  element.style.display = (element.style.display == '' || element.style.display == "none") ? "inline" : "none";
}
</script>


<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th width="10">&nbsp;</th>
	<th width="20"><?php echo $AppUI->_('Work');?></th>
	<th align='center'><?= sort_by_item_title( 'P', 'task_priority', SORT_NUMERIC ) ?></th>
	<th width="200"><?php sort_by_item_title( 'Task Name', 'task_name', SORT_STRING );?></th>
	<th nowrap="nowrap"><?php sort_by_item_title( 'Task Creator', 'user_username', SORT_STRING );?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Assigned users')?></th>
	<th nowrap="nowrap"><?php sort_by_item_title( 'Start Date', 'task_start_date', SORT_NUMERIC );?></th>
	<th nowrap="nowrap"><?php sort_by_item_title( 'Duration', 'task_duration', SORT_NUMERIC );?>&nbsp;&nbsp;</th>
	<th nowrap="nowrap"><?php sort_by_item_title( 'Finish Date', 'task_end_date', SORT_NUMERIC );?></th>
	<th nowrap="nowrap"><?php sort_by_item_title( 'Last Update', 'last_update', SORT_NUMERIC );?></th>
</tr>
<?php
//echo '<pre>'; print_r($projects); echo '</pre>';
reset( $projects );

foreach ($projects as $k => $p) {
	$tnums = count( @$p['tasks'] );
// don't show project if it has no tasks
// patch 2.12.04, show project if it is the only project in view
	if ($tnums > 0 || $project_id == $p['project_id']) {
//echo '<pre>'; print_r($p); echo '</pre>';
		if (!$min_view) {
?>
<tr>
	<td>
		<a href="index.php?m=tasks&f=<?php echo $f;?>&project_id=<?php echo $project_id ? 0 : $k;?>">
			<img src="./images/icons/<?php echo $project_id ? 'expand.gif' : 'collapse.gif';?>" width="16" height="16" border="0" alt="<?php echo $project_id ? 'show other projects' : 'show only this project';?>">
		</a>
	</td>
	<td colspan="9">
		<table width="100%" border="0">
		<tr>
			<!-- patch 2.12.04 display company name next to project name -->
			<td nowrap style="border: outset #eeeeee 2px;background-color:#<?php echo @$p["project_color_identifier"];?>">
				<a href="./index.php?m=projects&a=view&project_id=<?php echo $k;?>">
				<span style='color:<?php echo bestColor( @$p["project_color_identifier"] ); ?>;text-decoration:none;'><strong><?php echo @$p["company_name"].' :: '.@$p["project_name"];?></strong></span></a>
			</td>
			<td width="<?php echo (101 - intval(@$p["project_percent_complete"]));?>%">
				<?php echo (intval(@$p["project_percent_complete"]));?>%
			</td>
		</tr>
		</table>
</tr>
<?php
		}
		global $done;
		$done = array();
		if ( $task_sort_item1 != "" )
		{
			if ( $task_sort_item2 != "" && $task_sort_item1 != $task_sort_item2 )
				$p['tasks'] = array_csort($p['tasks'], $task_sort_item1, $task_sort_order1, $task_sort_type1
										  , $task_sort_item2, $task_sort_order2, $task_sort_type2 );
			else $p['tasks'] = array_csort($p['tasks'], $task_sort_item1, $task_sort_order1, $task_sort_type1 );
		}
		
		for ($i=0; $i < $tnums; $i++) {
			$t = $p['tasks'][$i];
			if ($t["task_parent"] == $t["task_id"]) {
			    $is_opened = in_array($t["task_id"], $tasks_opened);
				showtask( $t, 0, $is_opened );
				if($is_opened || !$t["task_dynamic"]){
				    findchild( $p['tasks'], $t["task_id"] );
				}
			}
		}
// check that any 'orphaned' user tasks are also display
		for ($i=0; $i < $tnums; $i++) {
			if ( !in_array( $p['tasks'][$i]["task_id"], $done )) {
			    if($p['tasks'][$i]["task_dynamic"] && in_array( $p['tasks'][$i]["task_parent"], $tasks_closed)) {
			        closeOpenedTask($p['tasks'][$i]["task_id"]);
			    }
			    if(in_array($p['tasks'][$i]["task_parent"], $tasks_opened)){
				    showtask( $p['tasks'][$i], 1, false);
			    }
			}
		}

		if($tnums && $dPconfig['enable_gantt_charts'] && !$min_view) { ?>
		<tr>
			<td colspan="10" align="right">
				<input type="button" class="button" value="<?php echo $AppUI->_('Gantt Chart');?>" onclick="javascript:window.location='index.php?m=tasks&a=viewgantt&project_id=<?php echo $k;?>';" />
			</td>
		</tr>
		<?php }
	}
}
$AppUI->setState("tasks_opened", $tasks_opened);
?>
</table>
