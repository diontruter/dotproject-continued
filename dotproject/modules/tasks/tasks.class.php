<?php /* TASKS $Id$ */

require_once( $AppUI->getSystemClass( 'libmail' ) );
require_once( $AppUI->getSystemClass( 'dp' ) );
require_once( $AppUI->getModuleClass( 'projects' ) );

// user based access
$task_access = array(
	'0'=>'Public',
	'1'=>'Protected',
	'2'=>'Participant',
	'3'=>'Private'
);

// this var is intended to track new status in task
$new_status = null;
$new_project = null;

/*
* CTask Class
*/
class CTask extends CDpObject {
/** @var int */
	var $task_id = NULL;
/** @var string */
	var $task_name = NULL;
/** @var int */
	var $task_parent = NULL;
	var $task_milestone = NULL;
	var $task_project = NULL;
	var $task_owner = NULL;
	var $task_start_date = NULL;
	var $task_duration = NULL;
	var $task_duration_type = NULL;
/** @deprecated */
	var $task_hours_worked = NULL;
	var $task_end_date = NULL;
	var $task_status = NULL;
	var $task_priority = NULL;
	var $task_percent_complete = NULL;
	var $task_description = NULL;
	var $task_target_budget = NULL;
	var $task_related_url = NULL;
	var $task_creator = NULL;

