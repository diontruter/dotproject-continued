<?php

/*
 * Dynamic Tasks Organizer - by J. Christopher Pereira
 *
 * Consider:
 *	- order by priorities
 *	- other related persons time availability
 *
 * Constraints:
 *	- other tasks
 *	- task dependencies
 *
 */

$errors = false;
$tasks = array();
$actions = false;

$do = isset( $_REQUEST['do'] ) ? $_REQUEST['do'] : 'conf';
$set_duration = isset( $_REQUEST['set_duration'] ) ? $_REQUEST['set_duration'] : null;
$set_dynamic = isset( $_REQUEST['set_dynamic'] ) ? $_REQUEST['set_dynamic'] : null;

$df = $AppUI->getPref('SHDATEFORMAT');

function task_link($task) {
	return "<a href='index.php?m=tasks&a=view&task_id=" . $task["task_id"] . "'>" . $task["task_name"] . "</a>";
}

function search_task($task_id) {
	global $tasks;
	for($i = 0; $i < count($tasks) ; $i++) {
		if($tasks[$i]["task_id"] == $task_id) return $i;
	}
	return -1;
}

function log_info($msg) {
	global $option_debug;
	if($option_debug) {
		echo "$msg<br>";
	}
}

function log_action($msg) {
	global $action;
	echo "&nbsp;&nbsp;<font color=red size=2>$msg</font><br>";
	$action = true;
}

function log_error($msg, $fields = "") {
	global $action;
	echo "<font color=red size=1>ERROR: $msg</font><br>$fields<hr>";
	$action = true;
}

function log_warning($msg, $fields = "") {
	global $show_warnings;
	echo "WARNING: $msg<br>$fields<hr>";
}

function convert2days( $durn, $units ) {
	global $AppUI;
	switch ($units) {
	case 'hours':
		return $durn / $AppUI->cfg['daily_working_hours'];
		break;
	case 'days':
		return $durn;
	}
}

function fixate_task($task_index, $time, $dep_on_task) {

	// task_index != task_id !!!

	global $tasks, $do, $option_advance_if_possible, $AppUI, $df;

	// don't fixate tasks before now

	if($time->getTimestamp() < time()) {
		$time = new CDate();
	}

	$start_date = new CDate( $time );
	$start_date->setFormat( $df );
	$end_date = $start_date;
	$durn = convert2days( $tasks[$task_index]["task_duration"], $tasks[$task_index]["task_duration_type"] );
	$end_date->addDays( $durn );

	// Complex SQL explanation:
	//
	// Objective: Check tasks overlapping only when
	// a user is vital for both tasks
	//
	// Definition of "vital for one task": when a task is assigned to user and total_users <= 2
	// (for example: if task is assigned to tree o more users, he is not vital).
	//
	// Thus, a user is vital for both tasks <=>
	//	- total_users <= 2 for both tasks
	//	- and he apears in both tasks
	//
	// Thus, in both tasks (say 4 and 10), a there will be a vital user <=>
	//	- "number of tasks with total_users <= 2"
	//	  = rows("select count(*) as num_users from user_tasks
	//	  where task_id=4 or task_id=10
	//	  group by task_id having num_users <= 2") == 2;
	//
	//	- and "number of users which appears in both tasks"
	//	  = rows("select count(*) as frec
	//	  from user_tasks where task_id=4 or task_id=10
	//	  group by user_id having frec = 2") > 0

	$t1_start = $start_date->getTimestamp();
	$t1_end = $end_date->getTimestamp();

	foreach($tasks as $task2) {
		$t2_start = db_dateTime2unix( $task2["task_start_date"] );
		$t2_end = db_dateTime2unix( $task2["task_end_date"] );

		if($task2["fixed"] && (
			($t1_start >= $t2_start && $t1_start <= $t2_end)
			|| ($t1_end >= $t2_start && $t1_end <= $t2_end))
		) {
			// tasks are overlapping

			if(!$option_advance_if_possible || $task2["task_precent_complete"] != 100) {

				$t1 = $tasks[$task_index]["task_id"];
				$t2 = $task2["task_id"];

				$sql1 = "select count(*) as num_users from user_tasks where task_id=$t1 or task_id=$t2 group by task_id having num_users <= 2";
				$sql2 = "select count(*) as frec from user_tasks where task_id=$t1 or task_id=$t2 group by user_id having frec = 2";

				$vital = mysql_num_rows(mysql_query($sql1)) == 2 && mysql_num_rows(mysql_query($sql2)) > 0;
				if($vital) {

					log_info("Task can't be set to [".$start_date->toString()." - ".$end_date->toString()."] due to conflicts with task " . task_link($task2) . ".");
					fixate_task($task_index, $t2_end, $dep_on_task);
					return;
				} else {
					log_info("Task conflicts with task " . task_link($task2) . " but there are no vital users.");
				}
			} else {
				log_info("Task " . task_link($task2) . " is complete, I won't check if it is overllaping");
			}
		}
	}

	$tasks[$task_index]["fixed"] = true;

	// be quite if nothing will be changed

	if (db_dateTime2unix( $tasks[$task_index]["task_start_date"] ) == $start_date->getTimestamp() &&
			db_dateTime2unix( $tasks[$task_index]["task_end_date"] ) == $end_date->getTimestamp()) {
		log_info("Nothing changed, still programmed for [".$start_date->toString()." - ".$end_date->toString()."]");
		return;
	}

	$tasks[$task_index]["task_start_date"] = db_unx2dateTime( $start_date->getTimestamp() );
	$tasks[$task_index]["task_end_date"] = db_unx2dateTime( $end_date->getTimestamp() );

	if($do == "ask") {
		if($dep_on_task) {
			log_action("I will fixate task " . task_link($tasks[$task_index]) . " to " . $start_date->toString() . " (depends on " .  task_link($dep_on_task) . ")");
		} else {
			log_action("I will fixate task " . task_link($tasks[$task_index]) . " to " . $start_date->toString() . " (no dependencies)");
		}

		// echo "<input type=hidden name=fixate_task[" . $tasks[$task_index]["task_id"] . "] value=y>";
	} else if($do == "fixate") {
		log_action("Task " . task_link($tasks[$task_index]) . " fixated to " . $start_date->toString);
		$sql = "update tasks set task_start_date = '" . $start_date->toString() . "', task_end_date = '" . $end_date->toString() . "' where task_id = " . $tasks[$task_index]["task_id"];
		mysql_query($sql);
	}
}

