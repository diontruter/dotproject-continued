<?php //$Id
$del = isset($_POST['del']) ? $_POST['del'] : 0;
$rm = isset($_POST['rm']) ? $_POST['rm'] : 0;
$hassign = @$_POST['hassign'];
$htasks = @$_POST['htasks'];
$store = dPgetParam($_POST, 'store', 0);
$percentage_assignment = @$_POST['percentage_assignment'];

// prepare the percentage of assignment per user as required by CTask::updateAssigned()
$hperc_assign_ar = array();
if (isset($hassign)){
        $tarr = explode( ",", $hassign );
        foreach ($tarr as $uid) {
                if (intval( $uid ) > 0) {
                  $hperc_assign_ar[$uid] = $percentage_assignment;
                }
        }
}

// prepare a list of tasks to process
$htasks_ar = array();
if (isset($htasks)){
        $tarr = explode( ",", $htasks );
        foreach ($tarr as $tid) {
                if (intval( $tid ) > 0) {
                  $htasks_ar[] = $tid;
                }
        }
}


for( $i=0; $i <= sizeof($htasks_ar); $i++) {


        $_POST['task_id'] = $htasks_ar[$i];

        // verify that task_id is not NULL
        if ($_POST['task_id'] > 0) {
                $obj = new CTask();


                if (!$obj->bind( $_POST )) {
                        $AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
                        $AppUI->redirect();
                }

                if ($rm || $del) {
                        if (($msg = $obj->removeAssigned($user_id))) {
                                $AppUI->setMsg( $msg, UI_MSG_ERROR );

                        } else {
                                $AppUI->setMsg( "User unassigned from Task", UI_MSG_OK);
                        }
                }
                if (isset($hassign) && ! $del == 1) {
                        $obj->updateAssigned( $hassign , $hperc_assign_ar, false);
                        $AppUI->setMsg( "User(s) assigned to Task", UI_MSG_OK);

                }
                if ($store == 1) {
                        if (($msg = $obj->store())) {
                                $AppUI->setMsg( $msg, UI_MSG_ERROR );

                        } else {
                                $AppUI->setMsg( "Task(s) updated", UI_MSG_OK);
                        }

                }
        }
}
$AppUI->redirect();
?>