	var $task_order = NULL;
	var $task_client_publish = NULL;
	var $task_dynamic = NULL;
	var $task_access = NULL;
	var $task_notify = NULL;
	var $task_departments = NULL;
	var $task_contacts = NULL;
	var $task_custom = NULL;
	var $task_type   = NULL;

	
	function CTask() {
		$this->CDpObject( 'tasks', 'task_id' );
	}

// overload check
	function check() {
		global $new_status, $new_project;
		
		if ($this->task_id === NULL) {
			return 'task id is NULL';
		}
	// ensure changes to checkboxes are honoured
		$this->task_milestone = intval( $this->task_milestone );
		$this->task_dynamic   = intval( $this->task_dynamic );
		
		$this->task_percent_complete = intval( $this->task_percent_complete );
	
		if (!$this->task_duration) {
			$this->task_duration = '0';
		}
		if (!$this->task_duration_type) {
			$this->task_duration_type = 1;
		}
		if (!$this->task_related_url) {
			$this->task_related_url = '';
		}
		if (!$this->task_notify) {
			$this->task_notify = 0;
		}
		
		$actual_status = db_loadResult("select task_status from tasks where task_id='$this->task_id' or task_parent='$this->task_id'");
		if($actual_status != $this->task_status){
			$new_status = $this->task_status;
		}

		$actual_project = db_loadResult("select task_project from tasks where task_id='$this->task_id'");
		if($actual_project != $this->task_project){
			$new_project = $this->task_project;
		}
		
		return NULL;
	}

	
	function updateDynamics( $fromChildren = false ) {
		//Has a parent or children, we will check if it is dynamic so that it's info is updated also
		
		$modified_task = new CTask();

		if ( $fromChildren ){
			$modified_task = &$this;
		} else {
			$modified_task->load($this->task_parent);
		}

		if ( $modified_task->task_dynamic == 1 ) {
			//Update allocated hours based on children
			$sql = "SELECT SUM( task_duration * task_duration_type ) from " . $this->_tbl . " WHERE task_parent = " . $modified_task->task_id .
					" and task_id != " . $modified_task->task_id . " GROUP BY task_parent;";
			$children_allocated_hours = (float) db_loadResult( $sql );
			if ( $modified_task->task_duration_type == 1 ) {
				$modified_task->task_duration = round($children_allocated_hours,2);
			} else {
				$modified_task->task_duration = round($children_allocated_hours / $modified_task->task_duration_type, 2);
			}

			//Update worked hours based on children
			$sql = "SELECT sum( task_log_hours ) FROM tasks, task_log
					WHERE task_id = task_log_task AND task_parent = " . $modified_task->task_id .
					" AND task_id != " . $modified_task->task_id .
					" AND task_dynamic = 0";
			$children_hours_worked = (float) db_loadResult( $sql );
			
			
			//Update worked hours based on dynamic children tasks
			$sql = "SELECT sum( task_hours_worked ) FROM tasks
					WHERE task_dynamic = 1 AND task_parent = " . $modified_task->task_id .
					" AND task_id != " . $modified_task->task_id;
			$children_hours_worked += (float) db_loadResult( $sql );
			
			$modified_task->task_hours_worked = $children_hours_worked;
					
			//Update percent complete
			$sql = "SELECT sum( task_percent_complete )  / count( task_percent_complete ) 
					FROM tasks WHERE task_parent = " . $modified_task->task_id . 
					" AND task_id != " . $modified_task->task_id;
			$modified_task->task_percent_complete = (float) db_loadResult( $sql );

			//Update start date
			$sql = "SELECT min( task_start_date ) FROM tasks
					WHERE task_parent = " . $modified_task->task_id .
					" AND task_id != " . $modified_task->task_id .
					" AND ! isnull( task_start_date ) AND task_start_date !=  '0000-00-00 00:00:00'";
			$modified_task->task_start_date = db_loadResult( $sql );

			//Update end date
			$sql = "SELECT max( task_end_date ) FROM tasks
					WHERE task_parent = " . $modified_task->task_id .
					" AND task_id != " . $modified_task->task_id .
					" AND ! isnull( task_end_date ) AND task_end_date !=  '0000-00-00 00:00:00'";
			$modified_task->task_end_date = db_loadResult( $sql );

			//If we are updating a dynamic task from its children we don't want to store() it
			//when the method exists the next line in the store calling function will do that
			if ( $fromChildren == false ) $modified_task->store();
		}
	}

/**
*	Copy the current task
*
*	@author	handco <handco@users.sourceforge.net>
*	@param	int		id of the destination project
*	@return	object	The new record object or null if error
**/
	function copy($destProject_id = 0) {
		$newObj = $this->clone();

		// Copy this task to another project if it's specified
		if ($destProject_id != 0)
			$newObj->task_project = $destProject_id;

		$msg = $newObj->store();

		if ($newObj->task_parent == $this->task_id) {
			$newObj->task_parent = $newObj->task_id;
			$msg = $newObj->store();
		}

		return $newObj;
	}// end of copy()

/**
* @todo Parent store could be partially used
*/
	function store() {
		GLOBAL $AppUI, $new_status, $new_project;

		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed - $msg";
		}
		if( $this->task_id ) {
			$this->_action = 'updated';

			// if task_status chenged, then update subtasks
			if(!is_null($new_status)){
				$this->updateSubTasksStatus($new_status);
			}
			
			if(!is_null($new_project)){
				$this->updateSubTasksProject($new_project);
			}
			
			$this->updateDynamics(true);

			// add to shift dependencies dates
			$oTsk = new CTask();
			$oTsk->load ($this->task_id);
			if ($this->task_end_date != $oTsk->task_end_date) {
				// we need to shift tasks
				$origDate = new CDate ($oTsk->task_end_date);
				$destDate = new CDate ($this->task_end_date);
				$this->shiftDependantTasks ($destDate->getTime() - $origDate->getTime());
			}

			$ret = db_updateObject( 'tasks', $this, 'task_id', false );

		} else {
			$this->_action = 'added';
			$ret = db_insertObject( 'tasks', $this, 'task_id' );

			if (!$this->task_parent) {
			// new task, parent = task id
				$sql = "UPDATE tasks SET task_parent = $this->task_id WHERE task_id = $this->task_id";
				db_exec( $sql );
			}
		// insert entry in user tasks
			$sql = "INSERT INTO user_tasks (user_id, task_id, user_type) VALUES ($AppUI->user_id, $this->task_id, -1)";
			db_exec( $sql );
		}
		
