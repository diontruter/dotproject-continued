<?php
if (empty( $project_active )) {
	$project_active = 0;
}

$project_start_date = $StartYYYY_int . "-". $StartMM_int . "-". $StartDD_int . " 00:00:00";
$project_end_date = $TargetYYYY_int . "-". $TargetMM_int . "-". $TargetDD_int . " 00:00:00";

if( strlen( trim( $ActualMM_int . $ActualDD_int . $ActualYYYY_int ) ) > 0) {
	$project_actual_end_date = $ActualYYYY_int . "-". $ActualMM_int . "-". $ActualDD_int . " 00:00:00";
} else {
	$project_actual_end_date = "0";
}
/*
echo $project_start_date ."<BR>";
echo $project_end_date ."<BR>";
echo $project_actual_end_date ."<BR>";
*/

if ($del) {
	// test for orphans
	$tsql = "select task_id from tasks where task_project = $project_id";
	$trc = mysql_query( $tsql );
	if (mysql_num_rows( $trc )) {
		$message = '<b>REQUEST DENIED</b>: You cannot delete a project that has tasks associated with it';
	} else {
		$tsql = "delete from projects where project_id = $project_id";
		mysql_query( $tsql );
		$message = mysql_errno() ? mysql_error() : "Project <i>$project_name</i> deleted." ;
	}
} else if ($project_id > 0) {
	$pssql = "
	update projects
	set
	project_company=$project_company,
	project_name='$project_name',
	project_short_name='$project_short_name',
	project_owner=$project_owner,
	project_url='$project_url',
	project_demo_url='$project_demo_url',
	project_start_date='$project_start_date',
	project_end_date='$project_end_date',
	project_actual_end_date='$project_actual_end_date',
	project_status=$project_status,
	project_color_identifier='$project_color_identifier',
	project_description='$project_description',
	project_target_budget=$project_target_budget,
	project_actual_budget=$project_actual_budget,
	project_active=$project_active
	where
	project_id = $project_id";

	mysql_query( $pssql );
	$message = mysql_error();
} else if ($project_id == 0) {
	$pssql="insert into projects

	(project_company, project_name, project_short_name, project_owner, project_url, project_demo_url, project_start_date, project_end_date, project_actual_end_date, project_status, project_color_identifier, project_description, project_target_budget, project_actual_budget, project_creator, project_active)
	values
	('$project_company', '$project_name', '$project_short_name', '$project_owner', '$project_url',
	'$project_demo_url', '$project_start_date', '$project_end_date', '$project_actual_end_date', '$project_status',  '$project_color_identifier', '$project_description', '$project_target_budget', '$project_actual_budget', '$user_cookie', '$project_active') ";

	mysql_query( $pssql );
	$message = mysql_error();
}

?>
<script>
window.location="./index.php?m=projects&message=<?php echo $message;?>";
</script>