function get_last_children($task) {
	// returns the last children (leafs) from $task
	$arr = array();

	// query children from task
	$sql = "select * from tasks where task_parent=" . $task["task_id"];
	$query = mysql_query($sql);
	if(mysql_num_rows($query)) {
		// has children
		while($row = mysql_fetch_array($query)) {
			if($row["task_id"] != $task["task_id"]) {
				// add recursively children of children to $arr
				$sub = get_last_children($row);
				array_splice($arr, count($arr), 0, $sub);
			}
		}
	} else {
		// it's a leaf
		array_push($arr, $task);
	}
	return $arr;
}

function process_dependencies($i) {
	global $tasks, $option_advance_if_possible;

	if($tasks[$i]["fixed"]) return;

	log_info("<div style='padding-left: 1em'>Dependecies for '" . $tasks[$i]["task_name"] . "':<br>");

	// query dependencies for this task

	$query = mysql_query("select tasks.* from tasks,task_dependencies where task_id=dependencies_req_task_id and dependencies_task_id=" . $tasks[$i]["task_id"]);

	if(mysql_num_rows($query) != 0) {

		$all_fixed = true;
		$latest_end_date = null;

		// store dependencies in an array (for adding more entries on the fly)

		$dependencies = array();
		while($row = mysql_fetch_array($query)) {
			array_push($dependencies, $row);
		}

		$d = 0;

		while($d < count($dependencies)) {

			$row = $dependencies[$d];
			$index = search_task($row["task_id"]);

			if($index == -1) {
				// task is not listed => it's a task group
				// => $i depends on all its subtasks
				// => add all subtasks to the dependencies array

				log_info("- task '" . $row["task_name"] . "' is a task group (processing subtask's dependencies)");

				$children = get_last_children($row);
				// replace this taskgroup with all its subtasks

				array_splice($dependencies, $d, 1, $children);

				continue;
			}

			log_info(" - '" . $tasks[$index]["task_name"] . ($tasks[$index]["fixed"]?" (FIXED)":"") . "'");

			// TODO: Detect dependencies loops (A->B, B->C, C->A)

			process_dependencies($index);

			if(!$tasks[$index]["fixed"]) {
				$all_fixed = false;
			} else {
				// ignore dependencies of finished tasks if option is enabled
				if(!$option_advance_if_possible || $tasks[$index]["task_precent_complete"] != 100) {
					// get latest end_date
					$end_date = new CDate( db_dateTime2unix( $tasks[$index]["task_end_date"] ) );

					if(!$latest_end_date || $end_date->getTimestamp() > $latest_end_date->getTimestamp()) {
						$latest_end_date = $end_date;
						$dep_on_task = $row;
					}
				} else {
					log_info("this task is complete => don't check dependency");
				}
				$d++;
			}
		}

		if($all_fixed) {
			// this task depends only on fixated tasks
			log_info("all dependencies are fixed");
			fixate_task($i, $latest_end_date, $dep_on_task);
		} else {
			log_error("task has not fixed dependencies");
		}

	} else {
		// task has no dependencies
		log_info("no dependencies => ");
		fixate_task($i, time(), "");
	}
	log_info("</div><br>\n");
}
?>