		//split out related departments and store them seperatly.
		$sql = 'DELETE FROM task_departments WHERE task_id='.$this->task_id;
		db_exec( $sql );
		$departments = explode(',',$this->task_departments);
		foreach($departments as $department){
			$sql = 'INSERT INTO task_departments (task_id, department_id) values ('.$this->task_id.', '.$department.')';
			db_exec( $sql );
		}
		
		//split out related contacts and store them seperatly.
		$sql = 'DELETE FROM task_contacts WHERE task_id='.$this->task_id;
		db_exec( $sql );
		$contacts = explode(',',$this->task_contacts);
		foreach($contacts as $contact){
			$sql = 'INSERT INTO task_contacts (task_id, contact_id) values ('.$this->task_id.', '.$contact.')';
			db_exec( $sql );
		}

		if ( $this->task_parent != $this->task_id ){
			//Has parent
			$this->updateDynamics();
		}

		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}

/**
* @todo Parent store could be partially used
* @todo Can't delete a task with children
*/
	function delete() {
		$this->_action = 'deleted';
	// delete linked user tasks
		$sql = "DELETE FROM user_tasks WHERE task_id = $this->task_id";
		if (!db_exec( $sql )) {
			return db_error();
		}

		//load it before deleting it because we need info on it to update the parents later on
		$this->load($this->task_id);
		
		// delete the tasks...what about orphans?
		// delete task with parent is this task
		
		$sql = "DELETE FROM tasks WHERE task_id = $this->task_id";
		if (!db_exec( $sql )) {
			return db_error();
		} else {
			if ( $this->task_parent != $this->task_id ){
				// Has parent, run the update sequence, this child will no longer be in the
				// database
				$this->updateDynamics();
			}
		}
		
		$sql = "SELECT * FROM tasks WHERE task_parent = $this->task_id";
		$children_taks = db_loadHashList($sql, "task_id");
		
		if(count($children_taks) > 0){
		     $sql = "DELETE FROM tasks WHERE task_parent = $this->task_id";
                    	     if (!db_exec( $sql )) {
			return db_error();
		      }else{
		          $this->_action ='deleted whit childs';
		      }
		}
	
		 return NULL;
	}


         // unassign a user from task
	function removeAssigned( $user_id ) {
	// delete all current entries
		$sql = "DELETE FROM user_tasks WHERE task_id = $this->task_id AND user_id = $user_id";
		db_exec( $sql );

	}

	//using user allocation percentage ($perc_assign)
	function updateAssigned( $cslist, $perc_assign, $del=true ) {
	// delete all current entries
                if ($del == true) {
                        $sql = "DELETE FROM user_tasks WHERE task_id = $this->task_id";
                        db_exec( $sql );
                }

	// process assignees
		$tarr = explode( ",", $cslist );
		foreach ($tarr as $user_id) {
			if (intval( $user_id ) > 0) {
				$perc = $perc_assign[$user_id];
				$sql = "REPLACE INTO user_tasks (user_id, task_id, perc_assignment) VALUES ($user_id, $this->task_id, $perc)";
				db_exec( $sql );
			}
		}
	}


	function updateDependencies( $cslist ) {
	// delete all current entries
		$sql = "DELETE FROM task_dependencies WHERE dependencies_task_id = $this->task_id";
		db_exec( $sql );

	// process dependencies
		$tarr = explode( ",", $cslist );
		foreach ($tarr as $task_id) {
			if (intval( $task_id ) > 0) {
				$sql = "REPLACE INTO task_dependencies (dependencies_task_id, dependencies_req_task_id) VALUES ($this->task_id, $task_id)";				
				db_exec($sql);
			}
		}
	}
	
	/**
	*	Retrieve the tasks dependencies 
	*
	*	@author	handco	<handco@users.sourceforge.net>
	*	@return	string	comma delimited list of tasks id's
	**/
	function getDependencies () {
		// Call the static method for this object
		$result = $this->staticGetDependencies ($this->task_id);
		return $result;
	} // end of getDependencies ()

	//}}}

	//{{{ staticGetDependencies ()
	/**
	*	Retrieve the tasks dependencies 
	*
	*	@author	handco	<handco@users.sourceforge.net>
	*	@param	integer	ID of the task we want dependencies
	*	@return	string	comma delimited list of tasks id's
	**/
	function staticGetDependencies ($taskId) {
		$sql = "
            SELECT dependencies_req_task_id
            FROM task_dependencies td
            WHERE td.dependencies_task_id = $taskId
		";
		$list = db_loadColumn ($sql);
		$result = $list ? implode (',', $list) : '';

		return $result;
	} // end of staticGetDependencies ()

	//}}}

	function notifyOwner() {
		GLOBAL $AppUI, $locale_char_set;
		
		$sql = "SELECT project_name FROM projects WHERE project_id=$this->task_project";
		$projname = db_loadResult( $sql );

		$mail = new Mail;

		$mail->Subject( "$projname::$this->task_name ".$AppUI->_($this->_action), $locale_char_set);

	// c = creator
	// a = assignee
	// o = owner
		$sql = "SELECT t.task_id,"
		."\nc.user_email as creator_email,"
		."\nc.user_first_name as creator_first_name,"
		."\nc.user_last_name as creator_last_name,"
		."\no.user_email as owner_email,"
		."\no.user_first_name as owner_first_name,"
		."\no.user_last_name as owner_last_name,"
		."\na.user_id as assignee_id,"
		."\na.user_email as assignee_email,"
		."\na.user_first_name as assignee_first_name,"
		."\na.user_last_name as assignee_last_name"
		."\nFROM tasks t"
		."\nLEFT JOIN user_tasks u ON u.task_id = t.task_id"
		."\nLEFT JOIN users o ON o.user_id = t.task_owner"
		."\nLEFT JOIN users c ON c.user_id = t.task_creator"
		."\nLEFT JOIN users a ON a.user_id = u.user_id"
		."\nWHERE t.task_id = $this->task_id";
		$users = db_loadList( $sql );

		if (count( $users )) {
			$body = $AppUI->_('Project').": $projname";
			$body .= "\n".$AppUI->_('Task').":    $this->task_name";
			$body .= "\n".$AppUI->_('URL').":     {$AppUI->cfg['base_url']}/index.php?m=tasks&a=view&task_id=$this->task_id";
			$body .= "\n\n" . $AppUI->_('Description') . ":"
				. "\n$this->task_description";
			$body .= "\n\n" . $AppUI->_('Creator').":" . $AppUI->user_first_name . " " . $AppUI->user_last_name;
		
			$body .= "\n\n" . $AppUI->_('Progress') . ": " . $this->task_percent_complete . "%";
			$body .= "\n\n" . dPgetParam($_POST, "task_log_description");
			
			
			$mail->Body( $body, isset( $GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : "" );
			$mail->From ( '"' . $AppUI->user_first_name . " " . $AppUI->user_last_name 
				. '" <' . $AppUI->user_email . '>'
			);
		}
		
		if ($mail->ValidEmail($users[0]['owner_email'])) {
			$mail->To( $users[0]['owner_email'], true );
			$mail->Send();
		}
		
		return '';
	}
	
	//additiona comment will be included in email body 
	function notify( $comment = '' ) {
		GLOBAL $AppUI, $locale_char_set;

		$sql = "SELECT project_name FROM projects WHERE project_id=$this->task_project";
		$projname = db_loadResult( $sql );

		$mail = new Mail;
		
		$mail->Subject( "$projname::$this->task_name ".$AppUI->_($this->_action), $locale_char_set);

	// c = creator
	// a = assignee
	// o = owner
		$sql = "SELECT t.task_id,"
		."\nc.user_email as creator_email,"
		."\nc.user_first_name as creator_first_name,"
		."\nc.user_last_name as creator_last_name,"
		."\no.user_email as owner_email,"
		."\no.user_first_name as owner_first_name,"
		."\no.user_last_name as owner_last_name,"
		."\na.user_id as assignee_id,"
		."\na.user_email as assignee_email,"
		."\na.user_first_name as assignee_first_name,"
		."\na.user_last_name as assignee_last_name"
		."\nFROM tasks t"
		."\nLEFT JOIN user_tasks u ON u.task_id = t.task_id"
		."\nLEFT JOIN users o ON o.user_id = t.task_owner"
		."\nLEFT JOIN users c ON c.user_id = t.task_creator"
		."\nLEFT JOIN users a ON a.user_id = u.user_id"
		."\nWHERE t.task_id = $this->task_id";
		$users = db_loadList( $sql );

		if (count( $users )) {
			$body = $AppUI->_('Project').": $projname";
			$body .= "\n".$AppUI->_('Task').":    $this->task_name";
			$body .= "\n".$AppUI->_('URL').":     {$AppUI->cfg['base_url']}/index.php?m=tasks&a=view&task_id=$this->task_id";
			$body .= "\n\n" . $AppUI->_('Description') . ":"
				. "\n$this->task_description";
			if ($users[0]['creator_email']) {
				$body .= "\n\n" . $AppUI->_('Creator').":"
					. "\n" . $users[0]['creator_first_name'] . " " . $users[0]['creator_last_name' ]
					. ", " . $users[0]['creator_email'];
			}
			$body .= "\n\n" . $AppUI->_('Owner').":"
				. "\n" . $users[0]['owner_first_name'] . " " . $users[0]['owner_last_name' ]
				. ", " . $users[0]['owner_email'];

			if ($comment != '') {
				$body .= "\n\n".$comment;
			}
			$mail->Body( $body, isset( $GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : "" );
			$mail->From ( '"' . $AppUI->user_first_name . " " . $AppUI->user_last_name 
				. '" <' . $AppUI->user_email . '>'
			);
		}

		foreach ($users as $row) {
			if ($row['assignee_id'] != $AppUI->user_id) {
				if ($mail->ValidEmail($row['assignee_email'])) {
					$mail->To( $row['assignee_email'], true );
					$mail->Send();
				}
			}
		}
		return '';
	}
/**
* @param Date Start date of the period
* @param Date End date of the period
* @param integer The target company
*/
	function getTasksForPeriod( $start_date, $end_date, $company_id=0 ) {
		GLOBAL $AppUI;
	// convert to default db time stamp
		$db_start = $start_date->format( FMT_DATETIME_MYSQL );
		$db_end = $end_date->format( FMT_DATETIME_MYSQL );
		
		// filter tasks for not allowed projects
		$tasks_filter = '';
		$join = winnow('projects', 'task_project', $tasks_filter);

	// assemble where clause
		$where = "task_project = project_id"
			. "\n\tAND ("
			. "\n\t\t(task_start_date <= '$db_end' AND task_end_date >= '$db_start')"
			. "\n\t\tOR task_start_date BETWEEN '$db_start' AND '$db_end'"
			. "\n\t)"
		    . "\n\tAND ($tasks_filter)";
	/*
			OR
			task_end_date BETWEEN '$db_start' AND '$db_end'
			OR
			(DATE_ADD(task_start_date, INTERVAL task_duration HOUR)) BETWEEN '$db_start' AND '$db_end'
			OR
			(DATE_ADD(task_start_date, INTERVAL task_duration DAY)) BETWEEN '$db_start' AND '$db_end'
	*/
		$where .= $company_id ? "\n\tAND project_company = $company_id" : '';

	// exclude read denied projects
		$obj = new CProject();
		$deny = $obj->getDeniedRecords( $AppUI->user_id );

		$where .= count($deny) > 0 ? "\n\tAND task_project NOT IN (" . implode( ',', $deny ) . ')' : '';

	// get any specifically denied tasks
		$obj = new CTask();
		$deny = $obj->getDeniedRecords( $AppUI->user_id );

		$where .= count($deny) > 0 ? "\n\tAND task_id NOT IN (" . implode( ',', $deny ) . ')' : '';

	// assemble query
		$sql = "SELECT task_name, task_id, task_start_date, task_end_date,"
			. "\n\ttask_duration, task_duration_type,"
			. "\n\tproject_color_identifier AS color,"
			. "\n\tproject_name"
			. "\nFROM tasks,projects"
		    . "\n$join"
			. "\nWHERE $where"
			. "\nORDER BY task_start_date";
//echo "<pre>$sql</pre>";
	// execute and return
		return db_loadList( $sql );
	}

	function canAccess( $user_id ) {
		//echo intval($this->task_access);
		switch ($this->task_access) {
			case 0:
				// public
				return true;
				break;
			case 1:
				// protected
				$sql = "SELECT user_company FROM users WHERE user_id=$user_id";
				$user_company = db_loadResult( $sql );
				$sql = "SELECT user_company FROM users WHERE user_id=$this->task_owner";
				$owner_company = db_loadResult( $sql );
				//echo "$user_company,$owner_company";die;

				$sql = "SELECT COUNT(*) FROM user_tasks WHERE user_id=$user_id AND task_id=$this->task_id";
				$count = db_loadResult( $sql );
				return (($owner_company == $user_company && $count > 0) || $this->task_owner == $user_id);
				break;
			case 2:
				// participant
				$sql = "SELECT COUNT(*) FROM user_tasks WHERE user_id=$user_id AND task_id=$this->task_id";
				$count = db_loadResult( $sql );
				return ($count > 0 || $this->task_owner == $user_id);
				break;
			case 3:
				// private
				return ($this->task_owner == $user_id);
				break;
		}
	}

	/**
	*       retrieve tasks are dependant of another.
	*       @param  integer         ID of the master task
	*       @param  boolean         true if is a dep call (recurse call)
	**/
	function dependantTasks ($taskId = false, $isDep = false) {
		static $aDeps = false;

		// Initialize the dependencies array
		if (($taskId == false) && ($isDep == false))
			$aDeps = array();

		// retrieve dependants tasks 
		if (!$taskId)
			$taskId = $this->task_id;
		$sql = "
			SELECT dependencies_task_id
			FROM task_dependencies AS td, tasks AS t
			WHERE td.dependencies_req_task_id = $taskId
			AND td.dependencies_task_id = t.task_id
			AND t.task_dynamic = 0
		";
		$aBuf = array_values(db_loadColumn ($sql));

		// Recurse to find sub dependancies
		foreach ($aBuf as $depId) {
			// work around for infinite loop
			if (!in_array($depId, $aDeps)) {
				$aDeps[] = $depId;
				$this->dependantTasks ($depId, true);
			}
		}

		// return if we are in a dependency call
		if ($isDep)
			return;
                       
		return implode (',', $aDeps);

	} // end of dependantTasks()

	/*
	 *       shift dependants tasks dates
	 *       @param  integer         time offset in seconds 
	 *       @return void
	 */
	function shiftDependantTasks ($offset) {
		$csDeps = $this->dependantTasks();

		if ($csDeps == '')
			return;

		$sql = "UPDATE tasks
		SET
			task_start_date = task_start_date + INTERVAL $offset SECOND ,
			task_end_date = task_end_date + INTERVAL $offset SECOND
		WHERE task_id IN ($csDeps)";

		db_exec ($sql);

	} // end of shiftDependantTasks() 

	/**
	* Function that returns the amount of hours this
	* task consumes per user each day
	*/
	function getTaskDurationPerDay(){
		$duration              = $this->task_duration*$this->task_duration_type;
		$task_start_date       = new CDate($this->task_start_date);
		$task_finish_date      = new CDate($this->task_end_date);
		$number_assigned_users = count($this->getAssignedUsers());
		
		$day_diff              = $task_finish_date->dateDiff($task_start_date);
		$number_of_days_worked = 0;
		$actual_date           = $task_start_date;

		for($i=0; $i<=$day_diff; $i++){
			if($actual_date->isWorkingDay()){
				$number_of_days_worked++;
			}
			$actual_date->addDays(1);
		}
		// May be it was a Sunday task
		if($number_of_days_worked == 0) $number_of_days_worked = 1;
		if($number_assigned_users == 0) $number_assigned_users = 1;
		return ($duration/$number_assigned_users) / $number_of_days_worked;
	}
	
	function getAssignedUsers(){
		$sql = "select u.*, ut.perc_assignment
		        from users as u, user_tasks as ut
		        where ut.task_id = '$this->task_id'
		              and ut.user_id = u.user_id";
		return db_loadHashList($sql, "user_id");
	}

        function getProjectName() {
                $sql = "SELECT project_name, project_short_name, project_color_identifier FROM projects WHERE project_id = '$this->task_project'";
                $proj = db_loadHash($sql, $projects);

                return $projects;


        }
	
	//Returns task children IDs
	function getChildren() {
		$sql = "select task_id from tasks where task_id != '$this->task_id'
				and task_parent = '$this->task_id'";
		return db_loadList($sql);
	}
		
	
	/**
	* This function, recursively, updates all tasks status
	* to the one passed as parameter
	*/ 
	function updateSubTasksStatus($new_status, $task_id = null){
		if(is_null($task_id)){
			$task_id = $this->task_id;
		}
		
		$sql = "select task_id
		        from tasks
		        where task_parent = '$task_id'";
		
		$tasks_id = db_loadColumn($sql);
		if(count($tasks_id) == 0) return true;
		
		$sql = "update tasks set task_status = '$new_status' where task_parent = '$task_id'";

		db_exec($sql);
		foreach($tasks_id as $id){
			if($id != $task_id){
				$this->updateSubTasksStatus($new_status, $id);
			}
		}
	}
	
	/**
	* This function recursively updates all tasks project
	* to the one passed as parameter
	*/ 
	function updateSubTasksProject($new_project , $task_id = null){
		if(is_null($task_id)){
			$task_id = $this->task_id;
		}
		$sql = "select task_id
		        from tasks
		        where task_parent = '$task_id'";
		
		$tasks_id = db_loadColumn($sql);
		if(count($tasks_id) == 0) return true;
		
		$sql = "update tasks set task_project = '$new_project' where task_parent = '$task_id'";
		db_exec($sql);

		foreach($tasks_id as $id){
			if($id != $task_id){
				$this->updateSubTasksProject($new_project, $id);
			}
		}
	}
	
	function canUserEditTimeInformation(){
		global $dPconfig, $AppUI;
		
		$project = new CProject();
		$project->load( $this->task_project );
		
		// Code to see if the current user is
		// enabled to change time information related to task
		$can_edit_time_information = false;
		// Let's see if all users are able to edit task time information
		if(isset($dPconfig['restrict_task_time_editing']) && $dPconfig['restrict_task_time_editing']==true && $this->task_id > 0){
		
			// Am I the task owner?
			if($this->task_owner == $AppUI->user_id){
				$can_edit_time_information = true;
			}
			
			// Am I the project owner?
			if($project->project_owner == $AppUI->user_id){
				$can_edit_time_information = true;
			}
			
			// Am I sys admin?
			if(!getDenyEdit("admin")){
				$can_edit_time_information = true;
			}
			
		} else if (!isset($dPconfig['restrict_task_time_editing']) || $dPconfig['restrict_task_time_editing']==false || $this->task_id == 0) { // If all users are able, then don't check anything
			$can_edit_time_information = true;
		}
		return $can_edit_time_information;
	}
}


/**
* CTask Class
*/
class CTaskLog extends CDpObject {
	var $task_log_id = NULL;
	var $task_log_task = NULL;
	var $task_log_name = NULL;
	var $task_log_description = NULL;
	var $task_log_creator = NULL;
	var $task_log_hours = NULL;
	var $task_log_date = NULL;
	var $task_log_costcode = NULL;

	function CTaskLog() {
		$this->CDpObject( 'task_log', 'task_log_id' );
	}

// overload check method
	function check() {
		$this->task_log_hours = (float) $this->task_log_hours;
		return NULL;
	}
}
?>
