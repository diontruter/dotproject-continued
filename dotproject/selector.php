<?php
$debug = false;
$callback = isset( $_GET['callback'] ) ? $_GET['callback'] : 0;
$table = isset( $_GET['table'] ) ? $_GET['table'] : 0;

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
	break;
case 'departments':
	$title = 'Department';
	$company_id = isset( $_GET['company_id'] ) ? $_GET['company_id'] : '0';
	//$ok &= $company_id;

	$select = 'dept_id,dept_name';
	$where = $company_id ? "dept_company = $company_id" : '';
	$order = 'dept_name';
	break;
case 'forums':
	$title = 'Forum';
	$select = 'forum_id,forum_name';
	$order = 'forum_name';
	break;
case 'projects':
	$title = 'Project';
	$select = 'project_id,project_name';
	$order = 'project_name';
	$where = $project_company ? "project_company = $project_company" : '';
	break;
case 'tasks':
	$title = 'Task';
	$select = 'task_id,task_name';
	$order = 'task_name';
	$where = $task_project ? "task_project = $task_project" : '';
	break;
default:
	$ok = false;
	break;
}

if (!$ok) {
	echo '</head><body bgcolor="#ffffff" text="#ff0000" onload="this.focus();">Incorrect parameters passed';
	if ($debug) {
		echo "<br />callback = $callback ";
		echo "<br />table = $table ";
		echo "<br />ok = $ok";
	}
} else { 
	require_once './includes/config.php';
	require_once './includes/db_connect.php';
	require_once( "{$AppUI->cfg['root_dir']}/classdefs/ui.php" );
	require_once './includes/main_functions.php';

	session_name( 'dotproject' );
	session_start();
	session_register( 'AppUI' );

	$AppUI =& $_SESSION['AppUI'];
	$uistyle = $AppUI->getPref( 'UISTYLE' ) ? $AppUI->getPref( 'UISTYLE' ) : $AppUI->cfg['host_style'];
	@include_once( "{$AppUI->cfg['root_dir']}/locales/core.php" );

	$sql = "SELECT $select FROM $table";
	$sql .= $where ? " WHERE $where" : ''; 
	$sql .= $order ? " ORDER BY $order" : '';
	$list = arrayMerge( array( 0=>''), db_loadHashList( $sql ) );
	echo db_error();
?>
<html>
<head>
	<link rel="stylesheet" href="./style/<?php echo $uistyle;?>/main.css" type="text/css">
	<script language="javascript">
	function setClose(){
		var list = document.frmSelector.list;
		var key = list.options[list.selectedIndex].value;
		var val = list.options[list.selectedIndex].text;
		window.opener.<?php echo $callback;?>(key,val);
		window.close();
	}
	</script>
<title><?php echo $title;?>Selector</title>
</head>

<body bgcolor="#529c9c" text="#ffffff" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0" onload="this.focus();document.frmSelector.list.focus();">

<table cellspacing="0" cellpadding="3" border="0">
<form name="frmSelector">
<tr>
	<td colspan="2">
<?php
	if (count( $list ) > 1) {
		echo $AppUI->_( 'Select' ).' '.$AppUI->_( $title ).':<br />';
		echo arraySelect( $list, 'list', ' size="8"', 0 );
?>
	</td>
</tr>
<tr>
	<td>
		<input type="button" class="button" value="<?php echo $AppUI->_( 'cancel' );?>" onclick="window.close()">
<?php 
	} else {
		echo $AppUI->_( "no$table" );
	}
?>
	</td>
	<td align="right">
		<input type="button" class="button" value="<?php echo $AppUI->_( 'Select', UI_CASE_LOWER );?>" onclick="setClose()">
	</td>
</tr>
</form>
</table>

<?php } ?>
</body>
</html>