<table name="table" cellspacing="1" cellpadding="1" border="0" width="98%">
<tr>
	<td><img src="./images/icons/tasks.gif" alt="" border="0"></td>
	<td nowrap><span class="title">Tasks Organizer Wizard</span></td>
	<td nowrap><img src="./images/shim.gif" width="16" height="16" alt="" border="0"></td>
	<td valign="top" align="right" width="100%"></td>
</tr>
</table>

<?php

/*** Process updates ***/

// update tasks duration
if($set_duration) {
	foreach($set_duration as $key=>$val) {
		if($val) {
			$sql = "update tasks set task_duration=" . ($val * $dayhour[$key]) . " where task_id=" . $key;
			mysql_query($sql);
		}
	}
	$do = "ask"; // ask again
}

if($set_dynamic) {
	foreach($set_dynamic as $key=>$val) {
		if($val) {
			$sql = "update tasks set task_dynamic=1 where task_id=$key";
			mysql_query($sql);
		}
	}
	$do = "ask";
}

?>

<form name="form" method="post">

<?php
if($do == "conf") {
	echo '<table border="0" cellpadding="4" cellspacing="0" width="98%" class="tbl">';
	echo '<tr>';
	echo '<td>';
}

function checkbox($name, $descr, $default = 0, $show = true) {
	global $$name;
	if(!isset($$name)) $$name=$default;
	if($show) {
		echo "<input type=checkbox name=$name value=1 " . ($$name?"checked":"") . ">$descr<br>";
	} else {
		echo "<input type=hidden name=$name value=" . ($$name?"1":"") . ">";
	}
}

checkbox("option_check_delayed_tasks", "Check delays for fixed tasks", 1, $do == "conf");
checkbox("option_fix_task_group_date_ranges", "Fix date ranges for task groups according to subtasks dates", 1, $do == "conf");
checkbox("option_no_end_date_warning", "Warn of fixed tasks without end dates", 0, $do == "conf");
checkbox("option_advance_if_possible", "Begin new tasks if dependencies are finished before expected", 1, $do == "conf");

/*
<input type=checkbox name=option_project value=1 <?php echo $option_project?"checked":"" ?>>Organize tasks belonging only to <select name=option_project_id>
	<?php
		$sql = "select project_id, project_name from projects";
		$query = mysql_query($sql);
		while($project = mysql_fetch_array($query)) {
			echo "<option value=" . $project["project_id"] . ">" . $project["project_name"] . "</option>";
		}
	?>
</select><br>
*/

checkbox("option_debug", "Show debug info", 0, $do == "conf");

if($do == "conf") { ?>
	</td>
</tr>
</table>
<br>
<?php }

