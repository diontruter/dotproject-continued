<?php /* PUBLIC $Id$ */

function selPermWhere( $table, $idfld ) {
	global $AppUI;

	// get any companies denied from viewing
	$sql = "SELECT $idfld"
		."\nFROM $table, permissions"
		."\nWHERE permission_user = $AppUI->user_id"
		."\n	AND permission_grant_on = '$table'"
		."\n	AND permission_item = $idfld"
		."\n	AND permission_value = 0";

	$deny = db_loadColumn( $sql );
	echo db_error();

	return "permission_user = $AppUI->user_id"
		."\nAND permission_value <> 0"
		."\nAND ("
		."\n	(permission_grant_on = 'all')"
		."\n	OR (permission_grant_on = '$table' and permission_item = -1)"
		."\n	OR (permission_grant_on = '$table' and permission_item = $idfld)"
		."\n	)"
		. (count($deny) > 0 ? "\nAND $idfld NOT IN (" . implode( ',', $deny ) . ')' : '');
}

$debug = false;
$callback = dPgetParam( $_GET, 'callback', 0 );
$table = dPgetParam( $_GET, 'table', 0 );

$ok = $callback & $table;

$title = "Generic Selector";
$select = '';
$from = $table;
$where = '';
$order = '';

switch ($table) {
case 'companies':
	$title = 'Company';
	$select = 'company_id,company_name';
	$order = 'company_name';
	$table .= ", permissions";
	$where = selPermWhere( 'companies', 'company_id' );
	break;
case 'departments':
// known issue: does not filter out denied companies
	$title = 'Department';
	$company_id = dPgetParam( $_GET, 'company_id', 0 );
	//$ok &= $company_id;  // Is it safe to delete this line ??? [kobudo 13 Feb 2003]
	//$where = selPermWhere( 'companies', 'company_id' );
	$where = "dept_company = company_id ";
	$where .= "\nAND ".selPermWhere( 'departments', 'dept_id' );

	$table .= ", companies, permissions";
	$hide_company = dPgetParam( $_GET, 'hide_company', 0 );
	if ( $hide_company == 1 ){
		$select = "dept_id, dept_name";
	}else{
		$select = "dept_id,CONCAT_WS(': ',company_name,dept_name) AS dept_name";
	}
	if ($company_id) {
		$where .= "\nAND dept_company = $company_id";
		$order = 'dept_name';
	} else {
		$order = 'company_name,dept_name';
	}
	break;
case 'forums':
	$title = 'Forum';
	$select = 'forum_id,forum_name';
	$order = 'forum_name';
	break;
case 'projects':
	$project_company = dPgetParam( $_GET, 'project_company', 0 );

	$title = 'Project';
	$select = 'project_id,project_name';
	$order = 'project_name';
	$where = selPermWhere( 'projects', 'project_id' );
	$where .= $project_company ? "\nAND project_company = $project_company" : '';
	$table .= ", permissions";
	break;
case 'tasks':
	$task_project = dPgetParam( $_GET, 'task_project', 0 );

	$title = 'Task';
	$select = 'task_id,task_name';
	$order = 'task_name';
	$where = $task_project ? "task_project = $task_project" : '';
	break;
case 'users':
	$title = 'User';
	$select = "user_id,CONCAT_WS(' ',user_first_name,user_last_name)";
	$order = 'user_first_name';
	break;
default:
	$ok = false;
	break;
}

if (!$ok) {
	echo "Incorrect parameters passed\n";
	if ($debug) {
		echo "<br />callback = $callback \n";
		echo "<br />table = $table \n";
		echo "<br />ok = $ok \n";
	}
} else {
	$sql = "SELECT $select FROM $table";
	$sql .= $where ? " WHERE $where" : '';
	$sql .= $order ? " ORDER BY $order" : '';
	//echo "<pre>$sql</pre>";

	$list = arrayMerge( array( 0=>$AppUI->_( '[none]' )), db_loadHashList( $sql ) );
	echo db_error();
?>
<script language="javascript">
	function setClose(key, val){
		window.opener.<?php echo $callback;?>(key,val);
		window.close();
	}
</script>

<form name="frmSelector">
<?=$AppUI->_( 'Select' ).' '.$AppUI->_( $title ).':'?>
<div style="position: absolute;top:40px;bottom:35px;left:0;width: 95%;padding:5px;overflow: auto;" name="selector">
<table cellspacing="0" cellpadding="3" border="0">
<tr>
	<td>
<?php
	if (count( $list ) > 1) {
//		echo arraySelect( $list, 'list', ' size="8"', 0 );
		foreach ($list as $key => $val) {
			echo "<a href=\"javascript:setClose('$key','$val');\">$val</a><br>\n";
		}
	} else {
		echo $AppUI->_( "no$table" );
	}
?>
	</td>
</tr>
</table>
</div>
<div style="position: absolute;width: 100%; height: 20px;left:0;bottom:0px;padding:5px;" name="footer">
<table cellspacing="0" cellpadding="0" border="0" width="90%" align="center">
<tr>
	<td align="right">
		<input type="button" class="button" value="<?php echo $AppUI->_( 'cancel' );?>" onclick="window.close()" />
	</td>
</tr>
</table>
</div>
</form>

<?php } ?>