if($do != "conf") {
	echo '<table border="0" cellpadding="4" cellspacing="0" width="98%" class="std">';
	echo '<tr>';
	echo '<td>';

	/**** Add tasks to an array and check conflicts ****/

	// Select tasks without children (sub tasks)

	$sql = "select a.*,!a.task_dynamic as fixed from tasks as a left join tasks as b on a.task_id = b.task_parent and a.task_id != b.task_id where b.task_id IS NULL or b.task_id = b.task_parent order by a.task_priority desc, a.task_order desc";
	$dtrc = mysql_query( $sql );

	while ($row = mysql_fetch_array( $dtrc, MYSQL_ASSOC )) {

		// check durations

		if(!$row["task_duration"]) {
			$row["task_end_date"] = "";
			log_error("Task " .task_link($row) . " has no duration.",
				"Please enter the expected duration: "
				."<input class=input type=text name='set_duration[" . $row["task_id"] . "]' size=3>"
				. "<select name='dayhour[" . $row["task_id"] . "]'>"
				. "<option value='1'>hour(s)</option>"
				. "<option value='24'>day(s)</option>"
				. "</select>"
			);
			$errors = true;
		}

		// calculate or set blank task_end_date if unset

		if(!$row["task_dynamic"] && $row["task_end_date"] == "0000-00-00 00:00:00") {
			$end_date = new CDate( db_dateTime2unix( $row["task_start_date"] ) );
			$durn = convert2days( $row["task_start_date"], $row["task_duration"] );
			$end_date->addDays( $durn );
			$row["task_end_date"] = db_unix2dateTime( $end_date->getTimestamp() );
			if($do=="ask" && $option_no_end_date_warning) {
				log_warning("Task " . task_link($row) . " has no end date. Using tasks duration instead.",
					"<input type=checkbox name='set_end_date[" . $row["task_id"] . "]' value=1> "
					."Set end date to " . $row["task_end_date"]
				);
			}
		}

		// check delayed tasks
		if($do == "ask") {
			if(!$row["task_dynamic"] && $row["task_precent_complete"] == 0) {
				// nothing has be done yet
				$end_time = new CDate( db_dateTime2unix( $row["task_end_date"] ) );
				if($end_time->getTimestamp() < time()) {
					if($option_check_delayed_tasks) {
						log_warning("Task " .task_link($row) . " started on " . $row["task_start_date"] . " and ended on " . $end_time->toString($df) . "." ,
							"<input type=checkbox name=set_dynamic[" . $row["task_id"] . "] value=1 checked> Set as dynamic task and reorganize<br>" .
							"<input type=checkbox name=set_priority[" . $row["task_id"] . "] value=1 checked> Set priority to high<br>"
						);
					}
				}
			}
		}
		array_push($tasks, $row);
	}

	if(!$errors) {
		for($i = 0; $i < count($tasks) ; $i++) {
			process_dependencies($i);
		}
	}

	if($option_fix_task_group_date_ranges) {
		// query taskgroups
		$sql = "select distinct a.* from tasks as a, tasks as b where b.task_parent = a.task_id and a.task_id != b.task_id";
		$taskgroups = mysql_query($sql);
		while($tg = mysql_fetch_array($taskgroups)) {
			$children = get_last_children($tg);
			$min_time = null;
			$max_time = null;
			foreach($children as $child) {
				$start_time = db_dateTime2unix($child["task_start_date"]);
				$end_time = db_dateTime2unix($child["task_end_date"]);
				if (!$min_time || $start_time->getTimestamp() < $min_time->getTimestamp()) {
					$min_time = $start_time;
				}
				if (!$max_time || $end_time->getTimestamp() > $max_time->getTimestamp()) {
					$max_time = $end_time;
				}
			}

			if (db_dateTime2unix($tg["task_start_date"]) != $min_time->getTimestamp()
					|| db_dateTime2unix($tg["task_end_date"]) != $max_time->getTimestamp()) {
				if ($do == "ask") {
					log_action("I will set date of task group " . task_link($tg) . " to " . $min_time->toString( $df ) . " - " . formatTime($max_time) . ".");
				} else if ($do == "fixate") {
					log_action("Date range of task group " . task_link($tg) . " changed to " . $min_time->toString( $df ) . " - " . formatTime($max_time) . ".");
					mysql_query("update tasks set task_start_date='" . $min_time->toString( $df ) . "', task_end_date='" . $max_time->toString( $df ) . "' where task_id=" . $tg["task_id"]);
				}
			}
		}
	}

	if(!$action) {
		echo "<font size=2><b>Tasks are already organized</b></font><br>";
	}

	echo '</td>';
	echo '</tr>';
	echo '</table>';
	echo '<br>';
}

if ($do=="conf" || $action) {
	if(!$errors) {
		echo "<input type=hidden name=do value=" . ($do=="ask"?"fixate":"ask") . ">";
		if($do == "ask") {
			echo "<font size=2><b>Do you want to accept this changes?</b></font><br>";
			echo "<input type=button value=accept class=button onClick='javascript:document.form.submit()'>";
		} else if ($do == "fixate") {
			echo "<font size=2><b>Tasks has been reorganized</b></font><br>";
		} else if ($do == "conf") {
				echo "<input type=button value=start class=button onClick='javascript:document.form.submit()'>";
		}
	} else {
		echo "<font size=2><b>Please correct the above errors</b></font><br>";
		echo "<input type=button value=submit class=button onClick='javascript:document.form.submit()'>";
	}
}
?>

</form>

</body>
</html>

